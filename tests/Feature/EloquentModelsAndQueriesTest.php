<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Program;
use App\Models\Role;
use App\Models\RoleAssigned;
use Modules\Academics\Models\CoPoPsoMapping;
use Modules\Academics\Models\CourseRegistration;
use Modules\Academics\Models\Subject;
use Modules\Admissions\Models\Programs;
use Modules\Admissions\Models\StudentDocument;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Auth\Models\Login;
use Modules\Examinations\Models\MarksCgpa;
use Modules\Examinations\Models\MarksMemo;
use Tests\TestCase;

class EloquentModelsAndQueriesTest extends TestCase
{
    /**
     * Test all Eloquent models can query their corresponding PostgreSQL tables.
     */
    public function test_models_query_configured_tables_successfully(): void
    {
        $this->assertNotEmpty((new Subject)->getTable());
        $this->assertNotEmpty((new CourseRegistration)->getTable());
        $this->assertNotEmpty((new CoPoPsoMapping)->getTable());
        $this->assertNotEmpty((new StudentRegistration)->getTable());
        $this->assertNotEmpty((new StudentDocument)->getTable());
        $this->assertNotEmpty((new Programs)->getTable());
        $this->assertNotEmpty((new Program)->getTable());
        $this->assertNotEmpty((new MarksMemo)->getTable());
        $this->assertNotEmpty((new MarksCgpa)->getTable());
        $this->assertNotEmpty((new Employee)->getTable());
        $this->assertNotEmpty((new Designation)->getTable());
        $this->assertNotEmpty((new Department)->getTable());
        $this->assertNotEmpty((new Campus)->getTable());
        $this->assertNotEmpty((new Role)->getTable());
        $this->assertNotEmpty((new RoleAssigned)->getTable());
        $this->assertNotEmpty((new Login)->getTable());

        // Test counts execute without SQL errors
        $this->assertIsInt(Subject::count());
        $this->assertIsInt(CourseRegistration::count());
        $this->assertIsInt(CoPoPsoMapping::count());
        $this->assertIsInt(StudentRegistration::count());
        $this->assertIsInt(StudentDocument::count());
        $this->assertIsInt(Programs::count());
        $this->assertIsInt(MarksMemo::count());
        $this->assertIsInt(Employee::count());
    }

    /**
     * Test refactored controllers serve successfully via Eloquent queries.
     */
    public function test_refactored_controllers_render_with_eloquent(): void
    {
        $superAdminSession = [
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ];

        $routes = [
            '/dashboard',
            '/academics/subjectsview',
            '/academics/subcopopso',
            '/academics/coursereg',
            '/academics/courseregisupload',
            '/admissions/stdsearch',
            '/admissions/datadownload',
            '/admissions/admdataupload',
            '/admissions/docview',
            '/admissions/docupload',
            '/examinations/stdmarks',
            '/examinations/cgpamarks',
            '/examinations/cgpamarkupload',
            '/hrms/EmpSearch',
            '/hrms/EmpCreation',
        ];

        foreach ($routes as $route) {
            $response = $this->withSession($superAdminSession)->get($route);
            $response->assertStatus(200);
        }
    }
}
