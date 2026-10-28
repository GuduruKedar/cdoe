<?php

namespace Modules\Examinations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\Subject;
use Modules\Admissions\Models\StudentRegistration;

class MarksMemo extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'examinations.marks_cgpa';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'appno' => 'integer',
        's_year' => 'integer',
        's_sem' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function scopeByRegno($query, string $regno)
    {
        return $query->where('regno', $regno);
    }

    public function scopeByMmno($query, string $mmno)
    {
        return $query->where('mmno', $mmno);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subcode', 'subcode');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'regno', 'register_no');
    }

    public function studentByAppno(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'appno', 'application_no');
    }

    /**
     * Calculate comprehensive grade statistics for a student across all semesters.
     * Evaluates effective status (e.g. subjects failed in regular but cleared in reappear)
     * and compiles semester-wise attempt breakdowns.
     *
     * @return array{
     *     grades: array<string, array{
     *         grade: string,
     *         name: string,
     *         color: string,
     *         points: int,
     *         effective_count: int,
     *         total_attempts: int,
     *         cleared_in_reappear: int,
     *         active_backlogs: int,
     *         semesters: array<string, array{regular: int, reappear: int, total: int, cleared: int, active: int}>
     *     }>,
     *     summary: array{total_subjects: int, total_passed: int, total_backlogs: int, total_cleared: int},
     *     semesters_list: list<string>
     * }
     */
    public static function getGradeStatsForStudent(?string $regno): array
    {
        $gradeDefinitions = [
            'O' => ['name' => 'Outstanding', 'color' => '#8b5cf6', 'points' => 10],
            'S' => ['name' => 'Superior', 'color' => '#3b82f6', 'points' => 9],
            'A' => ['name' => 'Very Good', 'color' => '#0ea5e9', 'points' => 8],
            'B' => ['name' => 'Good', 'color' => '#10b981', 'points' => 7],
            'C' => ['name' => 'Above Average', 'color' => '#f59e0b', 'points' => 6],
            'D' => ['name' => 'Pass', 'color' => '#f97316', 'points' => 5],
            'F' => ['name' => 'Fail / Backlog', 'color' => '#ef4444', 'points' => 0],
        ];

        $passGrades = ['O', 'S', 'A', 'B', 'C', 'D'];

        $stats = [];
        foreach ($gradeDefinitions as $g => $meta) {
            $stats[$g] = [
                'grade' => $g,
                'name' => $meta['name'],
                'color' => $meta['color'],
                'points' => $meta['points'],
                'effective_count' => 0,
                'total_attempts' => 0,
                'cleared_in_reappear' => 0,
                'active_backlogs' => 0,
                'semesters' => [],
            ];
        }

        if (empty($regno)) {
            return [
                'grades' => $stats,
                'summary' => [
                    'total_subjects' => 0,
                    'total_passed' => 0,
                    'total_backlogs' => 0,
                    'total_cleared' => 0,
                ],
                'semesters_list' => [],
            ];
        }

        $records = static::where('regno', $regno)
            ->select([
                'subcode',
                's_year',
                's_sem',
                'mm_type',
                'letter_grade',
                'total_conducted',
                'total_earned',
                'grade_points',
                'exam_mon_yyyy',
                'log_timestamp',
                'mmno',
            ])
            ->orderBy('s_year')
            ->orderBy('s_sem')
            ->orderBy('log_timestamp')
            ->get();

        if ($records->isEmpty()) {
            return [
                'grades' => $stats,
                'summary' => [
                    'total_subjects' => 0,
                    'total_passed' => 0,
                    'total_backlogs' => 0,
                    'total_cleared' => 0,
                ],
                'semesters_list' => [],
            ];
        }

        $subjectRecords = [];
        $allSemesters = [];

        foreach ($records as $r) {
            $sub = trim((string) $r->subcode);
            if ($sub === '') {
                continue;
            }

            $semKey = "{$r->s_year}-{$r->s_sem}";
            $allSemesters[$semKey] = true;

            $grade = strtoupper(trim((string) $r->letter_grade));
            $rawType = strtoupper(trim((string) $r->mm_type));
            $isReappear = (bool) preg_match('/reap|suppl|better|improv/i', $rawType);

            $subjectRecords[$sub][] = [
                'subcode' => $sub,
                'sem' => $semKey,
                'year' => (int) $r->s_year,
                'sem_num' => (int) $r->s_sem,
                'grade' => $grade,
                'raw_type' => $rawType,
                'is_reappear' => $isReappear,
                'exam_mon' => $r->exam_mon_yyyy,
                'timestamp' => $r->log_timestamp,
            ];
        }

        $subjectOutcomes = [];

        foreach ($subjectRecords as $sub => $attempts) {
            $hasFailedAttempt = false;
            $passedAttempt = null;

            foreach ($attempts as $att) {
                if ($att['grade'] === 'F') {
                    $hasFailedAttempt = true;
                }
                if (in_array($att['grade'], $passGrades, true)) {
                    $passedAttempt = $att;
                }
            }

            if ($passedAttempt !== null) {
                $effectiveGrade = $passedAttempt['grade'];
                $status = $hasFailedAttempt ? 'cleared_in_reappear' : 'passed_regular';
            } else {
                $effectiveGrade = 'F';
                $status = 'active_backlog';
            }

            $originSem = $attempts[0]['sem'];

            $subjectOutcomes[$sub] = [
                'subcode' => $sub,
                'effective_grade' => $effectiveGrade,
                'status' => $status,
                'origin_sem' => $originSem,
                'passed_attempt' => $passedAttempt,
                'has_failed_attempt' => $hasFailedAttempt,
                'attempts' => $attempts,
            ];
        }

        $totalPassed = 0;
        $totalBacklogs = 0;
        $totalCleared = 0;

        foreach ($subjectOutcomes as $sub => $outcome) {
            $eg = $outcome['effective_grade'];
            if (isset($stats[$eg])) {
                $stats[$eg]['effective_count']++;
            }

            if ($outcome['status'] === 'active_backlog') {
                $totalBacklogs++;
                $stats['F']['active_backlogs']++;
            } elseif ($outcome['status'] === 'cleared_in_reappear') {
                $totalCleared++;
                $totalPassed++;
                $stats['F']['cleared_in_reappear']++;
            } else {
                $totalPassed++;
            }
        }

        foreach ($subjectRecords as $sub => $attempts) {
            $outcome = $subjectOutcomes[$sub];

            foreach ($attempts as $att) {
                $g = $att['grade'];
                if (! isset($stats[$g])) {
                    continue;
                }

                $stats[$g]['total_attempts']++;

                $sem = $outcome['origin_sem'] ?? $att['sem'];
                $typeKey = $att['is_reappear'] ? 'reappear' : 'regular';

                if (! isset($stats[$g]['semesters'][$sem])) {
                    $stats[$g]['semesters'][$sem] = [
                        'regular' => 0,
                        'reappear' => 0,
                        'total' => 0,
                        'cleared' => 0,
                        'active' => 0,
                    ];
                }

                $stats[$g]['semesters'][$sem][$typeKey]++;
                $stats[$g]['semesters'][$sem]['total']++;

                if ($g === 'F') {
                    if ($outcome['status'] === 'cleared_in_reappear') {
                        $stats[$g]['semesters'][$sem]['cleared']++;
                    } else {
                        $stats[$g]['semesters'][$sem]['active']++;
                    }
                }
            }
        }

        foreach ($stats as $g => &$data) {
            uksort($data['semesters'], 'strnatcmp');
        }
        unset($data);

        $sortedSemesters = array_keys($allSemesters);
        sort($sortedSemesters, SORT_NATURAL);

        return [
            'grades' => $stats,
            'summary' => [
                'total_subjects' => count($subjectOutcomes),
                'total_passed' => $totalPassed,
                'total_backlogs' => $totalBacklogs,
                'total_cleared' => $totalCleared,
            ],
            'semesters_list' => $sortedSemesters,
        ];
    }
}
