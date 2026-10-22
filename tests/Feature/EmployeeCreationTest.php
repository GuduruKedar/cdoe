<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeCreationTest extends TestCase
{
    /**
     * Unauthenticated request should redirect to login.
     */
    public function test_emp_creation_page_requires_login(): void
    {
        $response = $this->get('/hrms/EmpCreation');
        $response->assertRedirect('/login');
    }

    /**
     * Authenticated request loads the employee creation form.
     */
    public function test_emp_creation_page_loads_with_session(): void
    {
        $response = $this->withSession([
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ])->get('/hrms/EmpCreation');

        $response->assertStatus(200);
        $response->assertSee('New Employee Onboarding');
        $response->assertSee('Employee Code');
    }

    /**
     * Validation errors triggered when submitting empty form.
     */
    public function test_emp_creation_validation_errors(): void
    {
        $response = $this->withSession([
            'user' => [
                'usercode' => '02266',
                'role_id' => 2,
                'user_type' => 2,
            ],
        ])->post('/hrms/EmpCreation', []);

        $response->assertSessionHasErrors([
            'emp_code',
            'full_name',
            'ofc_mailid',
            'phone_primary',
            'emp_type',
        ]);
    }

    /**
     * Successful submission creates employee and login records.
     */
    public function test_emp_creation_success(): void
    {
        $testEmpCode = 'TEST9999';

        DB::table('master.login')->where('usercode', $testEmpCode)->delete();
        DB::table('master.roles_assigned')->where('emp_id', $testEmpCode)->delete();
        DB::table('hrms.employees')->where('emp_code', $testEmpCode)->delete();

        try {
            $response = $this->withSession([
                'user' => [
                    'usercode' => '02266',
                    'role_id' => 2,
                    'user_type' => 2,
                ],
            ])->post('/hrms/EmpCreation', [
                'emp_code' => $testEmpCode,
                'full_name' => 'Test Employee Automation',
                'ofc_mailid' => 'test9999@vignan.ac.in',
                'personal_mailid' => 'test9999@gmail.com',
                'phone_primary' => '9876543210',
                'emp_type' => 'PERMANENT',
                'date_of_join' => '2026-01-01',
                'is_active' => '1',
                'create_login' => '1',
                'password' => 'Secret123',
            ]);

            $response->assertRedirect('/hrms/EmpSearch?q='.$testEmpCode);
            $this->assertDatabaseHas('hrms.employees', [
                'emp_code' => $testEmpCode,
                'full_name' => 'Test Employee Automation',
            ], 'pgsql');
            $this->assertDatabaseHas('master.login', [
                'usercode' => $testEmpCode,
            ], 'pgsql');
        } finally {
            DB::table('master.login')->where('usercode', $testEmpCode)->delete();
            DB::table('master.roles_assigned')->where('emp_id', $testEmpCode)->delete();
            DB::table('hrms.employees')->where('emp_code', $testEmpCode)->delete();
        }
    }
}
