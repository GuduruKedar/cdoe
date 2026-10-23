<?php

namespace Modules\Academics\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academics\Models\CourseRegistration;
use Modules\Admissions\Models\StudentRegistration;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseRegistrationDownloadController extends Controller
{
    use RequiresLogin;

    /**
     * Display a listing of course registrations with filters and search.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $semcode = $request->input('semcode');
        $subcode = $request->input('subcode');
        $year = $request->input('year');
        $sem = $request->input('sem');
        $section = $request->input('section');
        $regType = $request->input('reg_type');
        $q = trim((string) $request->input('q', ''));
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, [15, 25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        // Distinct filter options from database via Eloquent
        $semcodes = CourseRegistration::whereNotNull('semcode')
            ->distinct()
            ->orderBy('semcode')
            ->pluck('semcode');

        $subcodes = CourseRegistration::whereNotNull('subcode')
            ->distinct()
            ->orderBy('subcode')
            ->pluck('subcode');

        $years = CourseRegistration::whereNotNull('s_year')
            ->distinct()
            ->orderBy('s_year')
            ->pluck('s_year');

        $sems = CourseRegistration::whereNotNull('s_sem')
            ->distinct()
            ->orderBy('s_sem')
            ->pluck('s_sem');

        $sections = CourseRegistration::whereNotNull('section_no')
            ->distinct()
            ->orderBy('section_no')
            ->pluck('section_no');

        $regTypes = CourseRegistration::whereNotNull('reg_type')
            ->distinct()
            ->orderBy('reg_type')
            ->pluck('reg_type');

        $totalRecords = CourseRegistration::count();

        $crTable = (new CourseRegistration)->getTable();
        $srTable = (new StudentRegistration)->getTable();

        // Build query joined with student name for richness
        $query = CourseRegistration::from("{$crTable} as cr")
            ->leftJoin("{$srTable} as sr", 'cr.appno', '=', 'sr.application_no')
            ->select(
                'cr.semcode',
                'cr.classno',
                'cr.appno',
                'cr.regno',
                'cr.subcode',
                'cr.stdplace',
                'cr.roomno',
                'cr.s_year',
                'cr.s_sem',
                'cr.section_no',
                'cr.regulationid',
                'cr.reg_date',
                'cr.attendance_date',
                'cr.reg_type',
                'cr.core_emp',
                'cr.addl_emp',
                'sr.student_name'
            );

        if ($semcode && $semcode !== 'all') {
            $query->where('cr.semcode', $semcode);
        }
        if ($subcode && $subcode !== 'all') {
            $query->where('cr.subcode', $subcode);
        }
        if ($year && $year !== 'all') {
            $query->where('cr.s_year', (int) $year);
        }
        if ($sem && $sem !== 'all') {
            $query->where('cr.s_sem', (int) $sem);
        }
        if ($section && $section !== 'all') {
            $query->where('cr.section_no', $section);
        }
        if ($regType && $regType !== 'all') {
            $query->where('cr.reg_type', $regType);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('cr.regno', 'ILIKE', "%{$q}%")
                    ->orWhere('cr.subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('cr.classno', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('cr.appno', is_numeric($q) ? (int) $q : -1);
            });
        }

        $filteredCount = (clone $query)->count();
        $records = $query->orderBy('cr.regno', 'asc')->paginate($perPage);

        return view('academics::coursereg', compact(
            'user',
            'menus',
            'records',
            'semcodes',
            'subcodes',
            'years',
            'sems',
            'sections',
            'regTypes',
            'semcode',
            'subcode',
            'year',
            'sem',
            'section',
            'regType',
            'q',
            'totalRecords',
            'filteredCount'
        ));
    }

    /**
     * Stream download all filtered course registrations to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->requireLogin();

        $semcode = $request->input('semcode');
        $subcode = $request->input('subcode');
        $year = $request->input('year');
        $sem = $request->input('sem');
        $section = $request->input('section');
        $regType = $request->input('reg_type');
        $q = trim((string) $request->input('q', ''));

        $crTable = (new CourseRegistration)->getTable();
        $srTable = (new StudentRegistration)->getTable();

        $query = CourseRegistration::from("{$crTable} as cr")
            ->leftJoin("{$srTable} as sr", 'cr.appno', '=', 'sr.application_no')
            ->select(
                'cr.semcode',
                'cr.classno',
                'cr.appno',
                'cr.regno',
                'sr.student_name',
                'cr.subcode',
                'cr.stdplace',
                'cr.roomno',
                'cr.s_year',
                'cr.s_sem',
                'cr.section_no',
                'cr.regulationid',
                'cr.reg_date',
                'cr.reg_type',
                'cr.core_emp'
            );

        if ($semcode && $semcode !== 'all') {
            $query->where('cr.semcode', $semcode);
        }
        if ($subcode && $subcode !== 'all') {
            $query->where('cr.subcode', $subcode);
        }
        if ($year && $year !== 'all') {
            $query->where('cr.s_year', (int) $year);
        }
        if ($sem && $sem !== 'all') {
            $query->where('cr.s_sem', (int) $sem);
        }
        if ($section && $section !== 'all') {
            $query->where('cr.section_no', $section);
        }
        if ($regType && $regType !== 'all') {
            $query->where('cr.reg_type', $regType);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('cr.regno', 'ILIKE', "%{$q}%")
                    ->orWhere('cr.subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('cr.classno', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('cr.appno', is_numeric($q) ? (int) $q : -1);
            });
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="course_registrations_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'S.No', 'Semester Code', 'Class No', 'Application No', 'Register No', 'Student Name',
                'Subject Code', 'Study Place', 'Room No', 'Year', 'Semester', 'Section',
                'Regulation ID', 'Registration Date', 'Registration Type', 'Core Employee Code',
            ]);

            $i = 1;
            $query->orderBy('cr.regno', 'asc')->chunk(200, function ($rows) use ($handle, &$i) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $i++,
                        $r->semcode,
                        $r->classno,
                        $r->appno,
                        $r->regno,
                        $r->student_name,
                        $r->subcode,
                        $r->stdplace,
                        $r->roomno,
                        $r->s_year,
                        $r->s_sem,
                        $r->section_no,
                        $r->regulationid,
                        $r->reg_date,
                        $r->reg_type,
                        $r->core_emp,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
