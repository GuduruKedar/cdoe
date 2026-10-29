<?php

namespace Modules\Examinations\Services;

/**
 * Student marks KPI engine.
 *
 * Pure PHP (no DB, no Laravel facades) so it can be unit-tested with plain arrays.
 * Input: rows from examinations.marks_cgpa joined with registrations / programs (arrays).
 * Output: one array with summary KPIs, chart series, tables, data-quality checks and insights.
 *
 * Adjust the constants below to match your regulation before relying on the numbers.
 */
class MarksKpiService
{
    /** Letter grades that mean the subject is NOT cleared. */
    public const FAIL_GRADES = ['F', 'AB', 'ABSENT', 'FAIL', 'W', 'MP', 'NC'];

    /** Rows whose mm_type is in this list are first (regular) attempts; anything else is a reappear/supply attempt. */
    public const REGULAR_TYPES = ['REGULAR'];

    /** Which attempt counts when a subject is written more than once: 'best' or 'latest'. */
    public const ATTEMPT_POLICY = 'best';

    /**
     * Grade scale used only for the data-quality check (min %, letter, grade points).
     * This is an assumption for R24 - replace it with your regulation's real table.
     */
    public const GRADE_SCALE = [
        [90, 'O', 10], [80, 'S', 9], [70, 'A', 8], [60, 'B', 7], [50, 'C', 6], [40, 'D', 5], [0, 'F', 0],
    ];

    /** Display order for letter grades in charts. Unknown letters are appended alphabetically. */
    public const GRADE_ORDER = ['O', 'S', 'A+', 'A', 'B+', 'B', 'C', 'D', 'E', 'P', 'F', 'AB'];

    public const HIST_BINS = [[0, 39.99], [40, 49.99], [50, 59.99], [60, 69.99], [70, 79.99], [80, 89.99], [90, 100]];

    public const FA_SA_GAP_ALERT = 20;   // percentage points between internal (FA) and external (SA) %

    public const SGPA_TOLERANCE = 0.05; // allowed difference between stored and recomputed SGPA

    public const RISK_CGPA = 6.0;  // students below this CGPA are flagged

    public const RISK_SGPA_DROP = 1.0;  // SGPA drop between consecutive semesters that is flagged

    public const MAX_DETAIL_ROWS = 1500;

    public const MAX_SCATTER = 2000;

    public const MAX_STUDENTS = 3000;

    private const MONTHS = [
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6,
        'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
    ];

    /**
     * @param  array  $rows  list of associative arrays (cast stdClass rows with (array) first)
     * @param  array  $opts  ['truncated' => bool, 'row_limit' => int]
     */
    public static function build(array $rows, array $opts = []): array
    {
        $recs = [];
        foreach ($rows as $i => $r) {
            $recs[] = self::normalise((array) $r, $i);
        }

        if (! $recs) {
            return ['empty' => true, 'meta' => ['row_count' => 0, 'truncated' => ! empty($opts['truncated'])]];
        }

        // ---------- effective attempt per student + semester + subject ----------
        $groups = [];
        foreach ($recs as $r) {
            $groups[$r['regno'].'|'.$r['sem_key'].'|'.$r['subcode']][] = $r;
        }
        $effective = [];
        $attemptsByKey = [];
        foreach ($groups as $key => $list) {
            usort($list, fn ($a, $b) => $a['order'] <=> $b['order']);
            $attemptsByKey[$key] = $list;
            $eff = self::pickEffective($list);
            $eff['attempts'] = count($list);
            $eff['first_passed'] = $list[0]['passed'];
            $effective[] = $eff;
        }

        $students = self::buildStudents($recs, $effective);
        $mode = count($students) === 1 ? 'student' : 'cohort';

        $semesters = self::semesterSeries($effective, $students);
        $subjects = self::subjectTable($effective, $recs);
        $reappear = self::reappearAnalysis($attemptsByKey);
        $sessions = self::sessionSeries($recs);
        $quality = self::qualityChecks($recs, $attemptsByKey, $reappear);
        $summary = self::summary($recs, $effective, $students, $reappear, $quality);
        $forecast = self::forecast($mode === 'student' ? reset($students)['sem_list'] : $semesters);

        $out = [
            'empty' => false,
            'meta' => [
                'mode' => $mode,
                'row_count' => count($recs),
                'truncated' => ! empty($opts['truncated']),
                'row_limit' => $opts['row_limit'] ?? null,
                'attempt_policy' => self::ATTEMPT_POLICY,
                'risk_cgpa' => self::RISK_CGPA,
                'gap_alert' => self::FA_SA_GAP_ALERT,
                'student' => $mode === 'student' ? self::profile(reset($students), $recs) : null,
            ],
            'summary' => $summary,
            'grades' => self::gradeDistribution($effective),
            'histogram' => self::histogram($effective),
            'divisions' => self::divisionDistribution($students),
            'semesters' => $semesters,
            'sessions' => $sessions,
            'subjects' => $subjects,
            'reappear' => $reappear,
            'groups' => [
                'program' => self::groupCompare($students, 'program'),
                'gender' => self::groupCompare($students, 'gender'),
                'regulation' => self::groupCompare($students, 'regulation'),
            ],
            'scatter' => self::scatter($effective),
            'students' => self::studentTable($students),
            'quality' => $quality,
            'forecast' => $forecast,
            'records' => self::detailRecords($recs, $effective),
        ];
        $out['insights'] = self::insights($out, $students);

        return $out;
    }

    // =====================================================================
    // Normalisation
    // =====================================================================

