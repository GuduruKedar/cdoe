<?php

namespace Tests\Feature;

use App\Common\Services\MenuService;
use App\Common\Services\RoleService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class NewMenusAndScreensTest extends TestCase
{
    protected function getAdminSession(): array
    {
        return [
            'user' => [
                'usercode' => '02266',
                'role_id' => 6, // Super Admin
                'user_type' => 2,
            ],
        ];
    }

    public function test_super_admin_role_and_menus_resolution(): void
    {
        $hasRole = RoleService::userHasRole('02266', 6);
        $this->assertTrue($hasRole, 'User 02266 should have Super Admin role (role_id 6) assigned.');

        $user = (object) ['usercode' => '02266', 'role_id' => 6];
        session(['user' => ['usercode' => '02266', 'role_id' => 6]]);
        $menus = MenuService::getMenusForUser($user);

        $this->assertIsArray($menus);
        $menuNames = array_column($menus, 'menu_name');
        $this->assertContains('Admissions', $menuNames);
        $this->assertContains('Academics', $menuNames);
        $this->assertContains('Examinations', $menuNames);

        // Find Admissions menu children
        $admissions = collect($menus)->firstWhere('menu_name', 'Admissions');
        $this->assertNotNull($admissions);
        $childNames = array_column($admissions['children'], 'menu_name');
        $this->assertContains('Document Upload', $childNames);
        $this->assertContains('Document View', $childNames);
        $this->assertContains('Admin Data Download', $childNames);

        // Find Academics menu children
        $academics = collect($menus)->firstWhere('menu_name', 'Academics');
        $this->assertNotNull($academics);
        $acadChildNames = array_column($academics['children'], 'menu_name');
        $this->assertContains('Course Reg Download', $acadChildNames);

        // Find Examinations menu children
        $exams = collect($menus)->firstWhere('menu_name', 'Examinations');
        $this->assertNotNull($exams);
        $examChildNames = array_column($exams['children'], 'menu_name');
        $this->assertContains('CGPA Report Download', $examChildNames);
    }

    public function test_document_upload_screen_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/docupload');

        $response->assertStatus(200);
        $response->assertSee('Student Document');
        $response->assertSee('Upload');
        $response->assertSee('Passport Photo');
        $response->assertSee('10th / SSC Certificate');
    }

    public function test_document_view_screen_loads(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/docview');

        $response->assertStatus(200);
        $response->assertSee('Active Filters:');
        $response->assertSee('Photo');
        $response->assertSee('Aadhar');
        $response->assertSee('PAN');
    }

    public function test_admission_data_download_screen_and_export(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/datadownload');

        $response->assertStatus(200);
        $response->assertSee('Active Filters:');
        $response->assertSee('Total Records:');

        // Test export CSV
        $export = $this->withSession($this->getAdminSession())
            ->get('/admissions/datadownload/export');

        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', $export->headers->get('content-type'));
    }

    public function test_course_registration_download_screen_and_export(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/academics/coursereg');

        $response->assertStatus(200);
        $response->assertSee('Active Filters:');
        $response->assertSee('Course Registration Download');

        $export = $this->withSession($this->getAdminSession())
            ->get('/academics/coursereg/export');

        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', $export->headers->get('content-type'));
    }

    public function test_cgpa_marks_report_download_screen_and_export(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/cgpamarks');

        $response->assertStatus(200);
        $response->assertSee('Active Filters:');
        $response->assertSee('CGPA Marks Report Download');

        $export = $this->withSession($this->getAdminSession())
            ->get('/examinations/cgpamarks/export');

        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', $export->headers->get('content-type'));
    }

    public function test_document_upload_validation(): void
    {
        // Try uploading jpg for a pdf requirement
        $file = UploadedFile::fake()->create('marksheet.jpg', 100, 'image/jpeg');

        $response = $this->withSession($this->getAdminSession())
            ->postJson('/admissions/docupload/upload', [
                'application_no' => 2026010001,
                'register_no' => '261CC01001',
                'doctype' => '10th',
                'file' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('PDF', $response->json('message'));
    }
}
