<?php

namespace Modules\Examinations\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CgpaMarksUploadController extends Controller
{
    use RequiresLogin;

    /**
     * Display CGPA marks upload dashboard.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $stats = [
            'total_records' => DB::table('examinations.marks_cgpa')->count(),
            'total_students' => DB::table('examinations.marks_cgpa')->distinct()->count('regno'),
            'distinct_exams' => DB::table('examinations.marks_cgpa')->whereNotNull('exam_mon_yyyy')->distinct()->count('exam_mon_yyyy'),
            'distinct_subjects' => DB::table('examinations.marks_cgpa')->whereNotNull('subcode')->distinct()->count('subcode'),
        ];

        // Recent marks uploads (latest 15)
        $recentMarks = DB::table('examinations.marks_cgpa as m')
            ->leftJoin('admissions.student_registrations as s', 'm.regno', '=', 's.register_no')
            ->select(
                'm.regno',
                's.student_name',
                'm.exam_mon_yyyy',
                'm.results_mon_yyyy',
                'm.subcode',
                'm.mm_type',
                'm.total_conducted',
                'm.total_earned',
                'm.letter_grade',
                'm.grade_points',
                'm.cgpa_perc',
                'm.log_timestamp'
            )
            ->orderBy('m.log_timestamp', 'desc')
            ->limit(15)
            ->get();

        $examMonths = DB::table('examinations.marks_cgpa')
            ->whereNotNull('exam_mon_yyyy')
            ->distinct()
            ->orderBy('exam_mon_yyyy')
            ->pluck('exam_mon_yyyy');

        return view('examinations::cgpamarkupload', compact('user', 'menus', 'stats', 'recentMarks', 'examMonths'));
    }

    /**
     * Download standard sample CSV template for CGPA marks upload.
     */
    public function sampleTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cdoe_cgpa_marks_upload_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header columns matching examinations.marks_cgpa table schema
            fputcsv($handle, [
                'appno',
                'regno',
                'exam_mon_yyyy',
                'results_mon_yyyy',
                's_year',
                's_sem',
                'mmno',
                'subcode',
                'mm_type',
                'fa_conducted',
                'sa_conducted',
                'fa_earned',
                'sa_earned',
                'total_conducted',
                'total_earned',
                'grade_points',
                'letter_grade',
                'sgpa_perc',
                'cgpa_perc',
                'division_name',
            ]);

            // Sample record rows
            fputcsv($handle, [
                '20260120600',
                '261CK01001',
                'DEC-2025',
                'FEB-2026',
                '1',
                '1',
                'MM-001',
                'MBAD01',
                'R',
                '40',
                '60',
                '35',
                '52',
                '100',
                '87',
                '9.0',
                'A',
                '8.70',
                '8.70',
                'FIRST CLASS WITH DISTINCTION',
            ]);

            fputcsv($handle, [
                '20260120601',
                '261CK01002',
                'DEC-2025',
                'FEB-2026',
                '1',
                '1',
                'MM-002',
                'MBAD01',
                'R',
                '40',
                '60',
                '38',
                '55',
                '100',
                '93',
                '10.0',
                'O',
                '9.30',
                '9.30',
                'FIRST CLASS WITH DISTINCTION',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Process uploaded CSV/Excel file and upsert into examinations.marks_cgpa.
     */
    public function upload(Request $request)
    {
        $user = $this->requireLogin();

        $request->validate([
            'marks_file' => 'required|file|max:20480', // 20 MB max
        ], [
            'marks_file.required' => 'Please select a CSV file containing the CGPA marks data to upload.',
            'marks_file.max' => 'The uploaded marks file must not exceed 20 MB.',
        ]);

        $file = $request->file('marks_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            return back()->with('error', 'Only CSV format (.csv) is currently supported for bulk marks upload.');
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to read the uploaded marks file.');
        }

        // Detect BOM and read header
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->with('error', 'The uploaded file is empty.');
        }

        // Clean headers
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

            $examMon = ! empty($data['exam_mon_yyyy']) ? $data['exam_mon_yyyy'] : ($request->input('exam_mon_yyyy') ?: 'UNKNOWN');
            $mmno = ! empty($data['mmno']) ? $data['mmno'] : "MM-{$regno}-{$subcode}";

            $record = [
                'appno' => $data['appno'] ?? null,
                'regno' => $regno,
                'exam_mon_yyyy' => $examMon,
                'results_mon_yyyy' => $data['results_mon_yyyy'] ?? null,
                's_year' => ! empty($data['s_year']) ? (int) $data['s_year'] : null,
                's_sem' => ! empty($data['s_sem']) ? (int) $data['s_sem'] : null,
                'mmno' => $mmno,
                'subcode' => $subcode,
                'mm_type' => $data['mm_type'] ?? 'R',
                'fa_conducted' => ! empty($data['fa_conducted']) ? (int) $data['fa_conducted'] : null,
                'sa_conducted' => ! empty($data['sa_conducted']) ? (int) $data['sa_conducted'] : null,
                'fa_earned' => ! empty($data['fa_earned']) ? (int) $data['fa_earned'] : null,
                'sa_earned' => ! empty($data['sa_earned']) ? (int) $data['sa_earned'] : null,
                'total_conducted' => ! empty($data['total_conducted']) ? (int) $data['total_conducted'] : null,
                'total_earned' => ! empty($data['total_earned']) ? (int) $data['total_earned'] : null,
                'grade_points' => ! empty($data['grade_points']) ? (float) $data['grade_points'] : null,
                'letter_grade' => $data['letter_grade'] ?? null,
                'sgpa_perc' => ! empty($data['sgpa_perc']) ? (float) $data['sgpa_perc'] : null,
                'cgpa_perc' => ! empty($data['cgpa_perc']) ? (float) $data['cgpa_perc'] : null,
                'division_name' => $data['division_name'] ?? null,
                'log_userid' => $userCode,
                'log_ipaddress' => $ip,
                'log_timestamp' => $now,
            ];

            try {
                // Upsert on primary key (mmno, subcode)
                $existing = DB::table('examinations.marks_cgpa')
                    ->where('mmno', $mmno)
                    ->where('subcode', $subcode)
                    ->exists();

                if ($existing) {
                    DB::table('examinations.marks_cgpa')
                        ->where('mmno', $mmno)
                        ->where('subcode', $subcode)
                        ->update($record);
                    $updatedCount++;
                } else {
                    DB::table('examinations.marks_cgpa')->insert($record);
                    $insertedCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum} ({$regno}/{$subcode}): ".$e->getMessage();
            }
        }

        fclose($handle);

        ActivityLogger::log('EXAMINATIONS', 'MARKS_UPLOAD', "Uploaded marks file: {$file->getClientOriginalName()} ({$insertedCount} inserted, {$updatedCount} updated).", [
            'user_code' => $userCode,
            'status' => empty($errors) ? 'success' : 'warning',
            'payload' => [
                'filename' => $file->getClientOriginalName(),
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'error_count' => count($errors),
            ],
        ]);

        $msg = "CGPA marks upload completed: {$insertedCount} records inserted, {$updatedCount} records updated.";
        if (! empty($errors)) {
            $msg .= ' Some rows had warnings: '.implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $msg);
    }
}