    private static function normalise(array $r, int $i): array
    {
        $num = fn ($v) => ($v === null || $v === '') ? null : (float) $v;

        $type = strtoupper(trim((string) ($r['mm_type'] ?? '')));
        $grade = strtoupper(trim((string) ($r['letter_grade'] ?? '')));
        $gp = $num($r['grade_points'] ?? null) ?? 0.0;
        $faC = $num($r['fa_conducted'] ?? null);
        $saC = $num($r['sa_conducted'] ?? null);
        $faE = $num($r['fa_earned'] ?? null);
        $saE = $num($r['sa_earned'] ?? null);
        $totC = $num($r['total_conducted'] ?? null);
        $totE = $num($r['total_earned'] ?? null);
        $year = (int) ($r['s_year'] ?? 0);
        $sem = (int) ($r['s_sem'] ?? 0);
        $examTs = self::monthKey($r['exam_mon_yyyy'] ?? '');
        $sgpa = $num($r['sgpa_perc'] ?? null);
        $cgpa = $num($r['cgpa_perc'] ?? null);
        $isReg = in_array($type, self::REGULAR_TYPES, true);

        return [
            'idx' => $i,
            'appno' => trim((string) ($r['appno'] ?? '')),
            'regno' => strtoupper(trim((string) ($r['regno'] ?? ''))),
            'mmno' => trim((string) ($r['mmno'] ?? '')),
            'subcode' => strtoupper(trim((string) ($r['subcode'] ?? ''))),
            'mm_type' => $type ?: 'UNKNOWN',
            'is_reappear' => ! $isReg,
            'exam' => trim((string) ($r['exam_mon_yyyy'] ?? '')),
            'exam_ts' => $examTs,
            'results' => trim((string) ($r['results_mon_yyyy'] ?? '')),
            'fa_c' => $faC, 'sa_c' => $saC, 'fa_e' => $faE, 'sa_e' => $saE, 'tot_c' => $totC, 'tot_e' => $totE,
            'pct' => ($totC && $totE !== null) ? $totE / $totC * 100 : null,
            'fa_pct' => ($faC && $faE !== null) ? $faE / $faC * 100 : null,
            'sa_pct' => ($saC && $saE !== null) ? $saE / $saC * 100 : null,
            'gp' => $gp,
            'grade' => $grade ?: '-',
            'credits' => $num($r['credits'] ?? null) ?? 0.0,
            'sgpa' => ($sgpa !== null && $sgpa > 0) ? $sgpa : null,
            'cgpa' => ($cgpa !== null && $cgpa > 0) ? $cgpa : null,
            'division' => trim((string) ($r['division_name'] ?? '')),
            'year' => $year,
            'sem' => $sem,
            'sem_key' => sprintf('%02d-%02d', $year, $sem),
            'sem_label' => "Y{$year} S{$sem}",
            'regulation' => trim((string) ($r['regulation'] ?? '')) ?: 'Not set',
            'program' => trim((string) ($r['program_short_name'] ?? '')) ?: 'Unmapped',
            'program_id' => $r['program_id'] ?? null,
            'gender' => trim((string) ($r['gender'] ?? '')) ?: 'Not set',
            'electiveid' => $r['electiveid'] ?? null,
            'passed' => ! in_array($grade, self::FAIL_GRADES, true) && $gp > 0,
            // sort order of attempts: exam month, then regular before reappear
            'order' => $examTs * 10 + ($isReg ? 0 : 1),
        ];
    }

    /** "AUGUST 2026", "Aug-2026", "08/2026" -> 202608 (0 when unparseable). */
    private static function monthKey(string $s): int
    {
        $s = strtoupper(trim($s));
        if (preg_match('/([A-Z]{3})[A-Z]*[\s\-\/]*(\d{4})/', $s, $m) && isset(self::MONTHS[$m[1]])) {
            return (int) $m[2] * 100 + self::MONTHS[$m[1]];
        }
        if (preg_match('/(\d{1,2})[\s\-\/](\d{4})/', $s, $m)) {
            return (int) $m[2] * 100 + (int) $m[1];
        }

        return 0;
    }

    private static function pickEffective(array $attempts): array
    {
        if (self::ATTEMPT_POLICY === 'latest') {
            return end($attempts);
        }
        $best = $attempts[0];
        foreach ($attempts as $a) {
            $cmp = [(int) $a['passed'], $a['gp'], $a['pct'] ?? -1, $a['order']]
                <=> [(int) $best['passed'], $best['gp'], $best['pct'] ?? -1, $best['order']];
            if ($cmp > 0) {
                $best = $a;
            }
        }

        return $best;
    }

    // =====================================================================
    // Per-student model
    // =====================================================================

    private static function buildStudents(array $recs, array $effective): array
    {
        $st = [];
        foreach ($effective as $e) {
            $s = &$st[$e['regno']];
            $s['regno'] = $e['regno'];
            $s['program'] = $e['program'];
            $s['gender'] = $e['gender'];
            $s['regulation'] = $e['regulation'];
            $s['eff'][] = $e;
            $s['sems'][$e['sem_key']]['label'] = $e['sem_label'];
            $s['sems'][$e['sem_key']]['eff'][] = $e;
            unset($s);
        }
        // latest stored SGPA / division per semester (from raw rows)
        foreach ($recs as $r) {
            if (! isset($st[$r['regno']]['sems'][$r['sem_key']])) {
                continue;
            }
            $sem = &$st[$r['regno']]['sems'][$r['sem_key']];
            if (! isset($sem['latest_ts']) || $r['exam_ts'] >= $sem['latest_ts']) {
                $sem['latest_ts'] = $r['exam_ts'];
                if ($r['sgpa'] !== null) {
                    $sem['sgpa_reported'] = $r['sgpa'];
                }
                if ($r['division'] !== '') {
                    $sem['division'] = $r['division'];
                }
            }
            unset($sem);
        }

        foreach ($st as &$s) {
            ksort($s['sems']);
            $cumCr = 0;
            $cumPts = 0;
            $prev = null;
            $list = [];
            foreach ($s['sems'] as $key => $sem) {
                [$cr, $pts, $earned] = self::creditPoints($sem['eff']);
                $cumCr += $cr;
                $cumPts += $pts;
                $sgpa = $cr > 0 ? $pts / $cr : null;
                $list[] = [
                    'key' => $key,
                    'label' => $sem['label'],
                    'sgpa' => $sgpa,
                    'sgpa_reported' => $sem['sgpa_reported'] ?? null,
                    'cgpa_running' => $cumCr > 0 ? $cumPts / $cumCr : null,
                    'credits_reg' => $cr,
                    'credits_earned' => $earned,
                    'avg_pct' => self::mean(array_column($sem['eff'], 'pct')),
                    'division' => $sem['division'] ?? null,
                    'delta' => ($prev !== null && $sgpa !== null) ? $sgpa - $prev : null,
                ];
                $prev = $sgpa;
            }
            [$cr, $pts, $earned] = self::creditPoints($s['eff']);
            $s['sem_list'] = $list;
            $s['cgpa'] = $cr > 0 ? $pts / $cr : null;
            $s['credits_reg'] = $cr;
            $s['credits_earned'] = $earned;
            $s['avg_pct'] = self::mean(array_column($s['eff'], 'pct'));
            $s['backlogs'] = count(array_filter($s['eff'], fn ($e) => ! $e['passed']));
            $last = end($list);
            $s['last_sgpa'] = $last['sgpa'] ?? null;
            $s['last_delta'] = $last['delta'] ?? null;
            $s['division'] = $last['division'] ?? null;
            $s['stored_cgpa'] = null;
            foreach ($s['eff'] as $e) {
                if ($e['cgpa'] !== null) {
                    $s['stored_cgpa'] = $e['cgpa'];
                }
            }
        }
        unset($s);

        return $st;
    }

