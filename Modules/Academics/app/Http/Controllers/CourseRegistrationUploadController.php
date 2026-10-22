<?php

namespace Modules\Academics\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseRegistrationUploadController extends Controller
{
    use RequiresLogin;

    /**
     * Display Course Registration Upload dashboard.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $stats = [
            'total_registrations' => DB::table('academics.course_registration')->count(),
            'total_students' => DB::table('academics.course_registration')->distinct()->count('regno'),
            'distinct_subjects' => DB::table('academics.course_registration')->whereNotNull('subcode')->distinct()->count('subcode'),
            'distinct_semesters' => DB::table('academics.course_registration')->whereNotNull('semcode')->distinct()->count('semcode'),
        ];

        // Recent course registrations (latest 15)
        $recentRegistrations = DB::table('academics.course_registration as cr')
            ->leftJoin('admissions.student_registrations as s', 'cr.regno', '=', 's.register_no')
            ->leftJoin('academics.subjects as sub', 'cr.subcode', '=', 'sub.subcode')
            ->select(
                'cr.regno',
                's.student_name',
                'cr.semcode',
                'cr.subcode',
                'sub.subname',
                'cr.s_year',
                'cr.s_sem',
                'cr.section_no',
                'cr.reg_type',
                'cr.updated_at'
            )
            ->orderBy('cr.updated_at', 'desc')
            ->limit(15)
            ->get();

        $semcodes = DB::table('academics.course_registration')
            ->whereNotNull('semcode')
            ->distinct()
            ->orderBy('semcode')
            ->pluck('semcode');

        return view('academics::courseregisupload', compact('user', 'menus', 'stats', 'recentRegistrations', 'semcodes'));
    }

    /**
     * Download sample CSV template for Course Registration upload.
     */
    public function sampleTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cdoe_course_registration_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Columns matching academics.course_registration table schema
            fputcsv($handle, [
                'semcode',
                'classno',
                'appno',
                'regno',
                'subcode',
                'stdplace',
                'roomno',
                's_year',
                's_sem',
                'section_no',
                'regulationid',
                'reg_type',
            ]);

            // Sample rows
            fputcsv($handle, [
                'SEM-2025-1',
                'CLS-101',
                '20260120600',
                '261CK01001',
                'MBAD01',
                'HYDERABAD',
                'R-204',
                '1',
                '1',
                'A',
                'R23',
                'R',
            ]);

            fputcsv($handle, [
                'SEM-2025-1',
                'CLS-101',
                '20260120601',
                '261CK01002',
                'MBAD01',
                'GUNTUR',
                'R-204',
                '1',
                '1',
                'A',
                'R23',
                'R',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Process uploaded CSV file and upsert into academics.course_registration.
     */
    public function upload(Request $request)
    {
        $user = $this->requireLogin();

        $request->validate([
            'registration_file' => 'required|file|max:20480', // 20 MB max
        ], [
            'registration_file.required' => 'Please select a CSV file containing course registration records.',
            'registration_file.max' => 'The uploaded file must not exceed 20 MB.',
        ]);

        $file = $request->file('registration_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            return back()->with('error', 'Only CSV format (.csv) is currently supported for course registrations upload.');
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to read the uploaded course registrations file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->with('error', 'The uploaded file is empty.');
        }

        // Normalize header names
        $cleanedHeaders = array_map(function ($h) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $h)));
        }, $header);

        $regnoIdx = array_search('regno', $cleanedHeaders, true);
        $subcodeIdx = array_search('subcode', $cleanedHeaders, true);
        if ($regnoIdx === false || $subcodeIdx === false) {
            fclose($handle);

            return back()->with('error', "Invalid CSV format: Missing required columns 'regno' and 'subcode'. Please use the sample template.");
        }

        $now = Carbon::now();
        $userCode = $user->usercode ?? session('user.usercode', 'SYSTEM');
        $ip = $request->ip();

        $insertedCount = 0;
        $updatedCount = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(array_filter($row))) {
                continue;
            }

            $data = [];
            foreach ($cleanedHeaders as $idx => $colName) {
                $data[$colName] = isset($row[$idx]) ? trim((string) $row[$idx]) : null;
            }

            $regno = $data['regno'] ?? null;
            $subcode = $data['subcode'] ?? null;
            if (! $regno || ! $subcode) {
                $errors[] = "Row {$rowNum}: Missing register number or subject code.";

                continue;
            }

            $semcode = ! empty($data['semcode']) ? $data['semcode'] : ($request->input('semcode_default') ?: 'CURRENT');

            $record = [
                'semcode' => $semcode,
                'classno' => $data['classno'] ?? null,
                'appno' => $data['appno'] ?? null,
                'regno' => $regno,
                'subcode' => $subcode,
                'stdplace' => $data['stdplace'] ?? null,
                'roomno' => $data['roomno'] ?? null,
                's_year' => ! empty($data['s_year']) ? (int) $data['s_year'] : 1,
                's_sem' => ! empty($data['s_sem']) ? (int) $data['s_sem'] : 1,
                'section_no' => $data['section_no'] ?? 'A',
                'regulationid' => $data['regulationid'] ?? null,
                'reg_type' => $data['reg_type'] ?? 'R',
                'updated_by' => $userCode,
                'updated_at' => $now,
                'log_ipaddress' => $ip,
            ];

            try {
                // Check if registration exists for (regno, subcode, semcode)
                $existing = DB::table('academics.course_registration')
                    ->where('regno', $regno)
                    ->where('subcode', $subcode)
                    ->where('semcode', $semcode)
                    ->exists();

                if ($existing) {
                    DB::table('academics.course_registration')
                        ->where('regno', $regno)
                        ->where('subcode', $subcode)
                        ->where('semcode', $semcode)
                        ->update($record);
                    $updatedCount++;
                } else {
                    $record['reg_date'] = $now->toDateString();
                    DB::table('academics.course_registration')->insert($record);
                    $insertedCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum} ({$regno}/{$subcode}): ".$e->getMessage();
            }
        }

        fclose($handle);

        ActivityLogger::log('ACADEMICS', 'COURSE_REGISTRATION_UPLOAD', "Uploaded course registrations: {$file->getClientOriginalName()} ({$insertedCount} inserted, {$updatedCount} updated).", [
            'user_code' => $userCode,
            'status' => empty($errors) ? 'success' : 'warning',
            'payload' => [
                'filename' => $file->getClientOriginalName(),
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'error_count' => count($errors),
            ],
        ]);

        $msg = "Course registrations upload completed: {$insertedCount} records inserted, {$updatedCount} records updated.";
        if (! empty($errors)) {
            $msg .= ' Some rows had warnings: '.implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $msg);
    }
}
