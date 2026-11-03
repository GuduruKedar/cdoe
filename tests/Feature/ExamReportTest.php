<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExamReportTest extends TestCase
{
    protected function getAdminSession(): array
    {
        return [
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ];
    }

    public function test_exam_report_requires_login(): void
    {
        $response = $this->get('/examinations/examreport');
        $response->assertRedirect('/login');
    }

    public function test_exam_report_loads_with_fail_report_as_default_tab(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport');

        if ($response->status() === 302) {
            $this->fail('Redirected to: '.$response->headers->get('Location'));
        }

        $response->assertStatus(200);
        $response->assertSee('Exam Report');
        $response->assertSee('Fail Report');
        $response->assertSee('Toppers');
        $response->assertSee('CGPA Report');
        $response->assertSee('200 rows');
    }

    public function test_exam_report_toppers_tab_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers');

        $response->assertStatus(200);
        $response->assertSee('Toppers');
        $response->assertSee('SGPA');
        $response->assertSee('CGPA');
    }

    public function test_exam_report_cgpa_tab_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=cgpa');

        $response->assertStatus(200);
        $response->assertSee('CGPA Report');
        $response->assertSee('Backlogs');
    }

    public function test_exam_report_csv_export_streams_csv(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport/export-csv?tab=fail');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('exam_report_fail', (string) $response->headers->get('Content-Disposition'));
    }
}
