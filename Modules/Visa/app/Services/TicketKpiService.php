<?php

namespace Modules\Visa\Services;

/**
 * Ticket KPI & AI Perception engine.
 *
 * Pure PHP (no DB / framework calls) so it is fast and easy to test.
 * Input rows (one per ticket), all times as UNIX timestamps (int|null):
 *   tkt_id, regno, status (0..3), category, employee_code, employee_name,
 *   raised_at, pending_at, in_process_at, resolved_at
 *
 * Status codes: 0 = Raised, 1 = Pending, 2 = In Process, 3 = Resolved
 */
class TicketKpiService
{
    /** Target time for staff to open (acknowledge) a new ticket. */
    public const SLA_RESPONSE_HOURS = 4;

    /** Target time to fully resolve a ticket. */
    public const SLA_RESOLVE_HOURS = 48;

    public const STATUS_LABELS = [0 => 'Raised', 1 => 'Pending', 2 => 'In Process', 3 => 'Resolved'];

    /**
     * @param  array  $periodRows  tickets inside the selected period (drives most KPIs)
     * @param  array  $allRows  all tickets (used for 14-day trend & week-over-week)
     * @param  int  $now  current UNIX time
     * @param  bool  $hasStageTimestamps  whether pending_at / in_process_at / resolved_at columns exist
     */
    public static function build(array $periodRows, array $allRows, int $now, bool $hasStageTimestamps = true): array
    {
        $slaResp = self::SLA_RESPONSE_HOURS * 3600;
        $slaRes = self::SLA_RESOLVE_HOURS * 3600;

        $counts = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        $firstResponse = [];   // raised -> pending (first time staff opened it)
        $pendingToProc = [];   // pending -> in process
        $procToResolve = [];   // in process -> resolved
        $resolution = [];      // raised -> resolved
        $openAges = [];
        $aging = ['< 4h' => 0, '4–24h' => 0, '1–3 days' => 0, '3–7 days' => 0, '> 7 days' => 0];
        $reachedProcess = 0;
        $openBreachResolve = 0;
        $unackBreach = 0;
        $oldestUnackAge = 0;
        $hourly = array_fill(0, 24, 0);
        $weekday = array_fill(0, 7, 0); // 0 = Mon
        $categories = [];
        $staff = [];
        $students = [];
        $openList = [];
        $resolvedWithinSla = 0;

        foreach ($periodRows as $r) {
            $s = (int) $r['status'];
            if (! isset($counts[$s])) {
                continue;
            }
            $counts[$s]++;
            $raised = $r['raised_at'];
            $pend = $r['pending_at'] ?? null;
            $proc = $r['in_process_at'] ?? null;
            $res = $s === 3 ? ($r['resolved_at'] ?? null) : null;
            $cat = trim((string) ($r['category'] ?? '')) ?: 'Uncategorised';

            if ($proc || $s >= 2) {
                $reachedProcess++;
            }

            if ($raised) {
                $hourly[(int) date('G', $raised)]++;
                $weekday[(int) date('N', $raised) - 1]++;
            }

            $d = self::diff($raised, $pend);
            if ($d !== null) {
                $firstResponse[] = $d;
            }
            $d = self::diff($pend, $proc);
            if ($d !== null) {
                $pendingToProc[] = $d;
            }
            $d = self::diff($proc, $res);
            if ($d !== null) {
                $procToResolve[] = $d;
            }
            $resSec = self::diff($raised, $res);
            if ($resSec !== null) {
                $resolution[] = $resSec;
                if ($resSec <= $slaRes) {
                    $resolvedWithinSla++;
                }
            }

            // Category
            $categories[$cat] ??= ['name' => $cat, 'total' => 0, 'open' => 0, 'resolved' => 0, 'res_secs' => []];
            $categories[$cat]['total']++;
            if ($s === 3) {
                $categories[$cat]['resolved']++;
                if ($resSec !== null) {
                    $categories[$cat]['res_secs'][] = $resSec;
                }
            } else {
                $categories[$cat]['open']++;
            }

            // Staff (only tickets a staff member has touched)
            $emp = trim((string) ($r['employee_code'] ?? ''));
            if ($emp !== '' && $s >= 1) {
                $staff[$emp] ??= ['code' => $emp, 'name' => $r['employee_name'] ?: 'Staff '.$emp, 'handled' => 0, 'resolved' => 0, 'open' => 0, 'res_secs' => []];
                $staff[$emp]['handled']++;
                if ($s === 3) {
                    $staff[$emp]['resolved']++;
                    if ($resSec !== null) {
                        $staff[$emp]['res_secs'][] = $resSec;
                    }
                } else {
                    $staff[$emp]['open']++;
                }
            }

            // Students
            $reg = trim((string) ($r['regno'] ?? ''));
            if ($reg !== '') {
                $students[$reg] = ($students[$reg] ?? 0) + 1;
            }

            // Open ticket ageing
            if ($s !== 3 && $raised) {
                $age = max(0, $now - $raised);
                $openAges[] = $age;
                $aging[self::agingBucket($age)]++;
                if ($age > $slaRes) {
                    $openBreachResolve++;
                }
                if ($s === 0) {
                    $oldestUnackAge = max($oldestUnackAge, $age);
                    if ($age > $slaResp) {
                        $unackBreach++;
                    }
                }
                $openList[] = [
                    'tkt_id' => $r['tkt_id'],
                    'regno' => $reg,
                    'status' => $s,
                    'status_label' => self::STATUS_LABELS[$s],
                    'category' => $cat,
                    'age_sec' => $age,
                    'age' => self::fmt($age),
                    'breached' => $age > $slaRes,
                ];
            }
        }

        $total = array_sum($counts);
        $open = $total - $counts[3];
        $acknowledged = $total - $counts[0];

        // Categories summary
        $catOut = array_values(array_map(function ($c) {
            $avg = self::avg($c['res_secs']);
            unset($c['res_secs']);
            $c['avg_res_sec'] = $avg;
            $c['avg_res'] = self::fmt($avg);

            return $c;
        }, $categories));
        usort($catOut, fn ($a, $b) => $b['total'] <=> $a['total']);

        // Staff summary
        $staffOut = array_values(array_map(function ($s) {
            $avg = self::avg($s['res_secs']);
            unset($s['res_secs']);
            $s['avg_res_sec'] = $avg;
            $s['avg_res'] = self::fmt($avg);
            $s['rate'] = $s['handled'] > 0 ? (int) round($s['resolved'] / $s['handled'] * 100) : 0;

            return $s;
        }, $staff));
        usort($staffOut, fn ($a, $b) => $b['handled'] <=> $a['handled']);

        // Repeat students
        arsort($students);
        $repeat = [];
        foreach ($students as $reg => $n) {
            if ($n < 2) {
                break;
            }
            $repeat[] = ['regno' => (string) $reg, 'count' => $n];
        }

        usort($openList, fn ($a, $b) => $b['age_sec'] <=> $a['age_sec']);

        // Timings
        $timing = [
            'first_response' => self::stat($firstResponse),
            'pending_to_process' => self::stat($pendingToProc),
            'process_to_resolve' => self::stat($procToResolve),
            'resolution' => self::stat($resolution),
            'open_age' => self::stat($openAges),
        ];

        $rates = [
            'resolution' => self::pct($counts[3], $total),
            'acknowledged' => self::pct($acknowledged, $total),
            'sla_resolve' => count($resolution) ? self::pct($resolvedWithinSla, count($resolution)) : null,
            'open_within_sla' => $open ? self::pct($open - $openBreachResolve, $open) : 100,
        ];

        // 14-day trend + week-over-week (independent of the period filter)
        $trend = self::trend($allRows, $now, 14);
        $wow = self::weekOverWeek($allRows, $now);

        $health = self::health($total, $rates);

        $kpi = [
            'generated_at' => $now,
            'has_stage_timestamps' => $hasStageTimestamps,
            'sla' => ['response_hours' => self::SLA_RESPONSE_HOURS, 'resolve_hours' => self::SLA_RESOLVE_HOURS],
            'counts' => [
                'total' => $total, 'raised' => $counts[0], 'pending' => $counts[1],
                'in_process' => $counts[2], 'resolved' => $counts[3], 'open' => $open,
                'acknowledged' => $acknowledged,
            ],
            'funnel' => [
                'Raised' => $total,
                'Opened by staff' => $acknowledged,
                'Reached In Process' => $reachedProcess,
                'Resolved' => $counts[3],
            ],
            'rates' => $rates,
            'timing' => $timing,
            'breaches' => [
                'open_over_resolve_sla' => $openBreachResolve,
                'unopened_over_response_sla' => $unackBreach,
                'oldest_unopened_sec' => $oldestUnackAge,
                'oldest_unopened' => self::fmt($oldestUnackAge),
            ],
            'aging' => $aging,
            'hourly' => $hourly,
            'weekday' => $weekday,
            'categories' => array_slice($catOut, 0, 8),
            'staff' => array_slice($staffOut, 0, 8),
            'repeat_students' => array_slice($repeat, 0, 5),
            'oldest_open' => array_slice($openList, 0, 5),
            'trend' => $trend,
            'wow' => $wow,
            'health' => $health,
        ];

        $kpi['insights'] = self::insights($kpi);
        $kpi['headline'] = self::headline($kpi);

        return $kpi;
    }

