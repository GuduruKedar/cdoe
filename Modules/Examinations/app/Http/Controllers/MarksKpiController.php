<?php

namespace Modules\Examinations\Http\Controllers;

use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Examinations\Services\MarksKpiService;

class MarksKpiController extends Controller
{
    use RequiresLogin;

    /** Hard cap on rows pulled into PHP. Narrow the filters if a cohort is larger than this. */
    private const ROW_LIMIT = 60000;

    public function index(Request $request)
    {
        $user = $this->requireLogin();

        $filters = $request->validate([
            'regno' => 'nullable|string|max:30',
            'program_id' => 'nullable|integer',
            'regulation' => 'nullable|string|max:20',
            's_year' => 'nullable|integer|min:1|max:10',
            's_sem' => 'nullable|integer|min:1|max:10',
            'exam' => 'nullable|string|max:30',
            'gender' => 'nullable|string|max:20',
            'mm_type' => 'nullable|string|max:20',
            'all' => 'nullable|in:1',
        ]);
        $filters = array_filter($filters, fn ($v) => $v !== null && $v !== '');
        $options = $this->filterOptions();

        $kpi = null;
        $error = null;
        $hasFilter = count(array_diff_key($filters, ['all' => 1])) > 0;

        if ($hasFilter || isset($filters['all'])) {
            try {
                [$rows, $truncated] = $this->fetchRows($filters);
                $kpi = MarksKpiService::build($rows, ['truncated' => $truncated, 'row_limit' => self::ROW_LIMIT]);
            } catch (\Throwable $e) {
                Log::error('Marks KPI failed', ['filters' => $filters, 'error' => $e->getMessage()]);
                $error = 'Results could not be loaded. Check the filters and try again; details are in the application log.';
            }
        }

        return view('examinations::markskpi', compact('user', 'kpi', 'filters', 'options', 'error'));
    }

    /** @return array{0: array, 1: bool} rows as arrays, and whether the row limit was hit */
    private function fetchRows(array $f): array
    {
        $sql = 'SELECT mc.appno, mc.regno, mc.exam_mon_yyyy, mc.mmno, mc.subcode, mc.mm_type,
                       mc.fa_conducted, mc.sa_conducted, mc.fa_earned, mc.sa_earned,
                       mc.total_conducted, mc.total_earned, mc.grade_points, mc.letter_grade,
                       mc.sgpa_perc, mc.cgpa_perc, mc.division_name, mc.results_mon_yyyy,
                       mc.s_year, mc.s_sem, mc.regulation, mc.credits,
                       sr.progid, sr.electiveid, sr.gender, e.program_id, p.program_short_name
                  FROM examinations.marks_cgpa mc
                  LEFT JOIN admissions.student_registrations sr ON mc.regno = sr.register_no
                  LEFT JOIN master.electives e ON sr.electiveid = e.elective_id
                  LEFT JOIN master.programs p ON e.program_id = p.program_id
                 WHERE 1 = 1';
        $b = [];

        if (! empty($f['regno'])) {
            $sql .= ' AND mc.regno = ?';
            $b[] = strtoupper(trim($f['regno']));
        }
        if (! empty($f['program_id'])) {
            $sql .= ' AND e.program_id = ?';
            $b[] = (int) $f['program_id'];
        }
        if (! empty($f['regulation'])) {
            $sql .= ' AND mc.regulation = ?';
            $b[] = $f['regulation'];
        }
        if (! empty($f['s_year'])) {
            $sql .= ' AND mc.s_year = ?';
            $b[] = (int) $f['s_year'];
        }
        if (! empty($f['s_sem'])) {
            $sql .= ' AND mc.s_sem = ?';
            $b[] = (int) $f['s_sem'];
        }
        if (! empty($f['exam'])) {
            $sql .= ' AND mc.exam_mon_yyyy = ?';
            $b[] = $f['exam'];
        }
        if (! empty($f['gender'])) {
            $sql .= ' AND sr.gender = ?';
            $b[] = $f['gender'];
        }
        if (! empty($f['mm_type'])) {
            $sql .= ' AND mc.mm_type = ?';
            $b[] = $f['mm_type'];
        }

        $sql .= ' ORDER BY mc.regno, mc.s_year, mc.s_sem, mc.subcode LIMIT '.(self::ROW_LIMIT + 1);

        $rows = array_map(fn ($r) => (array) $r, DB::select($sql, $b));
        $truncated = count($rows) > self::ROW_LIMIT;
        if ($truncated) {
            array_pop($rows);
        }

        return [$rows, $truncated];
    }

    /** Dropdown values, cached for 10 minutes. Each list fails independently so the page still opens. */
    private function filterOptions(): array
    {
        return Cache::remember('examinations.markskpi.options', 600, function () {
            $safe = function (string $sql) {
                try {
                    return DB::select($sql);
                } catch (\Throwable $e) {
                    Log::warning('Marks KPI option query failed', ['sql' => $sql, 'error' => $e->getMessage()]);

                    return [];
                }
            };
            $col = fn (array $rows, string $c) => array_values(array_filter(array_map(fn ($r) => $r->$c, $rows), fn ($v) => $v !== null && $v !== ''));

            $exams = $col($safe('SELECT DISTINCT exam_mon_yyyy FROM examinations.marks_cgpa'), 'exam_mon_yyyy');
            usort($exams, fn ($a, $b) => strtotime('1 '.$b) <=> strtotime('1 '.$a)); // newest first

            return [
                'programs' => array_map(fn ($r) => ['id' => $r->program_id, 'name' => $r->program_short_name],
                    $safe('SELECT program_id, program_short_name FROM master.programs ORDER BY program_short_name')),
                'regulations' => $col($safe('SELECT DISTINCT regulation FROM examinations.marks_cgpa ORDER BY regulation'), 'regulation'),
                'years' => $col($safe('SELECT DISTINCT s_year FROM examinations.marks_cgpa ORDER BY s_year'), 's_year'),
                'sems' => $col($safe('SELECT DISTINCT s_sem FROM examinations.marks_cgpa ORDER BY s_sem'), 's_sem'),
                'exams' => $exams,
                'types' => $col($safe('SELECT DISTINCT mm_type FROM examinations.marks_cgpa ORDER BY mm_type'), 'mm_type'),
                'genders' => $col($safe('SELECT DISTINCT gender FROM admissions.student_registrations ORDER BY gender'), 'gender'),
            ];
        });
    }
}
