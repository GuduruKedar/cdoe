<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admissions\Models\Programs;
use Modules\Admissions\Models\StudentRegistration;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmissionDataDownloadController extends Controller
{
    use RequiresLogin;

    /**
     * Display a listing of admitted students with rich filtering, search, and pagination.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $program = $request->input('program');
        $batch = $request->input('batch');
        $regulation = $request->input('regulation');
        $gender = $request->input('gender');
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, [15, 25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        // Distinct dropdown options via Eloquent
        $programs = Programs::orderBy('program_code')
            ->select('program_id', 'program_code', 'program_long_name')
            ->get();

        $batches = StudentRegistration::whereNotNull('adm_batch_code')
            ->distinct()
            ->orderBy('adm_batch_code')
            ->pluck('adm_batch_code');

        $regulations = StudentRegistration::whereNotNull('regulation')
            ->distinct()
            ->orderBy('regulation')
            ->pluck('regulation');

        $statuses = StudentRegistration::whereNotNull('std_status')
            ->distinct()
            ->orderBy('std_status')
            ->pluck('std_status');

        $totalRecords = StudentRegistration::count();

        $srTable = (new StudentRegistration)->getTable();
        $pTable = (new Programs)->getTable();

        // Build query
        $query = StudentRegistration::from("{$srTable} as sr")
            ->leftJoin("{$pTable} as p", 'sr.progid', '=', 'p.program_id')
            ->select(
                'sr.application_no',
                'sr.enrollment_no',
                'sr.register_no',
                'sr.student_name',
                'sr.personal_email',
                'sr.dob',
                'sr.gender',
                'sr.doj',
                'sr.mobile',
                'sr.ofc_mail',
                'sr.regulation',
                'sr.progid',
                'sr.admyear',
                'sr.adm_batch_code',
                'sr.aadhar_no',
                'sr.abc_id',
                'sr.deb_id',
                'sr.std_type',
                'sr.std_status',
                'sr.lock_status',
                'sr.log_timestamp',
                'p.program_code',
                'p.program_long_name'
            );

        if ($program && $program !== 'all') {
            $query->where('sr.progid', (int) $program);
        }
        if ($batch && $batch !== 'all') {
            $query->where('sr.adm_batch_code', $batch);
        }
        if ($regulation && $regulation !== 'all') {
            $query->where('sr.regulation', $regulation);
        }
        if ($gender && $gender !== 'all') {
            $query->where('sr.gender', $gender);
        }
        if ($status && $status !== 'all') {
            $query->where('sr.std_status', $status);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('sr.register_no', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.personal_email', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.mobile', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.application_no', is_numeric($q) ? (int) $q : -1);
            });
        }

        $filteredCount = (clone $query)->count();
        $records = $query->orderBy('sr.application_no', 'asc')->paginate($perPage);

        return view('admissions::datadownload', compact(
            'user',
            'menus',
            'records',
            'programs',
            'batches',
            'regulations',
            'statuses',
            'program',
            'batch',
            'regulation',
            'gender',
            'status',
            'q',
            'totalRecords',
            'filteredCount'
        ));
    }

    /**
     * Stream download all filtered records to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->requireLogin();

        $program = $request->input('program');
        $batch = $request->input('batch');
        $regulation = $request->input('regulation');
        $gender = $request->input('gender');
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));

        $srTable = (new StudentRegistration)->getTable();
        $pTable = (new Programs)->getTable();

        $query = StudentRegistration::from("{$srTable} as sr")
            ->leftJoin("{$pTable} as p", 'sr.progid', '=', 'p.program_id')
            ->select(
                'sr.application_no',
                'sr.enrollment_no',
                'sr.register_no',
                'sr.student_name',
                'sr.personal_email',
                'sr.dob',
                'sr.gender',
                'sr.doj',
                'sr.mobile',
                'sr.ofc_mail',
                'sr.regulation',
                'p.program_code',
                'p.program_long_name',
                'sr.admyear',
                'sr.adm_batch_code',
                'sr.aadhar_no',
                'sr.abc_id',
                'sr.deb_id',
                'sr.std_type',
                'sr.std_status'
            );

        if ($program && $program !== 'all') {
            $query->where('sr.progid', (int) $program);
        }
        if ($batch && $batch !== 'all') {
            $query->where('sr.adm_batch_code', $batch);
        }
        if ($regulation && $regulation !== 'all') {
            $query->where('sr.regulation', $regulation);
        }
        if ($gender && $gender !== 'all') {
            $query->where('sr.gender', $gender);
        }
        if ($status && $status !== 'all') {
            $query->where('sr.std_status', $status);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('sr.register_no', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.personal_email', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.mobile', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.application_no', is_numeric($q) ? (int) $q : -1);
            });
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="student_registrations_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'S.No', 'Application No', 'Enrollment No', 'Register No', 'Student Name',
                'Personal Email', 'Official Email', 'Mobile', 'Gender', 'Date of Birth',
                'Date of Joining', 'Program Code', 'Program Name', 'Batch Code', 'Admission Year',
                'Regulation', 'Aadhar No', 'ABC ID', 'DEB ID', 'Student Type', 'Status',
            ]);

            $i = 1;
            $query->orderBy('sr.application_no', 'asc')->chunk(200, function ($rows) use ($handle, &$i) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $i++,
                        $r->application_no,
                        $r->enrollment_no,
                        $r->register_no,
                        $r->student_name,
                        $r->personal_email,
                        $r->ofc_mail,
                        $r->mobile,
                        $r->gender,
                        $r->dob,
                        $r->doj,
                        $r->program_code,
                        $r->program_long_name,
                        $r->adm_batch_code,
                        $r->admyear,
                        $r->regulation,
                        $r->aadhar_no ? "'".$r->aadhar_no : '',
                        $r->abc_id,
                        $r->deb_id,
                        $r->std_type,
                        $r->std_status,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