    /* ======================================================================
     *  AI PERCEPTIONS — rule-based reading of the numbers
     * ==================================================================== */
    protected static function insights(array $k): array
    {
        $out = [];
        $c = $k['counts'];
        $t = $k['timing'];
        $r = $k['rates'];
        $b = $k['breaches'];
        $total = $c['total'];

        if ($total === 0) {
            return [[
                'level' => 'info', 'icon' => 'bi-inbox', 'title' => 'No tickets in this period',
                'text' => 'There is nothing to analyse for the selected period.',
                'action' => 'Choose a wider period to see trends.',
            ]];
        }

        // 1. Unopened tickets
        if ($c['raised'] > 0) {
            $share = self::pct($c['raised'], $total);
            $level = ($share >= 50 || $b['unopened_over_response_sla'] > 0) ? 'critical' : 'warning';
            $out[] = [
                'level' => $level, 'icon' => 'bi-envelope-exclamation',
                'title' => "{$c['raised']} tickets not yet opened by staff",
                'text' => "{$share}% of tickets are still in Raised status. The oldest has been waiting {$b['oldest_unopened']}"
                    .($b['unopened_over_response_sla'] > 0 ? ", and {$b['unopened_over_response_sla']} are past the ".self::SLA_RESPONSE_HOURS.'-hour response target.' : '.'),
                'action' => 'Assign a daily triage owner to open every new ticket within '.self::SLA_RESPONSE_HOURS.' hours.',
            ];
        } else {
            $out[] = [
                'level' => 'good', 'icon' => 'bi-envelope-check',
                'title' => 'Every ticket has been opened by staff',
                'text' => 'No ticket is waiting in Raised status.',
                'action' => 'Keep the current triage routine.',
            ];
        }

        // 2. Resolution rate
        $rr = $r['resolution'];
        if ($rr < 40) {
            $out[] = ['level' => 'critical', 'icon' => 'bi-graph-down-arrow',
                'title' => "Only {$rr}% of tickets are resolved",
                'text' => "{$c['resolved']} of {$total} tickets closed, {$c['open']} still open.",
                'action' => 'Set a weekly closure target and review open tickets in the staff meeting.'];
        } elseif ($rr < 70) {
            $out[] = ['level' => 'warning', 'icon' => 'bi-graph-up',
                'title' => "Resolution rate is {$rr}%",
                'text' => "{$c['open']} tickets remain open. A healthy desk usually closes 70% or more.",
                'action' => 'Focus on the oldest open tickets first.'];
        } else {
            $out[] = ['level' => 'good', 'icon' => 'bi-trophy',
                'title' => "Strong resolution rate: {$rr}%",
                'text' => "{$c['resolved']} of {$total} tickets are resolved.",
                'action' => 'Maintain current staffing levels.'];
        }

        // 3. First response time
        $fr = $t['first_response'];
        if ($fr['n'] > 0) {
            $over = $fr['avg'] > self::SLA_RESPONSE_HOURS * 3600;
            $out[] = [
                'level' => $over ? 'warning' : 'good', 'icon' => 'bi-reply',
                'title' => "Staff take {$fr['avg_fmt']} on average to open a ticket",
                'text' => "Measured on {$fr['n']} ticket".($fr['n'] > 1 ? 's' : '')." (median {$fr['median_fmt']}). Target is ".self::SLA_RESPONSE_HOURS.' hours.',
                'action' => $over ? 'Enable a notification or dashboard alert for new tickets.' : 'Response speed is within target.',
            ];
        }

        // 4. Resolution time + long tail
        $res = $t['resolution'];
        if ($res['n'] > 0) {
            $over = $res['avg'] > self::SLA_RESOLVE_HOURS * 3600;
            $text = "Average {$res['avg_fmt']}, median {$res['median_fmt']}, fastest {$res['min_fmt']}, slowest {$res['max_fmt']}.";
            if ($res['n'] >= 5 && $res['median'] > 0 && $res['p90'] > 3 * $res['median']) {
                $text .= " The slowest 10% take over {$res['p90_fmt']}, more than 3× the median, so a few hard cases are dragging the average up.";
            }
            $out[] = [
                'level' => $over ? 'warning' : 'good', 'icon' => 'bi-stopwatch',
                'title' => "Tickets are resolved in {$res['avg_fmt']} on average",
                'text' => $text,
                'action' => $over ? 'Review the slowest tickets for missing information or hand-offs.' : 'Resolution speed is within the '.self::SLA_RESOLVE_HOURS.'-hour target.',
            ];
        }

        // 5. SLA breaches on open tickets
        if ($b['open_over_resolve_sla'] > 0) {
            $out[] = ['level' => 'critical', 'icon' => 'bi-alarm',
                'title' => "{$b['open_over_resolve_sla']} open tickets are older than ".self::SLA_RESOLVE_HOURS.' hours',
                'text' => 'These students have waited longer than the resolution target and are the most likely to escalate.',
                'action' => 'Escalate these tickets to a senior coordinator today.'];
        }

        // 6. Bottleneck stage
        $p2p = $t['pending_to_process'];
        $p2r = $t['process_to_resolve'];
        $stages = array_filter([
            'Waiting to be opened' => $fr['n'] ? $fr['avg'] : null,
            'Pending → In Process' => $p2p['n'] ? $p2p['avg'] : null,
            'In Process → Resolved' => $p2r['n'] ? $p2r['avg'] : null,
        ], fn ($v) => $v !== null);
        if (count($stages) >= 2) {
            arsort($stages);
            $stage = array_key_first($stages);
            $sum = array_sum($stages);
            $share = $sum > 0 ? self::pct($stages[$stage], $sum) : 0;
            $out[] = ['level' => $share >= 60 ? 'warning' : 'info', 'icon' => 'bi-funnel',
                'title' => "Biggest delay: {$stage}",
                'text' => 'This stage takes '.self::fmt($stages[$stage])." on average, {$share}% of the measured ticket lifecycle.",
                'action' => 'Look at what blocks tickets at this step (approvals, missing documents, unclear ownership).'];
        }

        // 7. Inflow vs outflow (last 7 days)
        $w = $k['wow'];
        if ($w['raised_this'] + $w['resolved_this'] > 0) {
            $net = $w['raised_this'] - $w['resolved_this'];
            $out[] = [
                'level' => $net > 0 ? 'warning' : 'good', 'icon' => $net > 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right',
                'title' => $net > 0 ? "Backlog grew by {$net} this week" : ($net < 0 ? 'Backlog shrank by '.abs($net).' this week' : 'Backlog is steady this week'),
                'text' => "Last 7 days: {$w['raised_this']} raised vs {$w['resolved_this']} resolved.",
                'action' => $net > 0 ? 'Closures need to exceed new tickets to bring the backlog down.' : 'The team is keeping pace with demand.',
            ];
        }

        // 8. Volume change week-over-week
        if ($w['raised_prev'] >= 3 && $w['raised_change'] !== null && abs($w['raised_change']) >= 30) {
            $up = $w['raised_change'] > 0;
            $out[] = ['level' => $up ? 'warning' : 'info', 'icon' => $up ? 'bi-activity' : 'bi-arrow-down',
                'title' => 'New tickets '.($up ? 'up' : 'down').' '.abs($w['raised_change']).'% vs last week',
                'text' => "{$w['raised_this']} this week compared with {$w['raised_prev']} the week before.",
                'action' => $up ? 'Check whether a recent event (results, fee deadline) is driving the spike.' : 'Volume is easing.'];
        }

        // 9. Category concentration & slowest category
        $cats = $k['categories'];
        if (! empty($cats)) {
            $top = $cats[0];
            $share = self::pct($top['total'], $total);
            if ($share >= 40 && $total >= 3) {
                $out[] = ['level' => 'info', 'icon' => 'bi-tags',
                    'title' => "{$top['name']} drives {$share}% of tickets",
                    'text' => "{$top['total']} of {$total} tickets fall in this category.",
                    'action' => 'Publish an FAQ or notice for this topic to prevent repeat questions.'];
            }
            $withAvg = array_filter($cats, fn ($x) => $x['avg_res_sec'] !== null);
            if (count($withAvg) >= 2) {
                usort($withAvg, fn ($a, $b) => $b['avg_res_sec'] <=> $a['avg_res_sec']);
                $slow = $withAvg[0];
                $out[] = ['level' => 'info', 'icon' => 'bi-hourglass-split',
                    'title' => "Slowest category: {$slow['name']}",
                    'text' => "Takes {$slow['avg_res']} on average to resolve.",
                    'action' => 'Prepare a standard answer or checklist for this category.'];
            }
        }

        // 10. Staff load
        $staff = $k['staff'];
        $handled = array_sum(array_column($staff, 'handled'));
        if (count($staff) === 1 && $handled >= 3) {
            $out[] = ['level' => 'warning', 'icon' => 'bi-person-exclamation',
                'title' => 'One staff member handles all tickets',
                'text' => "{$staff[0]['name']} ({$staff[0]['code']}) has handled all {$handled} tickets that were picked up.",
                'action' => 'Add a backup handler so tickets keep moving during leave or busy periods.'];
        } elseif (count($staff) > 1) {
            $topShare = self::pct($staff[0]['handled'], $handled);
            if ($topShare >= 60) {
                $out[] = ['level' => 'warning', 'icon' => 'bi-person-exclamation',
                    'title' => "{$staff[0]['name']} carries {$topShare}% of the workload",
                    'text' => "{$staff[0]['handled']} of {$handled} handled tickets.",
                    'action' => 'Spread new tickets more evenly across the team.'];
            }
            $fast = array_filter($staff, fn ($s) => $s['avg_res_sec'] !== null && $s['resolved'] >= 2);
            if (count($fast) >= 2) {
                usort($fast, fn ($a, $b) => $a['avg_res_sec'] <=> $b['avg_res_sec']);
                $out[] = ['level' => 'good', 'icon' => 'bi-lightning',
                    'title' => "Fastest resolver: {$fast[0]['name']}",
                    'text' => "Average {$fast[0]['avg_res']} across {$fast[0]['resolved']} resolved tickets.",
                    'action' => 'Share their approach with the rest of the team.'];
            }
        }

        // 11. Peak hour
        $hourly = $k['hourly'];
        $peak = array_keys($hourly, max($hourly))[0];
        if ($hourly[$peak] >= 2 && $hourly[$peak] >= 0.25 * $total) {
            $out[] = ['level' => 'info', 'icon' => 'bi-clock',
                'title' => 'Peak arrival time: '.self::hourLabel($peak).'–'.self::hourLabel(($peak + 1) % 24),
                'text' => "{$hourly[$peak]} tickets (".self::pct($hourly[$peak], $total).'%) arrive in this hour.',
                'action' => 'Make sure someone is watching the queue at this time.'];
        }

        // 12. Repeat students
        $heavy = array_filter($k['repeat_students'], fn ($s) => $s['count'] >= 3);
        if (! empty($heavy)) {
            $first = reset($heavy);
            $out[] = ['level' => 'info', 'icon' => 'bi-arrow-repeat',
                'title' => count($heavy).' student'.(count($heavy) > 1 ? 's have' : ' has').' raised 3 or more tickets',
                'text' => "Highest: {$first['regno']} with {$first['count']} tickets. Repeat tickets often mean the first answer did not solve the problem.",
                'action' => 'Call these students directly to close the loop.'];
        }

        // 13. Data quality
        if ($k['counts']['resolved'] > 0 && $k['counts']['resolved'] < 5) {
            $out[] = ['level' => 'info', 'icon' => 'bi-info-circle',
                'title' => 'Small sample',
                'text' => "Timing figures are based on only {$k['counts']['resolved']} resolved ticket".($k['counts']['resolved'] > 1 ? 's' : '').' and will become more reliable as volume grows.',
                'action' => 'Read averages as early indicators, not final numbers.'];
        }
        if (! $k['has_stage_timestamps']) {
            $out[] = ['level' => 'info', 'icon' => 'bi-database-exclamation',
                'title' => 'Stage timings are estimated',
                'text' => 'Only the latest staff action time is stored per ticket, so time-to-open and stage durations use partial data.',
                'action' => 'Run the stage-timestamp migration to track every stage precisely.'];
        }

        $order = ['critical' => 0, 'warning' => 1, 'info' => 2, 'good' => 3];
        usort($out, fn ($a, $b) => $order[$a['level']] <=> $order[$b['level']]);

        return $out;
    }

