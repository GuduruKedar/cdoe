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
        $response->assertDontSee('Examination results tabulation, backlog fail reports');
        $response->assertSee('Fail Report');
        $response->assertSee('Toppers');
        $response->assertSee('CGPA Report');
        $response->assertSee('Program (Filter 1)');
        $response->assertSee('Batch (Filter 2)');
        $response->assertSee('All Batches');
        $response->assertSee('Search');
        $response->assertDontSee('Semester (Filter 3)');
        $response->assertDontSee('All Semesters');
        $response->assertDontSee('All Exam Months');
        $response->assertSee('200 rows');
    }

    public function test_exam_report_toppers_tab_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers');

        $response->assertStatus(200);
        $response->assertSee('Toppers');
        $response->assertSee('Total Earned Marks');
        $response->assertSee('Internal (FA)');
        $response->assertSee('External (SA)');
        $response->assertSee('Program (Filter 1)');
        $response->assertSee('Batch (Filter 2)');
        $response->assertSee('All Batches');
        $response->assertSee('Search');
        $response->assertDontSee('Semester (Filter 3)');
        $response->assertDontSee('All Semesters');
        $response->assertDontSee('All Exam Months');
        $response->assertSee('CSV');
        $response->assertSee('PDF');
    }

    public function test_exam_report_toppers_page_2_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers&page=2');

        $response->assertStatus(200);
        $response->assertSee('Total Earned Marks');
        $response->assertSee('Program (Filter 1)');
        $response->assertSee('Batch (Filter 2)');
    }

    public function test_exam_report_toppers_excludes_reappear(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers');

        $response->assertStatus(200);
        // Student 261BHANU99 had reappear records and must not be in toppers
        $response->assertDontSee('261BHANU99');
        // Regular top performer must appear
        $response->assertSee('251DC01087');
    }

    public function test_exam_report_toppers_csv_export(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport/export-csv?tab=toppers');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('exam_report_toppers', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_exam_report_cgpa_tab_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=cgpa');

        $response->assertStatus(200);
        $response->assertSee('CGPA Report');
        $response->assertSee('Backlogs');
        $response->assertSee('Program (Filter 1)');
        $response->assertSee('Batch (Filter 2)');
        $response->assertSee('All Batches');
        $response->assertSee('Search');
        $response->assertDontSee('Semester (Filter 3)');
        $response->assertDontSee('All Semesters');
        $response->assertDontSee('All Exam Months');
        $response->assertSee('CSV');
        $response->assertSee('PDF');
    }

    public function test_exam_report_toppers_january_2026_batch_example(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers&batch=JANUARY_2026');

        $response->assertStatus(200);
        // User reference example: 261CC01224 (473 marks) Rank 1, 261CC01469 (472 marks) Rank 2
        $response->assertSee('261CC01224');
        $response->assertSee('473');
        $response->assertSee('261CC01469');
        $response->assertSee('472');
    }

    public function test_exam_report_csv_export_streams_csv(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport/export-csv?tab=fail');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('exam_report_fail', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_exam_report_program_dropdown_has_specialization_options(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport');

        $response->assertStatus(200);
        $response->assertSee('MBA — HRM');
        $response->assertSee('MBA — FINANCE');
        $response->assertSee('MBA (all other MBA)');
        $response->assertSee('BCA (all)');
        $response->assertSee('BBA (all)');
        $response->assertSee('MCA (all)');
    }

    public function test_exam_report_filters_by_specializations(): void
    {
        // 1. MBA — HRM
        $hrmResp = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers&program=mba_hrm');
        $hrmResp->assertStatus(200);
        $hrmResp->assertSee('MBA — HRM');
        $hrmResp->assertSee('251DC01425'); // Top MBA - HRM student

        // 2. MBA — FINANCE
        $finResp = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers&program=mba_finance');
        $finResp->assertStatus(200);
        $finResp->assertSee('MBA — FINANCE');
        $finResp->assertSee('251DC01278'); // Top MBA - Finance student

        // 3. BCA (all)
        $bcaResp = $this->withSession($this->getAdminSession())
            ->get('/examinations/examreport?tab=toppers&program=bca_all');
        $bcaResp->assertStatus(200);
        $bcaResp->assertSee('251DJ01004'); // Top BCA student
    }
}
