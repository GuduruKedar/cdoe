<?php

namespace Modules\Examinations\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admissions\Models\Programs;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Examinations\Models\MarksMemo;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamReportController extends Controller
{
    use RequiresLogin;

    /**
     * Display the Exam Report hub with tabs: Fail Report, Toppers, CGPA Report.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $tab = $request->input('tab', 'fail');
        if (! in_array($tab, ['fail', 'toppers', 'cgpa'], true)) {
            $tab = 'fail';
        }

        $program = $request->input('program', 'all');
        $batch = $request->input('batch', 'all');
        $sem = $request->input('sem', 'all');
        $examMon = $request->input('exam_mon', 'all');
        $q = trim((string) $request->input('q', ''));
        $perPage = (int) $request->input('per_page', 200);
        if (! in_array($perPage, [50, 100, 200, 500], true)) {
            $perPage = 200;
        }

        // Distinct filter options
        $programs = Programs::orderBy('program_id')->get(['program_id', 'program_code', 'program_short_name']);

        $batches = StudentRegistration::whereNotNull('adm_batch_code')
            ->where('adm_batch_code', '!=', '')
            ->distinct()
            ->orderBy('adm_batch_code')
            ->pluck('adm_batch_code');

        if ($batches->isEmpty()) {
            $batches = MarksMemo::whereNotNull('batch')
                ->where('batch', '!=', '')
                ->distinct()
                ->orderBy('batch')
                ->pluck('batch');
        }

        $semesters = MarksMemo::whereNotNull('s_sem')
            ->distinct()
            ->orderBy('s_sem')
            ->pluck('s_sem');

        $examMonths = MarksMemo::whereNotNull('exam_mon_yyyy')
            ->where('exam_mon_yyyy', '!=', '')
            ->distinct()
            ->orderBy('exam_mon_yyyy')
            ->pluck('exam_mon_yyyy');

        // Build query based on active tab
        $query = $this->buildReportQuery($tab, [
            'program' => $program,
            'batch' => $batch,
            'sem' => $sem,
            'exam_mon' => $examMon,
            'q' => $q,
        ]);

        $records = $query->paginate($perPage);

        // Summary counts for quick stats
        $counts = $this->getSummaryCounts([
            'program' => $program,
            'batch' => $batch,
            'sem' => $sem,
            'exam_mon' => $examMon,
            'q' => $q,
        ]);

        return view('examinations::examreport', compact(
            'user',
            'menus',
            'tab',
            'program',
            'batch',
            'sem',
            'examMon',
            'q',
            'perPage',
            'programs',
            'batches',
            'semesters',
            'examMonths',
            'records',
            'counts'
        ));
    }

    /**
     * Stream CSV export for the currently active tab and filter criteria.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->requireLogin();

        $tab = $request->input('tab', 'fail');
        if (! in_array($tab, ['fail', 'toppers', 'cgpa'], true)) {
            $tab = 'fail';
        }

        $filters = [
            'program' => $request->input('program', 'all'),
            'batch' => $request->input('batch', 'all'),
            'sem' => $request->input('sem', 'all'),
            'exam_mon' => $request->input('exam_mon', 'all'),
            'q' => trim((string) $request->input('q', '')),
        ];

        $query = $this->buildReportQuery($tab, $filters);
        $filename = 'exam_report_'.$tab.'_'.date('Ymd_His').'.csv';

        return new StreamedResponse(function () use ($query, $tab) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            if ($tab === 'fail') {
                fputcsv($handle, [
                    'S.No', 'Register No', 'Student Name', 'Program', 'Batch', 'Semester',
                    'Subject Code', 'Subject Name', 'Internal (FA)', 'External (SA)',
                    'Total Marks', 'Grade', 'Credits', 'Exam Month',
                ]);

                $index = 1;
                $query->chunk(500, function ($rows) use ($handle, &$index) {
                    foreach ($rows as $r) {
                        fputcsv($handle, [
                            $index++,
                            $r->regno ?? '',
                            $r->student_name ?? '',
                            $r->program_short_name ?? '',
                            $r->batch ?? '',
                            $r->s_sem ? 'Semester '.$r->s_sem : '',
                            $r->subcode ?? '',
                            $r->subname ?? '',
                            $r->fa_earned ?? '-',
                            $r->sa_earned ?? '-',
                            $r->total_earned ?? '-',
                            $r->letter_grade ?? 'F',
                            $r->credits ?? '',
                            $r->exam_mon_yyyy ?? '',
                        ]);
                    }
                });
            } elseif ($tab === 'toppers') {
                fputcsv($handle, [
                    'Rank', 'Register No', 'Student Name', 'Program', 'Batch', 'Semester',
                    'SGPA', 'CGPA', 'Division', 'Total Credits', 'Exam Month',
                ]);

                $index = 1;
                $query->chunk(500, function ($rows) use ($handle, &$index) {
                    foreach ($rows as $r) {
                        fputcsv($handle, [
                            $index++,
                            $r->regno ?? '',
                            $r->student_name ?? '',
                            $r->program_short_name ?? '',
                            $r->batch ?? '',
                            $r->s_sem ? 'Semester '.$r->s_sem : '',
                            number_format((float) ($r->sgpa ?? 0), 2),
                            number_format((float) ($r->cgpa ?? 0), 2),
                            $r->division_name ?? '',
                            $r->total_credits ?? '',
                            $r->exam_mon_yyyy ?? '',
                        ]);
                    }
                });
            } else {
                fputcsv($handle, [
                    'S.No', 'Register No', 'Student Name', 'Program', 'Batch', 'Semester',
                    'SGPA', 'CGPA', 'Division', 'Backlogs', 'Total Credits', 'Exam Month',
                ]);

                $index = 1;
                $query->chunk(500, function ($rows) use ($handle, &$index) {
                    foreach ($rows as $r) {
                        fputcsv($handle, [
                            $index++,
                            $r->regno ?? '',
                            $r->student_name ?? '',
                            $r->program_short_name ?? '',
                            $r->batch ?? '',
                            $r->s_sem ? 'Semester '.$r->s_sem : '',
                            number_format((float) ($r->sgpa ?? 0), 2),
                            number_format((float) ($r->cgpa ?? 0), 2),
                            $r->division_name ?? '',
                            $r->backlogs_count ?? 0,
                            $r->total_credits ?? '',
                            $r->exam_mon_yyyy ?? '',
                        ]);
                    }
                });
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Build the query for each specific report tab.
     */
    private function buildReportQuery(string $tab, array $f)
    {
        if ($tab === 'fail') {
            $query = DB::table('examinations.marks_cgpa as mc')
                ->leftJoin('admissions.student_registrations as sr', 'mc.regno', '=', 'sr.register_no')
                ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
                ->leftJoin('academics.subjects as sub', 'mc.subcode', '=', 'sub.subcode')
                ->where(function ($q) {
                    $q->where('mc.letter_grade', 'F')
                        ->orWhere('mc.grade_points', '0')
                        ->orWhere('mc.division_name', 'FAIL');
                })
                ->select([
                    'mc.regno',
                    'sr.student_name',
                    'p.program_short_name',
                    'sr.progid',
                    DB::raw('COALESCE(mc.batch, sr.adm_batch_code) as batch'),
                    'mc.s_sem',
                    'mc.s_year',
                    'mc.subcode',
                    'sub.subname',
                    'mc.fa_earned',
                    'mc.fa_conducted',
                    'mc.sa_earned',
                    'mc.sa_conducted',
                    'mc.total_earned',
                    'mc.total_conducted',
                    'mc.letter_grade',
                    'mc.grade_points',
                    'mc.credits',
                    'mc.exam_mon_yyyy',
                    'mc.mm_type',
                    'mc.mmno',
                    'mc.smmno',
                ]);

            if (! empty($f['program']) && $f['program'] !== 'all') {
                $query->where('sr.progid', (int) $f['program']);
            }
            if (! empty($f['batch']) && $f['batch'] !== 'all') {
                $query->where(function ($q) use ($f) {
                    $q->where('mc.batch', $f['batch'])
                        ->orWhere('sr.adm_batch_code', $f['batch']);
                });
            }
            if (! empty($f['sem']) && $f['sem'] !== 'all') {
                $query->where('mc.s_sem', (int) $f['sem']);
            }
            if (! empty($f['exam_mon']) && $f['exam_mon'] !== 'all') {
                $query->where('mc.exam_mon_yyyy', $f['exam_mon']);
            }
            if (! empty($f['q'])) {
                $search = $f['q'];
                $query->where(function ($q) use ($search) {
                    $q->where('mc.regno', 'ILIKE', "%{$search}%")
                        ->orWhere('sr.student_name', 'ILIKE', "%{$search}%")
                        ->orWhere('mc.subcode', 'ILIKE', "%{$search}%")
                        ->orWhere('sub.subname', 'ILIKE', "%{$search}%");
                });
            }

            return $query->orderBy('mc.regno')->orderBy('mc.s_sem')->orderBy('mc.subcode');
        }

        if ($tab === 'toppers') {
            $query = DB::table('examinations.marks_cgpa as mc')
                ->leftJoin('admissions.student_registrations as sr', 'mc.regno', '=', 'sr.register_no')
                ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
                ->where(function ($q) {
                    $q->whereNull('mc.division_name')
                        ->orWhere('mc.division_name', '!=', 'FAIL');
                })
                ->whereRaw("mc.sgpa_perc ~ '^[0-9]+(\\.[0-9]+)?$'")
                ->groupBy([
                    'mc.regno',
                    'sr.student_name',
                    'p.program_short_name',
                    'sr.progid',
                    DB::raw('COALESCE(mc.batch, sr.adm_batch_code)'),
                    'mc.s_sem',
                    'mc.s_year',
                    'mc.exam_mon_yyyy',
                    'mc.division_name',
                ])
                ->select([
                    'mc.regno',
                    'sr.student_name',
                    'p.program_short_name',
                    'sr.progid',
                    DB::raw('COALESCE(mc.batch, sr.adm_batch_code) as batch'),
                    'mc.s_sem',
                    'mc.s_year',
                    'mc.exam_mon_yyyy',
                    'mc.division_name',
                    DB::raw("MAX(CASE WHEN mc.sgpa_perc ~ '^[0-9]+(\\.[0-9]+)?$' THEN mc.sgpa_perc::numeric ELSE 0 END) as sgpa"),
                    DB::raw("MAX(CASE WHEN mc.cgpa_perc ~ '^[0-9]+(\\.[0-9]+)?$' THEN mc.cgpa_perc::numeric ELSE 0 END) as cgpa"),
                    DB::raw('SUM(mc.credits) as total_credits'),
                    DB::raw('COUNT(*) as total_subjects'),
                    DB::raw('MAX(mc.mmno) as mmno'),
                ]);

            if (! empty($f['program']) && $f['program'] !== 'all') {
                $query->where('sr.progid', (int) $f['program']);
            }
            if (! empty($f['batch']) && $f['batch'] !== 'all') {
                $query->where(function ($q) use ($f) {
                    $q->where('mc.batch', $f['batch'])
                        ->orWhere('sr.adm_batch_code', $f['batch']);
                });
            }
            if (! empty($f['sem']) && $f['sem'] !== 'all') {
                $query->where('mc.s_sem', (int) $f['sem']);
            }
            if (! empty($f['exam_mon']) && $f['exam_mon'] !== 'all') {
                $query->where('mc.exam_mon_yyyy', $f['exam_mon']);
            }
            if (! empty($f['q'])) {
                $search = $f['q'];
                $query->where(function ($q) use ($search) {
                    $q->where('mc.regno', 'ILIKE', "%{$search}%")
                        ->orWhere('sr.student_name', 'ILIKE', "%{$search}%");
                });
            }

            return $query->orderByDesc('sgpa')->orderByDesc('cgpa')->orderBy('mc.regno');
        }

        // Default: CGPA Report
        $query = DB::table('examinations.marks_cgpa as mc')
            ->leftJoin('admissions.student_registrations as sr', 'mc.regno', '=', 'sr.register_no')
            ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
            ->groupBy([
                'mc.regno',
                'sr.student_name',
                'p.program_short_name',
                'sr.progid',
                DB::raw('COALESCE(mc.batch, sr.adm_batch_code)'),
                'mc.s_sem',
                'mc.s_year',
                'mc.exam_mon_yyyy',
                'mc.results_mon_yyyy',
                'mc.division_name',
            ])
            ->select([
                'mc.regno',
                'sr.student_name',
                'p.program_short_name',
                'sr.progid',
                DB::raw('COALESCE(mc.batch, sr.adm_batch_code) as batch'),
                'mc.s_sem',
                'mc.s_year',
                'mc.exam_mon_yyyy',
                'mc.results_mon_yyyy',
                'mc.division_name',
                DB::raw("MAX(CASE WHEN mc.sgpa_perc ~ '^[0-9]+(\\.[0-9]+)?$' THEN mc.sgpa_perc::numeric ELSE 0 END) as sgpa"),
                DB::raw("MAX(CASE WHEN mc.cgpa_perc ~ '^[0-9]+(\\.[0-9]+)?$' THEN mc.cgpa_perc::numeric ELSE 0 END) as cgpa"),
                DB::raw('SUM(mc.credits) as total_credits'),
                DB::raw('COUNT(*) as total_subjects'),
                DB::raw("COUNT(CASE WHEN mc.letter_grade = 'F' THEN 1 END) as backlogs_count"),
                DB::raw('MAX(mc.mmno) as mmno'),
            ]);

        if (! empty($f['program']) && $f['program'] !== 'all') {
            $query->where('sr.progid', (int) $f['program']);
        }
        if (! empty($f['batch']) && $f['batch'] !== 'all') {
            $query->where(function ($q) use ($f) {
                $q->where('mc.batch', $f['batch'])
                    ->orWhere('sr.adm_batch_code', $f['batch']);
            });
        }
        if (! empty($f['sem']) && $f['sem'] !== 'all') {
            $query->where('mc.s_sem', (int) $f['sem']);
        }
        if (! empty($f['exam_mon']) && $f['exam_mon'] !== 'all') {
            $query->where('mc.exam_mon_yyyy', $f['exam_mon']);
        }
        if (! empty($f['q'])) {
            $search = $f['q'];
            $query->where(function ($q) use ($search) {
                $q->where('mc.regno', 'ILIKE', "%{$search}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('mc.regno')->orderBy('mc.s_sem');
    }

    /**
     * Get summary counts for the current filtered slice.
     */
    private function getSummaryCounts(array $f): array
    {
        $base = DB::table('examinations.marks_cgpa as mc')
            ->leftJoin('admissions.student_registrations as sr', 'mc.regno', '=', 'sr.register_no');

        if (! empty($f['program']) && $f['program'] !== 'all') {
            $base->where('sr.progid', (int) $f['program']);
        }
        if (! empty($f['batch']) && $f['batch'] !== 'all') {
            $base->where(function ($q) use ($f) {
                $q->where('mc.batch', $f['batch'])
                    ->orWhere('sr.adm_batch_code', $f['batch']);
            });
        }
        if (! empty($f['sem']) && $f['sem'] !== 'all') {
            $base->where('mc.s_sem', (int) $f['sem']);
        }
        if (! empty($f['exam_mon']) && $f['exam_mon'] !== 'all') {
            $base->where('mc.exam_mon_yyyy', $f['exam_mon']);
        }
        if (! empty($f['q'])) {
            $search = $f['q'];
            $base->where(function ($q) use ($search) {
                $q->where('mc.regno', 'ILIKE', "%{$search}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$search}%")
                    ->orWhere('mc.subcode', 'ILIKE', "%{$search}%");
            });
        }

        $rowStats = (clone $base)->select([
            DB::raw('COUNT(*) as total_marks_rows'),
            DB::raw('COUNT(DISTINCT mc.regno) as total_students'),
            DB::raw("COUNT(CASE WHEN mc.letter_grade = 'F' THEN 1 END) as total_failed_subjects"),
            DB::raw("COUNT(DISTINCT CASE WHEN mc.letter_grade = 'F' THEN mc.regno END) as students_with_backlogs"),
            DB::raw("COUNT(DISTINCT CASE WHEN (mc.division_name IS NULL OR mc.division_name != 'FAIL') AND mc.sgpa_perc ~ '^[0-9]+(\\.[0-9]+)?$' AND mc.sgpa_perc::numeric >= 8.5 THEN mc.regno END) as total_toppers"),
        ])->first();

        return [
            'total_students' => (int) ($rowStats->total_students ?? 0),
            'failed_subjects' => (int) ($rowStats->total_failed_subjects ?? 0),
            'students_with_backlogs' => (int) ($rowStats->students_with_backlogs ?? 0),
            'toppers' => (int) ($rowStats->total_toppers ?? 0),
        ];
    }
}