    /** One-line executive summary for the top of the page. */
    protected static function headline(array $k): string
    {
        $c = $k['counts'];
        if ($c['total'] === 0) {
            return 'No tickets in the selected period.';
        }
        $parts = [];
        $parts[] = "{$c['total']} tickets, {$k['rates']['resolution']}% resolved, {$c['open']} open";
        if ($k['timing']['resolution']['n'] > 0) {
            $parts[] = 'average resolution '.$k['timing']['resolution']['avg_fmt'];
        }
        $line = ucfirst(implode(', ', $parts)).'services';

        $top = null;
        foreach ($k['insights'] as $i) {
            if (in_array($i['level'], ['critical', 'warning'], true)) {
                $top = $i;
                break;
            }
        }
        $line .= $top ? ' Priority: '.rtrim($top['action'], '.').'.' : ' No urgent issues.';

        return $line;
    }

    /** 0–100 composite score. */
    protected static function health(int $total, array $rates): array
    {
        if ($total === 0) {
            return ['score' => null, 'label' => 'No data', 'level' => 'info', 'parts' => []];
        }
        $parts = [
            ['label' => 'Tickets resolved', 'weight' => 30, 'value' => $rates['resolution']],
            ['label' => 'Tickets opened by staff', 'weight' => 20, 'value' => $rates['acknowledged']],
            ['label' => 'Resolved within '.self::SLA_RESOLVE_HOURS.'h', 'weight' => 25, 'value' => $rates['sla_resolve'] ?? 0],
            ['label' => 'Open tickets within target', 'weight' => 25, 'value' => $rates['open_within_sla']],
        ];
        $score = (int) round(array_sum(array_map(fn ($p) => $p['value'] * $p['weight'] / 100, $parts)));

        [$label, $level] = match (true) {
            $score >= 80 => ['Healthy', 'good'],
            $score >= 60 => ['Stable', 'info'],
            $score >= 40 => ['Needs attention', 'warning'],
            default => ['Critical', 'critical'],
        };

        return ['score' => $score, 'label' => $label, 'level' => $level, 'parts' => $parts];
    }

