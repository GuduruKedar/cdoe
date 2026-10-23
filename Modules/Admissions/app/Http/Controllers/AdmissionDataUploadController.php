<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Admissions\Models\Programs;
use Modules\Admissions\Models\StudentRegistration;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmissionDataUploadController extends Controller
{
    use RequiresLogin;

    /**
     * Show Admission Data upload interface with statistics, recent uploads, and program reference.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $stats = [
            'total_students' => StudentRegistration::count(),
            'active_students' => StudentRegistration::where('std_status', 'Admitted')->count(),
            'total_programs' => Programs::count(),
            'total_batches' => StudentRegistration::whereNotNull('adm_batch_code')->distinct()->count('adm_batch_code'),
        ];

        $srTable = (new StudentRegistration)->getTable();
        $pTable = (new Programs)->getTable();

        // Recent student registrations preview (latest 15)
        $recentStudents = StudentRegistration::from("{$srTable} as sr")
            ->leftJoin("{$pTable} as p", 'sr.progid', '=', 'p.program_id')
            ->select(
                'sr.application_no',
                'sr.register_no',
                'sr.student_name',
                'sr.personal_email',
                'sr.mobile',
                'p.program_short_name',
                'sr.adm_batch_code',
                'sr.std_status',
                'sr.log_timestamp'
            )
            ->orderBy('sr.log_timestamp', 'desc')
            ->limit(15)
            ->get();

        $programs = Programs::orderBy('program_code')->get();

        return view('admissions::admdataupload', compact('user', 'menus', 'stats', 'recentStudents', 'programs'));
    }

    /**
     * Download sample CSV template for Student Admission Data upload.
     */
    public function sampleTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cdoe_admission_data_upload_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Headers matching admissions.student_registrations table schema
            fputcsv($handle, [
                'application_no',
                'enrollment_no',
                'register_no',
                'student_name',
                'personal_email',
                'dob',
                'gender',
                'doj',
                'mobile',
                'ofc_mail',
                'regulation',
                'progid',
                'electiveid',
                'admyear',
                'adm_batch_code',
                'aadhar_no',
                'abc_id',
                'deb_id',
                'std_type',
                'std_status',
            ]);

            // Sample rows
            fputcsv($handle, [
                '20260120901',
                'ENR-2026-001',
                '261CK01091',
                'RAMESH KUMAR',
                'ramesh.k@example.com',
                '2002-05-14',
                'Male',
                '2026-01-10',
                '9876543210',
                'ramesh_cdoe@vignan.ac.in',
                'R23',
                '1',
                '1',
                '2026',
                'BATCH-2026',
                '123456789012',
                'ABC1234567',
                'DEB9876543',
                'Regular',
                'Admitted',
            ]);

            fputcsv($handle, [
                '20260120902',
                'ENR-2026-002',
                '261CK01092',
                'PRIYA SHARMA',
                'priya.s@example.com',
                '2003-08-22',
                'Female',
                '2026-01-10',
                '9876543211',
                'priya_cdoe@vignan.ac.in',
                'R23',
                '1',
                '1',
                '2026',
                'BATCH-2026',
                '123456789013',
                'ABC1234568',
                'DEB9876544',
                'Regular',
                'Admitted',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Process uploaded CSV file and upsert into admissions.student_registrations.
     */
    public function upload(Request $request)
    {
        $user = $this->requireLogin();

        $request->validate([
            'admission_file' => 'required|file|max:20480', // 20 MB max
        ], [
            'admission_file.required' => 'Please select a CSV file containing applicant admission data to upload.',
            'admission_file.max' => 'The uploaded file must not exceed 20 MB.',
        ]);

        $file = $request->file('admission_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            return back()->with('error', 'Only CSV format (.csv) is currently supported for admission data upload.');
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to read the uploaded admission data file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->with('error', 'The uploaded file is empty.');
        }

        // Clean headers
        $cleanedHeaders = array_map(function ($h) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $h)));
        }, $header);

        $appNoIdx = array_search('application_no', $cleanedHeaders, true);
        $nameIdx = array_search('student_name', $cleanedHeaders, true);
        if ($appNoIdx === false || $nameIdx === false) {
            fclose($handle);

            return back()->with('error', "Invalid CSV format: Missing required columns 'application_no' and 'student_name'. Please use the sample template.");
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

            $applicationNo = $data['application_no'] ?? null;
            $studentName = $data['student_name'] ?? null;
            if (! $applicationNo || ! $studentName) {
                $errors[] = "Row {$rowNum}: Missing application number or student name.";

                continue;
            }

            $record = [
                'application_no' => (string) $applicationNo,
                'enrollment_no' => $data['enrollment_no'] ?? null,
                'register_no' => $data['register_no'] ?? null,
                'student_name' => $studentName,
                'personal_email' => $data['personal_email'] ?? null,
                'dob' => ! empty($data['dob']) ? $data['dob'] : null,
                'gender' => $data['gender'] ?? null,
                'doj' => ! empty($data['doj']) ? $data['doj'] : null,
                'mobile' => $data['mobile'] ?? null,
                'ofc_mail' => $data['ofc_mail'] ?? null,
                'regulation' => $data['regulation'] ?? 'R23',
                'progid' => ! empty($data['progid']) ? (int) $data['progid'] : 1,
                'electiveid' => ! empty($data['electiveid']) ? (int) $data['electiveid'] : null,
                'admyear' => ! empty($data['admyear']) ? (int) $data['admyear'] : (int) date('Y'),
                'adm_batch_code' => $data['adm_batch_code'] ?? null,
                'aadhar_no' => $data['aadhar_no'] ?? null,
                'abc_id' => $data['abc_id'] ?? null,
                'deb_id' => $data['deb_id'] ?? null,
                'std_type' => $data['std_type'] ?? 'Regular',
                'std_status' => $data['std_status'] ?? 'Admitted',
                'lock_status' => 0,
                'log_userid' => $userCode,
                'log_ipaddress' => $ip,
                'log_timestamp' => $now,
            ];

            try {
                // Upsert on primary key application_no via Eloquent
                $existing = StudentRegistration::where('application_no', (string) $applicationNo)->first();

                if ($existing) {
                    $existing->update($record);
                    $updatedCount++;
                } else {
                    StudentRegistration::create($record);
                    $insertedCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum} ({$applicationNo}): ".$e->getMessage();
            }
        }

        fclose($handle);

        ActivityLogger::log('ADMISSIONS', 'ADMISSION_DATA_UPLOAD', "Uploaded student admission data: {$file->getClientOriginalName()} ({$insertedCount} inserted, {$updatedCount} updated).", [
            'user_code' => $userCode,
            'status' => empty($errors) ? 'success' : 'warning',
            'payload' => [
                'filename' => $file->getClientOriginalName(),
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'error_count' => count($errors),
            ],
        ]);

        $msg = "Admission data upload completed: {$insertedCount} records inserted, {$updatedCount} records updated.";
        if (! empty($errors)) {
            $msg .= ' Some rows had warnings: '.implode('; ', array_slice($errors, 0, 3));
        }

        return back()->with('success', $msg);
    }
}
