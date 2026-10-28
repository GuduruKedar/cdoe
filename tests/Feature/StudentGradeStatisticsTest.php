<?php

namespace Tests\Feature;

use Modules\Auth\Models\Login;
use Modules\Examinations\Models\MarksMemo;
use Tests\TestCase;

class StudentGradeStatisticsTest extends TestCase
{
    /**
     * Test empty structure returned when student has no examination records.
     */
    public function test_get_grade_stats_returns_empty_structure_when_student_has_no_records(): void
    {
        $stats = MarksMemo::getGradeStatsForStudent('NON_EXISTENT_STUDENT_99999');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('grades', $stats);
        $this->assertArrayHasKey('summary', $stats);
        $this->assertArrayHasKey('semesters_list', $stats);

        $this->assertSame(0, $stats['summary']['total_subjects']);
        $this->assertSame(0, $stats['summary']['total_passed']);
        $this->assertSame(0, $stats['summary']['total_backlogs']);
        $this->assertSame(0, $stats['summary']['total_cleared']);

        foreach (['O', 'S', 'A', 'B', 'C', 'D', 'F'] as $grade) {
            $this->assertArrayHasKey($grade, $stats['grades']);
            $this->assertSame(0, $stats['grades'][$grade]['effective_count']);
            $this->assertSame(0, $stats['grades'][$grade]['total_attempts']);
            $this->assertSame(0, $stats['grades'][$grade]['active_backlogs']);
        }
    }

    /**
     * Test calculation for student with both regular and reappear attempts.
     */
    public function test_get_grade_stats_calculates_regular_and_reappear_breakdown(): void
    {
        // 261CK01066 has both regular and reappear records in DB
        $stats = MarksMemo::getGradeStatsForStudent('261CK01066');

        $this->assertGreaterThan(0, $stats['summary']['total_subjects']);
        $this->assertSame(5, $stats['summary']['total_subjects']);
        $this->assertSame(0, $stats['summary']['total_backlogs']);

        // Grade S should have 1 effective passed subject and multiple attempts across semesters
        $this->assertGreaterThanOrEqual(1, $stats['grades']['S']['effective_count']);
        $this->assertNotEmpty($stats['grades']['S']['semesters']);

        // Verify each semester in breakdown contains regular and reappear keys
        foreach ($stats['grades'] as $g => $gradeData) {
            foreach ($gradeData['semesters'] as $sem => $semData) {
                $this->assertArrayHasKey('regular', $semData);
                $this->assertArrayHasKey('reappear', $semData);
                $this->assertArrayHasKey('total', $semData);
                $this->assertSame($semData['regular'] + $semData['reappear'], $semData['total']);
            }
        }
    }

    /**
     * Test mmview route renders grade cards and breakdown.
     */
    public function test_mmview_route_renders_grade_cards(): void
    {
        // Authenticate as student
        $studentUser = Login::where('usercode', '261CD01121')->first();

        if (! $studentUser) {
            $this->markTestSkipped('Student 261CD01121 not found in test database.');
        }

        $response = $this->withSession([
            'user' => [
                'usercode' => $studentUser->usercode,
                'role_id' => $studentUser->role_id,
                'user_type' => $studentUser->user_type,
            ],
        ])->get(route('examinations.mmview'));

        $response->assertStatus(200);
        $response->assertSee('Subject Grade Distribution');
        $response->assertSee('Marks Memo History');
        $response->assertSee('Active Backlog');
    }

    /**
     * Test mmview route with custom regno query parameter.
     */
    public function test_mmview_route_supports_query_regno_preview(): void
    {
        $studentUser = Login::where('usercode', '261CD01121')->first();

        if (! $studentUser) {
            $this->markTestSkipped('Student 261CD01121 not found in test database.');
        }

        $response = $this->withSession([
            'user' => [
                'usercode' => $studentUser->usercode,
                'role_id' => $studentUser->role_id,
                'user_type' => $studentUser->user_type,
            ],
        ])->get(route('examinations.mmview', ['regno' => '261CK01066']));

        $response->assertStatus(200);
        $response->assertSee('Previewing examination marks for Student:');
        $response->assertSee('261CK01066');
    }
}