    /** @return array [registered credits, credit*points, earned credits] */
    private static function creditPoints(array $eff): array
    {
        $cr = 0;
        $pts = 0;
        $earned = 0;
        foreach ($eff as $e) {
            $cr += $e['credits'];
            $pts += $e['credits'] * ($e['passed'] ? $e['gp'] : 0);
            if ($e['passed']) {
                $earned += $e['credits'];
            }
        }

        return [$cr, $pts, $earned];
    }

    private static function profile(array $s, array $recs): array
    {
        $r = $recs[0];

        return [
            'regno' => $s['regno'],
            'appno' => $r['appno'],
            'program' => $s['program'],
            'gender' => $s['gender'],
            'regulation' => $s['regulation'],
            'electiveid' => $r['electiveid'],
            'division' => $s['division'],
            'stored_cgpa' => $s['stored_cgpa'],
        ];
    }

    // =====================================================================
    // Summary KPIs
    // =====================================================================

    private static function summary(array $recs, array $eff, array $students, array $reappear, array $quality): array
    {
        $pcts = array_column($eff, 'pct');
        $passed = array_filter($eff, fn ($e) => $e['passed']);
        $cgpas = array_values(array_filter(array_column($students, 'cgpa'), fn ($v) => $v !== null));
        $reported = [];
        foreach ($students as $s) {
            foreach ($s['sem_list'] as $sem) {
                if ($sem['sgpa_reported'] !== null) {
                    $reported[] = $sem['sgpa_reported'];
                }
            }
        }
        $faE = $faC = $saE = $saC = 0;
        foreach ($eff as $e) {
            $faE += $e['fa_e'] ?? 0;
            $faC += $e['fa_c'] ?? 0;
            $saE += $e['sa_e'] ?? 0;
            $saC += $e['sa_c'] ?? 0;
        }
        $subjectsByPct = [];
        foreach ($eff as $e) {
            $subjectsByPct[$e['subcode']][] = $e['pct'];
        }
        $subjectAvg = array_map(fn ($v) => self::mean($v), $subjectsByPct);
        arsort($subjectAvg);

        $credReg = array_sum(array_column($students, 'credits_reg'));
        $credEarn = array_sum(array_column($students, 'credits_earned'));
        $distinct = count(array_filter($students, fn ($s) => $s['division'] && stripos($s['division'], 'DISTINCTION') !== false));
        $withBack = count(array_filter($students, fn ($s) => $s['backlogs'] > 0));
        $atRisk = count(array_filter($students, fn ($s) => self::isAtRisk($s)));

        return [
            'students' => count($students),
            'records' => count($recs),
            'regular_attempts' => count(array_filter($recs, fn ($r) => ! $r['is_reappear'])),
            'reappear_attempts' => count(array_filter($recs, fn ($r) => $r['is_reappear'])),
            'subject_results' => count($eff),
            'subjects' => count($subjectAvg),
            'semesters' => count(array_unique(array_column($eff, 'sem_key'))),
            'sessions' => count(array_unique(array_column($recs, 'exam'))),
            'avg_pct' => self::r(self::mean($pcts)),
            'median_pct' => self::r(self::median($pcts)),
            'std_pct' => self::r(self::std($pcts)),
            'min_pct' => self::r(self::minOf($pcts)),
            'max_pct' => self::r(self::maxOf($pcts)),
            'pass_pct' => self::r(self::ratio(count($passed), count($eff))),
            'first_attempt_pass_pct' => self::r(self::ratio(count(array_filter($eff, fn ($e) => $e['first_passed'])), count($eff))),
            'backlogs' => count($eff) - count($passed),
            'students_with_backlog' => $withBack,
            'all_clear_pct' => self::r(self::ratio(count($students) - $withBack, count($students))),
            'cgpa' => self::r(self::mean($cgpas), 2),
            'cgpa_median' => self::r(self::median($cgpas), 2),
            'cgpa_max' => self::r(self::maxOf($cgpas), 2),
            'cgpa_min' => self::r(self::minOf($cgpas), 2),
            'avg_sgpa_reported' => self::r(self::mean($reported), 2),
            'credits_registered' => self::r($credReg),
            'credits_earned' => self::r($credEarn),
            'credit_completion_pct' => self::r(self::ratio($credEarn, $credReg)),
            'fa_pct' => self::r(self::ratio($faE, $faC)),
            'sa_pct' => self::r(self::ratio($saE, $saC)),
            'fa_sa_gap' => self::r(self::ratio($faE, $faC) - self::ratio($saE, $saC)),
            'top_grade_pct' => self::r(self::ratio(count(array_filter($eff, fn ($e) => $e['gp'] >= 9)), count($eff))),
            'avg_grade_points' => self::r(self::mean(array_column($eff, 'gp')), 2),
            'distinction_students' => $distinct,
            'distinction_pct' => self::r(self::ratio($distinct, count($students))),
            'at_risk_students' => $atRisk,
            'best_subject' => $subjectAvg ? ['code' => array_key_first($subjectAvg), 'pct' => self::r(reset($subjectAvg))] : null,
            'weakest_subject' => $subjectAvg ? ['code' => array_key_last($subjectAvg), 'pct' => self::r(end($subjectAvg))] : null,
            'reappear_avg_gain' => $reappear['summary']['avg_gain'],
            'quality_issues' => array_sum(array_map(fn ($q) => $q['severity'] === 'info' ? 0 : $q['count'], $quality)),
        ];
    }

    private static function isAtRisk(array $s): bool
    {
        return $s['backlogs'] > 0
            || ($s['cgpa'] !== null && $s['cgpa'] < self::RISK_CGPA)
            || ($s['last_delta'] !== null && $s['last_delta'] <= -self::RISK_SGPA_DROP);
    }

    // =====================================================================
    // Chart series
    // =====================================================================

