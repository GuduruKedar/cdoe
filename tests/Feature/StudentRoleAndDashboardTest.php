<?php

namespace Tests\Feature;

use App\Common\Services\MenuService;
use Modules\Auth\Models\Login;
use Tests\TestCase;

class StudentRoleAndDashboardTest extends TestCase
{
    public function test_student_role_menus_do_not_contain_staff_menus(): void
    {
        // 261BHANU99 is a student with role_id 5
        $user = Login::where('role_id', 5)->first();
        if (! $user) {
            $user = (object) ['usercode' => '261BHANU99', 'role_id' => 5, 'user_type' => 1];
        }

        session(['user' => [
            'usercode' => $user->usercode,
            'role_id' => 5,
            'user_type' => 1,
        ]]);

        $menus = MenuService::getMenusForUser($user);
        $this->assertIsArray($menus);

        $menuNames = array_column($menus, 'menu_name');

        // Student MUST NOT have staff menus: HRMS, General (PPR books)
        $this->assertNotContains('HRMS', $menuNames, 'Student should not have access to HRMS menu.');
        $this->assertNotContains('General', $menuNames, 'Student should not have access to General / PPR Books menu.');

        // Student SHOULD have their academic, examination, admissions & helpdesk menus
        $this->assertContains('Academics', $menuNames);
        $this->assertContains('Examinations', $menuNames);
        $this->assertContains('Admissions', $menuNames);
        $this->assertContains('Help&Tickets(VISA)', $menuNames);
    }

    public function test_student_role_dashboard_loads_student_portal(): void
    {
        $user = Login::where('role_id', 5)->first();
        $usercode = $user?->usercode ?: '261BHANU99';

        $response = $this->withSession([
            'user' => [
                'usercode' => $usercode,
                'role_id' => 5,
                'user_type' => 1,
            ],
        ])->get('/dashboard');

        $response->assertStatus(200);

        // Must NOT see staff-only metrics
        $response->assertDontSee('Faculty & Staff Members');
        $response->assertDontSee('Total Registered Students');

        // Must see student academic metrics & services
        $response->assertSee('Dashboard');
        $response->assertSee('Examination Results');
        $response->assertSee('My Subject List');
        $response->assertSee('Admission Info');
        $response->assertSee('Raise Ticket');
    }

    public function test_staff_switching_to_student_role_sees_student_portal(): void
    {
        // Staff user 02598 who has role_id 5 assigned
        $response = $this->withSession([
            'user' => [
                'usercode' => '02598',
                'role_id' => 5, // switched to student
                'user_type' => 2, // employee in master.login
            ],
        ])->get('/dashboard');

        $response->assertStatus(200);

        // Must NOT see staff dashboard metrics when viewing as Student
        $response->assertDontSee('Faculty & Staff Members');
        $response->assertDontSee('Total Registered Students');

        // Must see student services
        $response->assertSee('Raise Ticket');
        $response->assertSee('Examination Results');
        $response->assertSee('Student Role Preview');
        $response->assertSee('Session 2026-2027 • Active Student');
    }

    public function test_student_subject_list_loads_registered_subjects_from_course_registration(): void
    {
        $response = $this->withSession([
            'user' => [
                'usercode' => '261CK01026',
                'role_id' => 5,
                'user_type' => 1,
            ],
        ])->get('/academics/subjectlist');

        $response->assertStatus(200);
        $response->assertSee('My Registered Subjects');
        $response->assertSee('Registered Courses');
        $response->assertSee('O25BB101');
        $response->assertSee('PRINCIPLES AND PRACTICES OF MANAGEMENT');
    }

    public function test_login_page_register_tab_shows_updated_contact_info(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Mr. T. Venkatesh');
        $response->assertSee('Jr. Assistant');
        $response->assertSee('7842988298');
        $response->assertDontSee('Mr. D. V. N. S. M. Karthik');
        $response->assertDontSee('8886852292');
    }
}
