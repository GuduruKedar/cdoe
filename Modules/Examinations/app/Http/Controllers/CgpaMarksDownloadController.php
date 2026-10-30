<?php

namespace Modules\Examinations\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Examinations\Models\MarksMemo;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CgpaMarksDownloadController extends Controller
{
    use RequiresLogin;

    /**
     * Display a listing of CGPA marks records with rich filtering, search, and pagination.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $batch = $request->input('batch');
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

        // Distinct filter options from database via Eloquent
        $batches = MarksMemo::whereNotNull('batch')
            ->where('batch', '!=', '')
            ->distinct()
            ->orderBy('batch')
            ->pluck('batch');

        if ($batches->isEmpty()) {
            $batches = StudentRegistration::whereNotNull('adm_batch_code')
                ->where('adm_batch_code', '!=', '')
                ->distinct()
                ->orderBy('adm_batch_code')
                ->pluck('adm_batch_code');
        }

        $examMonths = MarksMemo::whereNotNull('exam_mon_yyyy')
            ->distinct()
            ->orderBy('exam_mon_yyyy')
            ->pluck('exam_mon_yyyy');

        $resultMonths = MarksMemo::whereNotNull('results_mon_yyyy')
            ->distinct()
            ->orderBy('results_mon_yyyy')
            ->pluck('results_mon_yyyy');

        $years = MarksMemo::whereNotNull('s_year')
            ->distinct()
            ->orderBy('s_year')
            ->pluck('s_year');

        $sems = MarksMemo::whereNotNull('s_sem')
            ->distinct()
            ->orderBy('s_sem')
            ->pluck('s_sem');

        $grades = MarksMemo::whereNotNull('letter_grade')
            ->where('letter_grade', '!=', '')
            ->distinct()
            ->orderBy('letter_grade')
            ->pluck('letter_grade');

        $divisions = MarksMemo::whereNotNull('division_name')
            ->where('division_name', '!=', '')
            ->distinct()
            ->orderBy('division_name')
            ->pluck('division_name');

        $totalRecords = MarksMemo::count();

        $mcTable = (new MarksMemo)->getTable();
        $srTable = (new StudentRegistration)->getTable();

        // Build query joined with student name and batch
        $query = MarksMemo::from("{$mcTable} as mc")
            ->leftJoin("{$srTable} as sr", 'mc.appno', '=', 'sr.application_no')
            ->select(
                'mc.appno',
                'mc.regno',
                'sr.student_name',
                DB::raw('COALESCE(mc.batch, sr.adm_batch_code) as batch'),
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
                'mc.s_sem'
            );

        if ($batch && $batch !== 'all') {
            $query->where(function ($w) use ($batch) {
                $w->where('mc.batch', $batch)
                    ->orWhere('sr.adm_batch_code', $batch);
            });
        }
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
                    ->orWhere('mc.batch', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.adm_batch_code', 'ILIKE', "%{$q}%")
                    ->orWhere('mc.appno', is_numeric($q) ? (int) $q : -1);
            });
        }

        $filteredCount = (clone $query)->count();
        $records = $query->orderBy('mc.regno', 'asc')->paginate($perPage);

        return view('examinations::cgpamarks', compact(
            'user',
            'menus',
            'records',
            'batches',
            'examMonths',
            'resultMonths',
            'years',
            'sems',
            'grades',
            'divisions',
            'batch',
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

        $batch = $request->input('batch');
        $examMon = $request->input('exam_mon');
        $resultsMon = $request->input('results_mon');
        $year = $request->input('year');
        $sem = $request->input('sem');
        $grade = $request->input('grade');
        $division = $request->input('division');
        $q = trim((string) $request->input('q', ''));

        $mcTable = (new MarksMemo)->getTable();
        $srTable = (new StudentRegistration)->getTable();

        $query = MarksMemo::from("{$mcTable} as mc")
            ->leftJoin("{$srTable} as sr", 'mc.appno', '=', 'sr.application_no')
            ->select(
                'mc.appno',
                'mc.regno',
                'sr.student_name',
                DB::raw('COALESCE(mc.batch, sr.adm_batch_code) as batch'),
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

        if ($batch && $batch !== 'all') {
            $query->where(function ($w) use ($batch) {
                $w->where('mc.batch', $batch)
                    ->orWhere('sr.adm_batch_code', $batch);
            });
        }
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
                    ->orWhere('mc.batch', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.adm_batch_code', 'ILIKE', "%{$q}%")
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
                'S.No', 'Application No', 'Register No', 'Student Name', 'Batch', 'Marks Memo No', 'Subject Code',
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
                        $r->batch,
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