    private static function gradeDistribution(array $eff): array
    {
        $counts = [];
        $gp = [];
        foreach ($eff as $e) {
            $counts[$e['grade']] = ($counts[$e['grade']] ?? 0) + 1;
            $gp[$e['grade']][] = $e['gp'];
        }
        $order = array_flip(self::GRADE_ORDER);
        uksort($counts, function ($a, $b) use ($order) {
            $ia = $order[$a] ?? 100;
            $ib = $order[$b] ?? 100;

            return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
        });
        $total = array_sum($counts);
        $out = [];
        foreach ($counts as $g => $c) {
            $out[] = [
                'grade' => $g,
                'count' => $c,
                'share' => self::r(self::ratio($c, $total)),
                'avg_gp' => self::r(self::mean($gp[$g]), 2),
                'fail' => in_array($g, self::FAIL_GRADES, true),
            ];
        }

        return $out;
    }

    private static function histogram(array $eff): array
    {
        $out = [];
        foreach (self::HIST_BINS as [$lo, $hi]) {
            $out[] = [
                'label' => $lo.'-'.(int) ceil($hi),
                'count' => count(array_filter($eff, fn ($e) => $e['pct'] !== null && $e['pct'] >= $lo && $e['pct'] <= $hi)),
            ];
        }

        return $out;
    }

    private static function divisionDistribution(array $students): array
    {
        $c = [];
        foreach ($students as $s) {
            foreach ($s['sem_list'] as $sem) {
                $d = $sem['division'] ?: 'Not recorded';
                $c[$d] = ($c[$d] ?? 0) + 1;
            }
        }
        arsort($c);

        return array_map(fn ($k, $v) => ['division' => $k, 'count' => $v], array_keys($c), $c);
    }

    private static function semesterSeries(array $eff, array $students): array
    {
        $by = [];
        foreach ($eff as $e) {
            $by[$e['sem_key']]['label'] = $e['sem_label'];
            $by[$e['sem_key']]['eff'][] = $e;
        }
        ksort($by);
        $out = [];
        foreach ($by as $key => $g) {
            $sg = $rep = $cg = $cr = $ce = [];
            foreach ($students as $s) {
                foreach ($s['sem_list'] as $sem) {
                    if ($sem['key'] !== $key) {
                        continue;
                    }
                    $sg[] = $sem['sgpa'];
                    $rep[] = $sem['sgpa_reported'];
                    $cg[] = $sem['cgpa_running'];
                    $cr[] = $sem['credits_reg'];
                    $ce[] = $sem['credits_earned'];
                }
            }
            $passed = count(array_filter($g['eff'], fn ($e) => $e['passed']));
            $out[] = [
                'key' => $key,
                'label' => $g['label'],
                'students' => count($sg),
                'subject_results' => count($g['eff']),
                'sgpa' => self::r(self::mean($sg), 2),
                'sgpa_reported' => self::r(self::mean($rep), 2),
                'cgpa_running' => self::r(self::mean($cg), 2),
                'avg_pct' => self::r(self::mean(array_column($g['eff'], 'pct'))),
                'fa_pct' => self::r(self::mean(array_column($g['eff'], 'fa_pct'))),
                'sa_pct' => self::r(self::mean(array_column($g['eff'], 'sa_pct'))),
                'pass_pct' => self::r(self::ratio($passed, count($g['eff']))),
                'credits_reg_avg' => self::r(self::mean($cr)),
                'credits_earned_avg' => self::r(self::mean($ce)),
            ];
        }

        return $out;
    }

    private static function sessionSeries(array $recs): array
    {
        $by = [];
        foreach ($recs as $r) {
            $k = $r['exam_ts'].'|'.$r['exam'];
            $by[$k][] = $r;
        }
        ksort($by);
        $out = [];
        foreach ($by as $list) {
            $passed = count(array_filter($list, fn ($r) => $r['passed']));
            $out[] = [
                'exam' => $list[0]['exam'],
                'results' => $list[0]['results'],
                'records' => count($list),
                'students' => count(array_unique(array_column($list, 'regno'))),
                'regular' => count(array_filter($list, fn ($r) => ! $r['is_reappear'])),
                'reappear' => count(array_filter($list, fn ($r) => $r['is_reappear'])),
                'avg_pct' => self::r(self::mean(array_column($list, 'pct'))),
                'pass_pct' => self::r(self::ratio($passed, count($list))),
                'avg_gp' => self::r(self::mean(array_column($list, 'gp')), 2),
            ];
        }

        return $out;
    }

    private static function subjectTable(array $eff, array $recs): array
    {
        $by = [];
        foreach ($eff as $e) {
            $by[$e['subcode']][] = $e;
        }
        $attempts = array_count_values(array_column($recs, 'subcode'));
        $reapp = array_count_values(array_column(array_filter($recs, fn ($r) => $r['is_reappear']), 'subcode'));
        $out = [];
        foreach ($by as $code => $list) {
            $fa = self::mean(array_column($list, 'fa_pct'));
            $sa = self::mean(array_column($list, 'sa_pct'));
            $pcts = array_column($list, 'pct');
            $out[] = [
                'subcode' => $code,
                'results' => count($list),
                'attempts' => $attempts[$code] ?? 0,
                'reappears' => $reapp[$code] ?? 0,
                'semesters' => implode(', ', array_unique(array_column($list, 'sem_label'))),
                'credits' => self::r(self::mean(array_column($list, 'credits')), 1),
                'avg_pct' => self::r(self::mean($pcts)),
                'max_pct' => self::r(self::maxOf($pcts)),
                'min_pct' => self::r(self::minOf($pcts)),
                'std_pct' => self::r(self::std($pcts)),
                'fa_pct' => self::r($fa),
                'sa_pct' => self::r($sa),
                'gap' => ($fa !== null && $sa !== null) ? self::r($fa - $sa) : null,
                'avg_gp' => self::r(self::mean(array_column($list, 'gp')), 2),
                'pass_pct' => self::r(self::ratio(count(array_filter($list, fn ($e) => $e['passed'])), count($list))),
            ];
        }
        usort($out, fn ($a, $b) => $b['avg_pct'] <=> $a['avg_pct']);

        return $out;
    }