    protected static function trend(array $rows, int $now, int $days): array
    {
        $start = strtotime(date('Y-m-d', $now)) - ($days - 1) * 86400;
        $labels = [];
        $raised = [];
        $resolved = [];
        $index = [];
        for ($i = 0; $i < $days; $i++) {
            $ts = $start + $i * 86400;
            $key = date('Y-m-d', $ts);
            $index[$key] = $i;
            $labels[] = date('d M', $ts);
            $raised[] = 0;
            $resolved[] = 0;
        }
        foreach ($rows as $r) {
            if ($r['raised_at'] && isset($index[$k = date('Y-m-d', $r['raised_at'])])) {
                $raised[$index[$k]]++;
            }
            if ((int) $r['status'] === 3 && ! empty($r['resolved_at']) && isset($index[$k = date('Y-m-d', $r['resolved_at'])])) {
                $resolved[$index[$k]]++;
            }
        }

        return ['labels' => $labels, 'raised' => $raised, 'resolved' => $resolved];
    }

    protected static function weekOverWeek(array $rows, int $now): array
    {
        $w1 = $now - 7 * 86400;
        $w2 = $now - 14 * 86400;
        $o = ['raised_this' => 0, 'raised_prev' => 0, 'resolved_this' => 0, 'resolved_prev' => 0];
        foreach ($rows as $r) {
            $ra = $r['raised_at'];
            if ($ra && $ra > $w1) {
                $o['raised_this']++;
            } elseif ($ra && $ra > $w2) {
                $o['raised_prev']++;
            }
            $rs = (int) $r['status'] === 3 ? ($r['resolved_at'] ?? null) : null;
            if ($rs && $rs > $w1) {
                $o['resolved_this']++;
            } elseif ($rs && $rs > $w2) {
                $o['resolved_prev']++;
            }
        }
        $o['raised_change'] = $o['raised_prev'] > 0 ? (int) round(($o['raised_this'] - $o['raised_prev']) / $o['raised_prev'] * 100) : null;
        $o['resolved_change'] = $o['resolved_prev'] > 0 ? (int) round(($o['resolved_this'] - $o['resolved_prev']) / $o['resolved_prev'] * 100) : null;

        return $o;
    }

