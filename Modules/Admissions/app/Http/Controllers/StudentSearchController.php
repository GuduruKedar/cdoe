<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSearchController extends Controller
{
    use RequiresLogin;

    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $q = trim((string) $request->input('q', ''));
        $batch = $request->input('batch');
        $regulation = $request->input('regulation');
        $program = $request->input('program');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = DB::table('admissions.student_registrations as sr')
            ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
            ->select(
                'sr.application_no',
                'sr.register_no',
                'sr.student_name',
                'sr.adm_batch_code as batch_code',
                'sr.regulation',
                'sr.gender',
                'sr.dob as date_of_birth',
                'sr.doj as admission_date',
                'sr.std_status as status',
                'sr.std_type',
                'sr.admyear',
                'sr.personal_email as email',
                'sr.ofc_mail',
                'sr.mobile as mobile_no',
                'sr.aadhar_no',
                'sr.abc_id',
                'sr.deb_id',
                'sr.enrollment_no',
                'p.program_long_name as program_name',
                'p.program_code',
                'p.program_short_name'
            );

        // Multi-token semantic search across all key student attributes
        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $token) {
                $query->where(function ($sub) use ($token) {
                    $term = "%{$token}%";
                    $sub->where('sr.register_no', 'ilike', $term)
                        ->orWhere('sr.student_name', 'ilike', $term)
                        ->orWhere('sr.adm_batch_code', 'ilike', $term)
                        ->orWhere('sr.regulation', 'ilike', $term)
                        ->orWhere('p.program_code', 'ilike', $term)
                        ->orWhere('p.program_long_name', 'ilike', $term)
                        ->orWhere('p.program_short_name', 'ilike', $term)
                        ->orWhere('sr.std_status', 'ilike', $term)
                        ->orWhere('sr.personal_email', 'ilike', $term)
                        ->orWhere('sr.ofc_mail', 'ilike', $term)
                        ->orWhere('sr.mobile', 'ilike', $term)
                        ->orWhere('sr.enrollment_no', 'ilike', $term)
                        ->orWhere('sr.aadhar_no', 'ilike', $term);
                });
            }
        }

        // Direct attribute filters
        if (! empty($batch) && $batch !== 'all') {
            $query->where('sr.adm_batch_code', $batch);
        }
        if (! empty($regulation) && $regulation !== 'all') {
            $query->where('sr.regulation', $regulation);
        }
        if (! empty($program) && $program !== 'all') {
            if (is_numeric($program)) {
                $query->where('sr.progid', (int) $program);
            } else {
                $query->where(function ($sub) use ($program) {
                    $sub->where('p.program_code', $program)
                        ->orWhere('p.program_short_name', $program);
                });
            }
        }
        if (! empty($status) && $status !== 'all') {
            $query->where('sr.std_status', $status);
        }

        $students = $query->orderBy('sr.register_no')
            ->paginate($perPage)
            ->withQueryString();

        // AJAX request: return JSON with rendered rows, pagination and metadata
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $rowsHtml = view('admissions::partials.student-rows', compact('students'))->render();
            $paginationHtml = ($students->count() > 0 && $students->hasPages())
                ? $students->links('pagination::bootstrap-5')->render()
                : '';

            return response()->json([
                'success' => true,
                'html' => $rowsHtml,
                'pagination' => $paginationHtml,
                'total' => $students->total(),
                'count' => $students->count(),
                'from' => $students->firstItem() ?? 0,
                'to' => $students->lastItem() ?? 0,
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
            ]);
        }

        // Filter dropdown options for the initial page load
        $batches = DB::table('admissions.student_registrations')
            ->whereNotNull('adm_batch_code')
            ->distinct()
            ->orderBy('adm_batch_code', 'desc')
            ->pluck('adm_batch_code');

        $regulations = DB::table('admissions.student_registrations')
            ->whereNotNull('regulation')
            ->distinct()
            ->orderBy('regulation', 'asc')
            ->pluck('regulation');

        $programs = DB::table('master.programs')
            ->select('program_id', 'program_code', 'program_short_name', 'program_long_name')
            ->orderBy('program_code')
            ->get();

        $statuses = DB::table('admissions.student_registrations')
            ->whereNotNull('std_status')
            ->distinct()
            ->orderBy('std_status')
            ->pluck('std_status');

        $totalStudents = DB::table('admissions.student_registrations')->count();

        return view('admissions::stdsearch', compact(
            'user',
            'menus',
            'students',
            'q',
            'batch',
            'regulation',
            'program',
            'status',
            'perPage',
            'batches',
            'regulations',
            'programs',
            'statuses',
            'totalStudents'
        ));
    }
}