    private static function reappearAnalysis(array $attemptsByKey): array
    {
        $rows = [];
        foreach ($attemptsByKey as $list) {
            for ($i = 1; $i < count($list); $i++) {
                $a = $list[$i];
                if (! $a['is_reappear']) {
                    continue;
                }
                $b = $list[$i - 1];
                $identical = $a['fa_e'] === $b['fa_e'] && $a['sa_e'] === $b['sa_e']
                    && $a['tot_e'] === $b['tot_e'] && $a['grade'] === $b['grade'];
                $rows[] = [
                    'regno' => $a['regno'],
                    'subcode' => $a['subcode'],
                    'semester' => $a['sem_label'],
                    'before_exam' => $b['exam'],
                    'after_exam' => $a['exam'],
                    'before_pct' => self::r($b['pct']),
                    'after_pct' => self::r($a['pct']),
                    'gain' => ($a['pct'] !== null && $b['pct'] !== null) ? self::r($a['pct'] - $b['pct']) : null,
                    'before_grade' => $b['grade'],
                    'after_grade' => $a['grade'],
                    'gp_gain' => self::r($a['gp'] - $b['gp'], 1),
                    'kind' => $b['passed'] ? 'Improvement' : ($a['passed'] ? 'Backlog cleared' : 'Still pending'),
                    'identical' => $identical,
                ];
            }
        }
        $gains = array_column($rows, 'gain');

        return [
            'rows' => $rows,
            'summary' => [
                'attempts' => count($rows),
                'avg_gain' => self::r(self::mean($gains)),
                'improved' => count(array_filter($rows, fn ($r) => ($r['gain'] ?? 0) > 0)),
                'unchanged' => count(array_filter($rows, fn ($r) => ($r['gain'] ?? 0) == 0)),
                'declined' => count(array_filter($rows, fn ($r) => ($r['gain'] ?? 0) < 0)),
                'backlogs_cleared' => count(array_filter($rows, fn ($r) => $r['kind'] === 'Backlog cleared')),
                'improvement_only' => count(array_filter($rows, fn ($r) => $r['kind'] === 'Improvement')),
                'still_pending' => count(array_filter($rows, fn ($r) => $r['kind'] === 'Still pending')),
                'identical' => count(array_filter($rows, fn ($r) => $r['identical'])),
            ],
        ];
    }

    private static function groupCompare(array $students, string $field): array
    {
        $by = [];
        foreach ($students as $s) {
            $by[$s[$field]][] = $s;
        }
        $out = [];
        foreach ($by as $k => $list) {
            $eff = array_merge(...array_column($list, 'eff'));
            $out[] = [
                'name' => (string) $k,
                'students' => count($list),
                'avg_cgpa' => self::r(self::mean(array_column($list, 'cgpa')), 2),
                'avg_pct' => self::r(self::mean(array_column($eff, 'pct'))),
                'pass_pct' => self::r(self::ratio(count(array_filter($eff, fn ($e) => $e['passed'])), count($eff))),
                'backlog_students' => count(array_filter($list, fn ($s) => $s['backlogs'] > 0)),
            ];
        }
        usort($out, fn ($a, $b) => $b['avg_cgpa'] <=> $a['avg_cgpa']);

        return $out;
    }

    private static function scatter(array $eff): array
    {
        $pts = [];
        foreach ($eff as $e) {
            if ($e['fa_pct'] !== null && $e['sa_pct'] !== null) {
                $pts[] = ['x' => self::r($e['fa_pct']), 'y' => self::r($e['sa_pct']), 's' => $e['subcode'], 'g' => $e['regno']];
            }
        }
        $r = self::pearson(array_column($pts, 'x'), array_column($pts, 'y'));
        if (count($pts) > self::MAX_SCATTER) {
            shuffle($pts);
            $pts = array_slice($pts, 0, self::MAX_SCATTER);
        }

        return ['points' => $pts, 'r' => $r === null ? null : self::r($r, 2)];
    }

    private static function studentTable(array $students): array
    {
        $out = [];
        foreach ($students as $s) {
            $out[] = [
                'regno' => $s['regno'],
                'program' => $s['program'],
                'gender' => $s['gender'],
                'cgpa' => self::r($s['cgpa'], 2),
                'avg_pct' => self::r($s['avg_pct']),
                'backlogs' => $s['backlogs'],
                'credits_earned' => self::r($s['credits_earned']),
                'credits_reg' => self::r($s['credits_reg']),
                'last_sgpa' => self::r($s['last_sgpa'], 2),
                'delta' => self::r($s['last_delta'], 2),
                'division' => $s['division'],
                'at_risk' => self::isAtRisk($s),
                'sems' => array_map(fn ($x) => ['label' => $x['label'], 'sgpa' => self::r($x['sgpa'], 2)], $s['sem_list']),
            ];
        }
        usort($out, fn ($a, $b) => ($b['cgpa'] ?? -1) <=> ($a['cgpa'] ?? -1));

        return array_slice($out, 0, self::MAX_STUDENTS);
    }

    private static function detailRecords(array $recs, array $eff): array
    {
        $effIdx = array_flip(array_column($eff, 'idx'));
        usort($recs, fn ($a, $b) => [$a['regno'], $a['sem_key'], $a['subcode'], $a['order']]
            <=> [$b['regno'], $b['sem_key'], $b['subcode'], $b['order']]);
        $out = [];
        foreach (array_slice($recs, 0, self::MAX_DETAIL_ROWS) as $r) {
            $out[] = [
                'regno' => $r['regno'], 'sem' => $r['sem_label'], 'exam' => $r['exam'], 'type' => $r['mm_type'],
                'subcode' => $r['subcode'], 'fa' => $r['fa_e'], 'fa_c' => $r['fa_c'], 'sa' => $r['sa_e'], 'sa_c' => $r['sa_c'],
                'total' => $r['tot_e'], 'total_c' => $r['tot_c'], 'pct' => self::r($r['pct']), 'grade' => $r['grade'],
                'gp' => $r['gp'], 'credits' => $r['credits'], 'sgpa' => $r['sgpa'], 'division' => $r['division'],
                'passed' => $r['passed'], 'counted' => isset($effIdx[$r['idx']]), 'mmno' => $r['mmno'],
                'program' => $r['program'],
            ];
        }

        return $out;
    }

    // =====================================================================
    // Forecast (least squares over semester SGPA)
    // =====================================================================