    /* ---------------------------- helpers ---------------------------- */

    protected static function diff(?int $from, ?int $to): ?int
    {
        if (! $from || ! $to || $to < $from) {
            return null;
        }

        return $to - $from;
    }

    protected static function stat(array $v): array
    {
        $n = count($v);
        if ($n === 0) {
            return ['n' => 0, 'avg' => null, 'median' => null, 'p90' => null, 'min' => null, 'max' => null,
                'avg_fmt' => '—', 'median_fmt' => '—', 'p90_fmt' => '—', 'min_fmt' => '—', 'max_fmt' => '—'];
        }
        sort($v);
        $avg = (int) round(array_sum($v) / $n);
        $median = self::percentile($v, 50);
        $p90 = self::percentile($v, 90);

        return [
            'n' => $n, 'avg' => $avg, 'median' => $median, 'p90' => $p90, 'min' => $v[0], 'max' => $v[$n - 1],
            'avg_fmt' => self::fmt($avg), 'median_fmt' => self::fmt($median), 'p90_fmt' => self::fmt($p90),
            'min_fmt' => self::fmt($v[0]), 'max_fmt' => self::fmt($v[$n - 1]),
        ];
    }

    protected static function percentile(array $sorted, float $p): int
    {
        $n = count($sorted);
        if ($n === 1) {
            return $sorted[0];
        }
        $pos = ($p / 100) * ($n - 1);
        $lo = (int) floor($pos);
        $hi = (int) ceil($pos);

        return (int) round($sorted[$lo] + ($sorted[$hi] - $sorted[$lo]) * ($pos - $lo));
    }

