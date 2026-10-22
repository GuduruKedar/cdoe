<?php

namespace Modules\Examinations\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CgpaMarksDownloadController extends Controller
{
    use RequiresLogin;

    /**
     * Display CGPA marks report table with filters and download options.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $examMon = $request->input('exam_mon');
        $resultsMon = $request->input('results_mon');
        $year = $request->input('year');
        $sem = $request->input('sem');
        $grade = $request->input('grade');
        $division = $request->input('division');
        $q = trim((string) $request->input('q', ''));
        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, [15, 25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        // Distinct filter options from database
        $examMonths = DB::table('examinations.marks_cgpa')
            ->whereNotNull('exam_mon_yyyy')
            ->distinct()
            ->orderBy('exam_mon_yyyy')
            ->pluck('exam_mon_yyyy');

        $resultMonths = DB::table('examinations.marks_cgpa')
            ->whereNotNull('results_mon_yyyy')
            ->distinct()
            ->orderBy('results_mon_yyyy')
            ->pluck('results_mon_yyyy');

        $years = DB::table('examinations.marks_cgpa')
            ->whereNotNull('s_year')
            ->distinct()
            ->orderBy('s_year')
            ->pluck('s_year');

        $sems = DB::table('examinations.marks_cgpa')
            ->whereNotNull('s_sem')
            ->distinct()
            ->orderBy('s_sem')
            ->pluck('s_sem');

        $grades = DB::table('examinations.marks_cgpa')
            ->whereNotNull('letter_grade')
            ->where('letter_grade', '!=', '')
            ->distinct()
            ->orderBy('letter_grade')
            ->pluck('letter_grade');

        $divisions = DB::table('examinations.marks_cgpa')
            ->whereNotNull('division_name')
            ->where('division_name', '!=', '')
            ->distinct()
            ->orderBy('division_name')
            ->pluck('division_name');

        $totalRecords = DB::table('examinations.marks_cgpa')->count();

        // Build query joined with student name
        $query = DB::table('examinations.marks_cgpa as mc')
            ->leftJoin('admissions.student_registrations as sr', 'mc.appno', '=', 'sr.application_no')
            ->select(
                'mc.appno',
                'mc.regno',
                'mc.exam_mon_yyyy',
                'mc.mmno',
                'mc.subcode',
                'mc.mm_type',
                'mc.fa_conducted',
                'mc.sa_conducted',
                'mc.fa_earned',
                'mc.sa_earned',
                'mc.total_conducted',
                'mc.total_earned',
                'mc.grade_points',
                'mc.letter_grade',
                'mc.sgpa_perc',
                'mc.cgpa_perc',
                'mc.division_name',
                'mc.results_mon_yyyy',
                'mc.s_year',
                'mc.s_sem',
                'sr.student_name'
            );

        if ($examMon && $examMon !== 'all') {
            $query->where('mc.exam_mon_yyyy', $examMon);
        }
        if ($resultsMon && $resultsMon !== 'all') {
            $query->where('mc.results_mon_yyyy', $resultsMon);
        }
        if ($year && $year !== 'all') {
            $query->where('mc.s_year', (int) $year);
        }
        if ($sem && $sem !== 'all') {
            $query->where('mc.s_sem', (int) $sem);
        }
        if ($grade && $grade !== 'all') {
            $query->where('mc.letter_grade', $grade);
        }
        if ($division && $division !== 'all') {
            $query->where('mc.division_name', $division);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('mc.regno', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.mmno', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.appno', is_numeric($q) ? (int) $q : -1);
            });
        }

        $filteredCount = (clone $query)->count();
        $records = $query->orderBy('mc.regno', 'asc')->paginate($perPage);

        return view('examinations::cgpamarks', compact(
            'user',
            'menus',
            'records',
            'examMonths',
            'resultMonths',
            'years',
            'sems',
            'grades',
            'divisions',
            'examMon',
            'resultsMon',
            'year',
            'sem',
            'grade',
            'division',
            'q',
            'totalRecords',
            'filteredCount'
        ));
    }

    /**
     * Stream download filtered CGPA marks report to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->requireLogin();

        $examMon = $request->input('exam_mon');
        $resultsMon = $request->input('results_mon');
        $year = $request->input('year');
        $sem = $request->input('sem');
        $grade = $request->input('grade');
        $division = $request->input('division');
        $q = trim((string) $request->input('q', ''));

        $query = DB::table('examinations.marks_cgpa as mc')
            ->leftJoin('admissions.student_registrations as sr', 'mc.appno', '=', 'sr.application_no')
            ->select(
                'mc.appno',
                'mc.regno',
                'sr.student_name',
                'mc.mmno',
                'mc.subcode',
                'mc.exam_mon_yyyy',
                'mc.results_mon_yyyy',
                'mc.s_year',
                'mc.s_sem',
                'mc.mm_type',
                'mc.fa_earned',
                'mc.sa_earned',
                'mc.total_conducted',
                'mc.total_earned',
                'mc.grade_points',
                'mc.letter_grade',
                'mc.sgpa_perc',
                'mc.cgpa_perc',
                'mc.division_name'
            );

        if ($examMon && $examMon !== 'all') {
            $query->where('mc.exam_mon_yyyy', $examMon);
        }
        if ($resultsMon && $resultsMon !== 'all') {
            $query->where('mc.results_mon_yyyy', $resultsMon);
        }
        if ($year && $year !== 'all') {
            $query->where('mc.s_year', (int) $year);
        }
        if ($sem && $sem !== 'all') {
            $query->where('mc.s_sem', (int) $sem);
        }
        if ($grade && $grade !== 'all') {
            $query->where('mc.letter_grade', $grade);
        }
        if ($division && $division !== 'all') {
            $query->where('mc.division_name', $division);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('mc.regno', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.mmno', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.appno', is_numeric($q) ? (int) $q : -1);
            });
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="marks_cgpa_report_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'S.No', 'Application No', 'Register No', 'Student Name', 'Marks Memo No', 'Subject Code',
                'Exam Month-Year', 'Results Month-Year', 'Year', 'Semester', 'Memo Type',
                'FA Earned', 'SA Earned', 'Total Conducted', 'Total Earned', 'Grade Points',
                'Letter Grade', 'SGPA %', 'CGPA %', 'Division',
            ]);

            $i = 1;
            $query->orderBy('mc.regno', 'asc')->chunk(200, function ($rows) use ($handle, &$i) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $i++,
                        $r->appno,
                        $r->regno,
                        $r->student_name,
                        $r->mmno,
                        $r->subcode,
                        $r->exam_mon_yyyy,
                        $r->results_mon_yyyy,
                        $r->s_year,
                        $r->s_sem,
                        $r->mm_type,
                        $r->fa_earned,
                        $r->sa_earned,
                        $r->total_conducted,
                        $r->total_earned,
                        $r->grade_points,
                        $r->letter_grade,
                        $r->sgpa_perc,
                        $r->cgpa_perc,
                        $r->division_name,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