    private static function forecast(array $semList): array
    {
        $ys = [];
        foreach ($semList as $s) {
            if ($s['sgpa'] !== null) {
                $ys[] = (float) $s['sgpa'];
            }
        }
        $n = count($ys);
        if ($n < 2) {
            return ['available' => false, 'reason' => 'Needs at least two semesters with results.'];
        }
        $xs = range(1, $n);
        $mx = array_sum($xs) / $n;
        $my = array_sum($ys) / $n;
        $sxy = $sxx = $sst = 0;
        foreach ($xs as $i => $x) {
            $sxy += ($x - $mx) * ($ys[$i] - $my);
            $sxx += ($x - $mx) ** 2;
            $sst += ($ys[$i] - $my) ** 2;
        }
        $b = $sxx ? $sxy / $sxx : 0;
        $a = $my - $b * $mx;
        $sse = 0;
        foreach ($xs as $i => $x) {
            $sse += ($ys[$i] - ($a + $b * $x)) ** 2;
        }
        $next = max(0, min(10, $a + $b * ($n + 1)));

        return [
            'available' => true,
            'next_sgpa' => self::r($next, 2),
            'slope' => self::r($b, 3),
            'r2' => $sst > 0 ? self::r(1 - $sse / $sst, 2) : null,
            'points' => $n,
            'confidence' => $n >= 4 ? 'moderate' : 'low',
        ];
    }

    // =====================================================================
    // Data quality
    // =====================================================================

    private static function qualityChecks(array $recs, array $attemptsByKey, array $reappear): array
    {
        $issues = [];
        $add = function (string $id, string $sev, string $title, string $detail, array $samples) use (&$issues) {
            if ($samples) {
                $issues[] = ['id' => $id, 'severity' => $sev, 'title' => $title, 'detail' => $detail,
                    'count' => count($samples), 'samples' => array_slice($samples, 0, 6)];
            }
        };
        $tag = fn ($r) => "{$r['regno']} {$r['subcode']} ({$r['exam']})";

        // 1. letter grade / grade points vs marks band
        $scaleLetters = array_column(self::GRADE_SCALE, 1);
        $s = [];
        foreach ($recs as $r) {
            if ($r['pct'] === null || ! in_array($r['grade'], $scaleLetters, true)) {
                continue;
            }
            [$letter, $points] = self::expectedGrade($r['pct']);
            if ($letter !== $r['grade'] || abs($points - $r['gp']) > 0.01) {
                $s[] = $tag($r).": {$r['tot_e']}/{$r['tot_c']} stored {$r['grade']}/{$r['gp']}, scale says {$letter}/{$points}";
            }
        }
        $add('grade_band', 'high', 'Grade does not match marks',
            'Letter grade or grade points differ from the configured grade scale for the marks scored. Confirm GRADE_SCALE matches the regulation, then re-check these results.', $s);

        // 2. FA + SA != total
        $s = [];
        foreach ($recs as $r) {
            if ($r['fa_e'] !== null && $r['sa_e'] !== null && $r['tot_e'] !== null
                && abs($r['fa_e'] + $r['sa_e'] - $r['tot_e']) > 0.01) {
                $s[] = $tag($r).": {$r['fa_e']} + {$r['sa_e']} != {$r['tot_e']}";
            }
        }
        $add('component_sum', 'high', 'Internal + external does not equal total', 'fa_earned + sa_earned should equal total_earned.', $s);

        // 3. earned above maximum
        $s = [];
        foreach ($recs as $r) {
            foreach ([['fa_e', 'fa_c', 'FA'], ['sa_e', 'sa_c', 'SA'], ['tot_e', 'tot_c', 'Total']] as [$e, $c, $lbl]) {
                if ($r[$e] !== null && $r[$c] !== null && $r[$e] > $r[$c]) {
                    $s[] = $tag($r).": {$lbl} {$r[$e]} > {$r[$c]}";
                }
            }
        }
        $add('over_max', 'high', 'Marks above maximum', 'Earned marks exceed conducted (maximum) marks.', $s);

        // 4. stored SGPA vs SGPA recomputed from credits x grade points
        $s = [];
        $byStudentSem = [];
        foreach ($attemptsByKey as $list) {
            foreach ($list as $a) {
                $byStudentSem[$a['regno'].'|'.$a['sem_key']][] = $a;
            }
        }
        foreach ($byStudentSem as $list) {
            $sessions = [];
            foreach ($list as $a) {
                if ($a['sgpa'] !== null) {
                    $sessions[$a['exam_ts']] = ['sgpa' => $a['sgpa'], 'exam' => $a['exam']];
                }
            }
            ksort($sessions);
            foreach ($sessions as $ts => $info) {
                $asOf = array_filter($list, fn ($a) => $a['exam_ts'] <= $ts);
                $bySub = [];
                foreach ($asOf as $a) {
                    $bySub[$a['subcode']][] = $a;
                }
                $eff = array_map(fn ($x) => self::pickEffective($x), $bySub);
                [$cr, $pts] = self::creditPoints($eff);
                if ($cr <= 0) {
                    continue;
                }
                $calc = $pts / $cr;
                if (abs($calc - $info['sgpa']) > self::SGPA_TOLERANCE) {
                    $s[] = "{$list[0]['regno']} {$list[0]['sem_label']} ({$info['exam']}): stored "
                        .self::fmt($info['sgpa']).', recomputed '.self::fmt($calc);
                }
            }
        }
        $add('sgpa_mismatch', 'high', 'Stored SGPA differs from recomputed SGPA',
            'SGPA recomputed as sum(credits x grade points) / sum(credits) over the counted attempts up to that exam does not match sgpa_perc.', $s);

        // 5. reappear rows identical to the previous attempt
        $s = [];
        foreach ($reappear['rows'] as $r) {
            if ($r['identical']) {
                $s[] = "{$r['regno']} {$r['subcode']}: {$r['after_exam']} copies {$r['before_exam']}";
            }
        }
        $add('identical_reappear', 'medium', 'Reappear marks identical to earlier attempt',
            'Every component matches the previous attempt exactly. This is often a copied row rather than a real re-examination.', $s);

        // 6. duplicate rows (same student, subject, exam, type) - often caused by the registrations join
        $seen = [];
        $s = [];
        foreach ($recs as $r) {
            $k = "{$r['regno']}|{$r['subcode']}|{$r['exam']}|{$r['mm_type']}|{$r['sem_key']}";
            if (isset($seen[$k])) {
                $s[] = $tag($r)." {$r['mm_type']}";
            }
            $seen[$k] = true;
        }
        $add('duplicates', 'high', 'Duplicate result rows',
            'The same student, subject, exam and type appear more than once. Check for multiple rows per register_no in student_registrations.', $s);

        // 7. same subject in more than one semester for one student
        $subSems = [];
        $s = [];
        foreach ($recs as $r) {
            $subSems[$r['regno'].'|'.$r['subcode']][$r['sem_label']] = true;
        }
        foreach ($subSems as $k => $sems) {
            if (count($sems) > 1) {
                [$reg, $sub] = explode('|', $k);
                $s[] = "{$reg} {$sub}: ".implode(', ', array_keys($sems));
            }
        }
        $add('subject_multi_sem', 'medium', 'Subject code in more than one semester',
            'A subject code is recorded under different semesters for the same student. Check the subject mapping or s_year/s_sem.', $s);

        // 8. CGPA column empty
        $s = [];
        foreach ($recs as $r) {
            if ($r['cgpa'] === null) {
                $s[] = $tag($r);
            }
        }
        if (count($s) === count($recs)) {
            $add('cgpa_missing', 'info', 'cgpa_perc is not populated',
                'All rows have cgpa_perc = 0 or empty. The dashboard computes CGPA from credits and grade points instead.', $s);
        } else {
            $add('cgpa_missing', 'medium', 'cgpa_perc missing on some rows', 'Some rows have cgpa_perc = 0 or empty.', $s);
        }

        // 9. missing join data / credits
        $s = array_map($tag, array_filter($recs, fn ($r) => $r['program'] === 'Unmapped'));
        $add('no_program', 'medium', 'Program not mapped', 'No program found through student_registrations -> electives -> programs.', $s);
        $s = array_map($tag, array_filter($recs, fn ($r) => $r['credits'] <= 0));
        $add('no_credits', 'high', 'Zero or missing credits', 'Rows with credits <= 0 are ignored in SGPA/CGPA.', $s);
        $s = array_map($tag, array_filter($recs, fn ($r) => $r['exam_ts'] === 0));
        $add('bad_exam_month', 'medium', 'Exam month not readable', 'exam_mon_yyyy could not be parsed, so attempt order may be wrong.', $s);

        $rank = ['high' => 0, 'medium' => 1, 'info' => 2];
        usort($issues, fn ($a, $b) => $rank[$a['severity']] <=> $rank[$b['severity']]);

        return $issues;
    }

