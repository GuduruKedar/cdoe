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
                'role_id' => 2, // Super Admin
                'user_type' => 2,
            ],
        ];
    }

    public function test_super_admin_role_and_menus_resolution(): void
    {
        $hasRole = RoleService::userHasRole('02266', 2);
        $this->assertTrue($hasRole, 'User 02266 should have Super Admin role (role_id 2) assigned.');

        $user = (object) ['usercode' => '02266', 'role_id' => 2];
        session(['user' => ['usercode' => '02266', 'role_id' => 2]]);
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
        $this->assertContains('Doc Upload', $childNames);
        $this->assertContains('Doc download', $childNames);

        // Find Academics menu children
        $academics = collect($menus)->firstWhere('menu_name', 'Academics');
        $this->assertNotNull($academics);
        $acadChildNames = array_column($academics['children'], 'menu_name');
        $this->assertContains('course regis Downlaod', $acadChildNames);

        // Find Examinations menu children
        $exams = collect($menus)->firstWhere('menu_name', 'Examinations');
        $this->assertNotNull($exams);
        $examChildNames = array_column($exams['children'], 'menu_name');
        $this->assertContains('cgpamarkdownload', $examChildNames);
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

    public function test_admission_data_upload_screen_and_sample_template(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/admissions/addatauplaodsd');

        $response->assertStatus(200);
        $response->assertSee('Admission Data Upload');
        $response->assertSee('Download Sample CSV Template');

        $sample = $this->withSession($this->getAdminSession())
            ->get('/admissions/addatauplaod/sample-template');

        $sample->assertStatus(200);
        $this->assertStringContainsString('text/csv', $sample->headers->get('content-type'));
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

    public function test_course_registration_upload_screen_and_sample_template(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/academics/courseregisuploadsd');

        $response->assertStatus(200);
        $response->assertSee('Course Registration Upload');
        $response->assertSee('Download Sample CSV Template');

        $sample = $this->withSession($this->getAdminSession())
            ->get('/academics/courseregisupload/sample-template');

        $sample->assertStatus(200);
        $this->assertStringContainsString('text/csv', $sample->headers->get('content-type'));
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

    public function test_cgpa_marks_upload_screen_and_sample_template(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/examinations/cgpamarkuploadsd');

        $response->assertStatus(200);
        $response->assertSee('CGPA Marks Upload');
        $response->assertSee('Download Sample CSV Template');

        $sample = $this->withSession($this->getAdminSession())
            ->get('/examinations/cgpamarkupload/sample-template');

        $sample->assertStatus(200);
        $this->assertStringContainsString('text/csv', $sample->headers->get('content-type'));
    }

    public function test_subjects_view_screen_and_export(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/academics/subjectsview');

        $response->assertStatus(200);
        $response->assertSee('Curriculum & Subject List');
        $response->assertSee('Subject Type');
        $response->assertSee('Delivery Mode');

        $export = $this->withSession($this->getAdminSession())
            ->get('/academics/subjectsview/export');

        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', $export->headers->get('content-type'));
    }

    public function test_sub_co_po_pso_screen_and_export(): void
    {
        $response = $this->withSession($this->getAdminSession())
            ->get('/academics/subcopopso');

        $response->assertStatus(200);
        $response->assertSee('Sub CO-PO-PSO Mapping');
        $response->assertSee('Course / Subject');
        $response->assertSee('Course Outcome (CO)');

        $export = $this->withSession($this->getAdminSession())
            ->get('/academics/subcopopso/export');

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

    public function test_dynamic_navbar_brand_title_per_role(): void
    {
        // 1. Super Admin role
        $response = $this->withSession($this->getAdminSession())
            ->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('CDOE Super Admin');

        // 2. Administrator role
        $adminSession = [
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ];
        $responseAdmin = $this->withSession($adminSession)
            ->get('/dashboard');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Super Admin');

        // 3. Student role
        $studentSession = [
            'user' => [
                'usercode' => '261CC01014',
                'role_id' => 5,
                'user_type' => 1,
            ],
        ];
        $responseStudent = $this->withSession($studentSession)
            ->get('/dashboard');
        $responseStudent->assertStatus(200);
        $responseStudent->assertSee('CDOE Student');
    }

    public function test_sub_admin_role_switch_and_distinct_menu_resolution(): void
    {
        // 1. User 02266 has both role 2 (Super Admin) and role 4 (Sub Admin)
        $this->assertTrue(RoleService::userHasRole('02266', 4));
        $this->assertTrue(RoleService::userHasRole('02266', 2));

        // 2. Perform role switch to Sub Admin (role 4)
        $session = $this->getAdminSession();
        $response = $this->withSession($session)
            ->post('/switch-role/4');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('user.role_id', 4);

        // 3. Verify Sub Admin menu structure
        $subAdminUser = (object) ['usercode' => '02266', 'role_id' => 4];
        session(['user' => ['usercode' => '02266', 'role_id' => 4]]);
        $subAdminMenus = MenuService::getMenusForUser($subAdminUser);

        $this->assertIsArray($subAdminMenus);
        $topLevelNames = array_column($subAdminMenus, 'menu_name');

        // Sub Admin must have operational modules
        $this->assertContains('Admissions', $topLevelNames);
        $this->assertContains('Academics', $topLevelNames);
        $this->assertContains('Examinations', $topLevelNames);

        // Sub Admin must NOT have HRMS
        $this->assertNotContains('HRMS', $topLevelNames);

        // 4. Verify Dashboard displays Sub Admin role correctly without reverting
        $dashResponse = $this->withSession(['user' => ['usercode' => '02266', 'role_id' => 4, 'user_type' => 2]])
            ->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Sub Admin');
        $this->assertEquals(4, session('user.role_id'));
    }
}