    protected static function avg(array $v): ?int
    {
        return count($v) ? (int) round(array_sum($v) / count($v)) : null;
    }

    protected static function pct(int|float $part, int|float $whole): int
    {
        return $whole > 0 ? (int) round($part / $whole * 100) : 0;
    }

    protected static function agingBucket(int $sec): string
    {
        return match (true) {
            $sec < 4 * 3600 => '< 4h',
            $sec < 86400 => '4–24h',
            $sec < 3 * 86400 => '1–3 days',
            $sec < 7 * 86400 => '3–7 days',
            default => '> 7 days',
        };
    }

    protected static function hourLabel(int $h): string
    {
        return date('g A', mktime($h, 0, 0, 1, 1, 2000));
    }

    /** Human duration: 45s, 12m, 3h 20m, 2d 4h */
    public static function fmt(?int $sec): string
    {
        if ($sec === null) {
            return '—';
        }
        if ($sec < 60) {
            return $sec.'s';
        }
        if ($sec < 3600) {
            return floor($sec / 60).'m';
        }
        if ($sec < 86400) {
            $h = floor($sec / 3600);
            $m = floor(($sec % 3600) / 60);

            return $m ? "{$h}h {$m}m" : "{$h}h";
        }
        $d = floor($sec / 86400);
        $h = floor(($sec % 86400) / 3600);

        return $h ? "{$d}d {$h}h" : "{$d}d";
    }
}