    private static function expectedGrade(float $pct): array
    {
        foreach (self::GRADE_SCALE as [$min, $letter, $points]) {
            if ($pct >= $min) {
                return [$letter, $points];
            }
        }
        $scale = self::GRADE_SCALE;
        $last = end($scale);

        return [$last[1], $last[2]];
    }

    // =====================================================================
    // Rule-based insights (deterministic statistics - not a language model)
    // =====================================================================

    private static function insights(array $d, array $students): array
    {
        $S = $d['summary'];
        $mode = $d['meta']['mode'];
        $ins = [];
        $add = function ($kind, $area, $title, $text) use (&$ins) {
            $ins[] = ['kind' => $kind, 'area' => $area, 'title' => $title, 'text' => $text];
        };

        // ---- overall standing
        if ($mode === 'student') {
            $p = $d['meta']['student'];
            $add($S['backlogs'] ? 'risk' : 'good', 'Overall', 'Current standing',
                "{$p['regno']} ({$p['program']}, {$p['regulation']}) has a computed CGPA of ".self::fmt($S['cgpa'])
                ." over {$S['credits_registered']} credits, with {$S['credits_earned']} credits earned and "
                .($S['backlogs'] ? "{$S['backlogs']} subject(s) still to clear." : 'no pending backlogs.'));
        } else {
            $add('info', 'Overall', 'Cohort standing',
                "{$S['students']} students, {$S['subject_results']} subject results. Average CGPA ".self::fmt($S['cgpa'])
                .' (median '.self::fmt($S['cgpa_median'])."), pass rate {$S['pass_pct']}%, "
                ."{$S['all_clear_pct']}% of students with no backlog.");
        }

        // ---- semester trend
        $sems = $mode === 'student' ? reset($students)['sem_list'] : $d['semesters'];
        $sg = array_values(array_filter(array_map(fn ($x) => $x['sgpa'], $sems), fn ($v) => $v !== null));
        if (count($sg) >= 2) {
            $delta = end($sg) - $sg[0];
            $kind = $delta >= 0.25 ? 'good' : ($delta <= -0.25 ? 'risk' : 'info');
            $word = $delta >= 0.25 ? 'improved' : ($delta <= -0.25 ? 'declined' : 'stayed steady');
            $add($kind, 'Trend', 'Semester trend',
                'SGPA '.$word.' from '.self::fmt($sg[0]).' to '.self::fmt(end($sg))
                .' across '.count($sg).' semesters ('.($delta >= 0 ? '+' : '').self::fmt($delta).').');
        }
        if ($d['forecast']['available']) {
            $f = $d['forecast'];
            $add('info', 'Forecast', 'Next semester projection',
                'A straight-line fit over '.$f['points'].' semesters projects the next SGPA near '.self::fmt($f['next_sgpa'])
                ." (confidence: {$f['confidence']}). Treat this as a direction, not a prediction.");
        }

        // ---- subjects
        $subs = $d['subjects'];
        if (count($subs) >= 2) {
            $top = array_slice($subs, 0, min(2, count($subs)));
            $low = array_slice($subs, -min(2, count($subs)));
            $add('good', 'Subjects', 'Strongest subjects',
                implode(', ', array_map(fn ($x) => "{$x['subcode']} ({$x['avg_pct']}%)", $top)).' lead the results.');
            $add('watch', 'Subjects', 'Subjects needing attention',
                implode(', ', array_map(fn ($x) => "{$x['subcode']} ({$x['avg_pct']}%, pass {$x['pass_pct']}%)", $low))
                .' are the lowest. '.($mode === 'student'
                    ? 'Prioritise revision and the external exam pattern here.'
                    : 'Review question paper difficulty, teaching load and remedial classes for these.'));
        }

        // ---- internal vs external
        if ($S['fa_pct'] !== null && $S['sa_pct'] !== null) {
            $gap = $S['fa_sa_gap'];
            $gapSubs = array_filter($subs, fn ($x) => $x['gap'] !== null && $x['gap'] >= self::FA_SA_GAP_ALERT);
            usort($gapSubs, fn ($x, $y) => $y['gap'] <=> $x['gap']);
            $kind = $gap >= self::FA_SA_GAP_ALERT ? 'risk' : ($gap >= 10 ? 'watch' : 'good');
            $txt = "Internal (FA) average is {$S['fa_pct']}% against external (SA) {$S['sa_pct']}%, a gap of {$gap} points.";
            if ($gapSubs) {
                $txt .= ' Largest gaps: '.implode(', ', array_map(fn ($x) => "{$x['subcode']} ({$x['gap']})", array_slice($gapSubs, 0, 3))).'.';
            }
            $txt .= $gap >= 10 ? ' A wide gap can mean lenient internal marking or weak exam preparation.' : ' Internal and external marks are consistent.';
            $add($kind, 'Assessment', 'Internal vs external', $txt);
        }
        if ($d['scatter']['r'] !== null && count($d['scatter']['points']) >= 5) {
            $r = $d['scatter']['r'];
            $add('info', 'Assessment', 'Do internal marks predict external marks?',
                "Correlation between FA% and SA% is r = {$r}. ".(abs($r) >= 0.5
                    ? 'Internal marks are a reasonable early signal for external results.'
                    : 'Internal marks are a weak predictor, so early-warning on internals alone may miss students.'));
        }

        // ---- consistency
        if ($S['std_pct'] !== null) {
            $add($S['std_pct'] > 15 ? 'watch' : 'good', 'Consistency', 'Spread of marks',
                "Marks range {$S['min_pct']}%-{$S['max_pct']}% with a standard deviation of {$S['std_pct']} points. "
                .($S['std_pct'] > 15 ? 'Performance is uneven across subjects.' : 'Performance is fairly even across subjects.'));
        }

        // ---- reappear
        $R = $d['reappear']['summary'];
        if ($R['attempts']) {
            $txt = "{$R['attempts']} reappear attempt(s): {$R['backlogs_cleared']} cleared a backlog, {$R['improvement_only']} were improvement attempts, "
                ."{$R['still_pending']} still pending. Average change {$R['avg_gain']} points.";
            if ($R['identical']) {
                $txt .= " {$R['identical']} attempt(s) are identical to the previous attempt - verify these entries.";
            }
            $add($R['identical'] ? 'watch' : 'info', 'Reappear', 'Reappear effectiveness', $txt);
        }

        // ---- cohort-only
        if ($mode === 'cohort') {
            $prog = $d['groups']['program'];
            if (count($prog) >= 2) {
                $a = $prog[0];
                $z = end($prog);
                $add('info', 'Programs', 'Program comparison',
                    "{$a['name']} has the highest average CGPA (".self::fmt($a['avg_cgpa']).") and {$z['name']} the lowest (".self::fmt($z['avg_cgpa']).').');
            }
            $gen = $d['groups']['gender'];
            if (count($gen) >= 2) {
                $add('info', 'Gender', 'Gender comparison',
                    implode('; ', array_map(fn ($g) => "{$g['name']}: CGPA ".self::fmt($g['avg_cgpa']).", pass {$g['pass_pct']}%", $gen)).'.');
            }
            if ($S['at_risk_students']) {
                $add('risk', 'Risk', 'Students at risk',
                    "{$S['at_risk_students']} student(s) have a backlog, CGPA below ".self::RISK_CGPA
                    .' or an SGPA drop of '.self::RISK_SGPA_DROP.'+ - see the at-risk list for mentoring.');
            }
            $hard = array_filter($subs, fn ($x) => $x['pass_pct'] !== null && $x['pass_pct'] < 75);
            if ($hard) {
                usort($hard, fn ($a, $b) => $a['pass_pct'] <=> $b['pass_pct']);
                $add('risk', 'Subjects', 'Low pass-rate subjects',
                    'Below a 75% pass rate: '.implode(', ', array_map(fn ($x) => "{$x['subcode']} ({$x['pass_pct']}%)", array_slice($hard, 0, 5))).'.');
            }
        }

        // ---- quality
        $hi = array_filter($d['quality'], fn ($q) => $q['severity'] === 'high');
        if ($hi) {
            $add('risk', 'Data quality', 'Results need verification',
                count($hi).' high-severity check(s) failed: '.implode('; ', array_map(fn ($q) => "{$q['title']} ({$q['count']})", $hi))
                .'. Fix these before publishing or using the KPIs for decisions.');
        }

        return $ins;
    }

    // =====================================================================
    // Math helpers
    // =====================================================================

    private static function clean(array $v): array
    {
        return array_values(array_filter($v, fn ($x) => $x !== null && is_numeric($x)));
    }

    private static function mean(array $v): ?float
    {
        $v = self::clean($v);

        return $v ? array_sum($v) / count($v) : null;
    }

    private static function median(array $v): ?float
    {
        $v = self::clean($v);
        if (! $v) {
            return null;
        }
        sort($v);
        $n = count($v);

        return $n % 2 ? $v[intdiv($n, 2)] : ($v[$n / 2 - 1] + $v[$n / 2]) / 2;
    }

    private static function std(array $v): ?float
    {
        $v = self::clean($v);
        if (count($v) < 2) {
            return null;
        }
        $m = array_sum($v) / count($v);

        return sqrt(array_sum(array_map(fn ($x) => ($x - $m) ** 2, $v)) / (count($v) - 1));
    }

    private static function minOf(array $v): ?float
    {
        $v = self::clean($v);

        return $v ? min($v) : null;
    }

    private static function maxOf(array $v): ?float
    {
        $v = self::clean($v);

        return $v ? max($v) : null;
    }

    private static function ratio($a, $b): ?float
    {
        return $b ? $a / $b * 100 : null;
    }

    private static function pearson(array $x, array $y): ?float
    {
        $n = count($x);
        if ($n < 3) {
            return null;
        }
        $mx = array_sum($x) / $n;
        $my = array_sum($y) / $n;
        $sxy = $sxx = $syy = 0;
        for ($i = 0; $i < $n; $i++) {
            $sxy += ($x[$i] - $mx) * ($y[$i] - $my);
            $sxx += ($x[$i] - $mx) ** 2;
            $syy += ($y[$i] - $my) ** 2;
        }

        return ($sxx && $syy) ? $sxy / sqrt($sxx * $syy) : null;
    }

    private static function r(?float $v, int $d = 1): ?float
    {
        return $v === null ? null : round($v, $d);
    }

    private static function fmt(?float $v): string
    {
        return $v === null ? '-' : number_format($v, 2);
    }
}
