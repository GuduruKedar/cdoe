<?php

namespace Tests\Feature;

use Modules\Auth\Models\Login;
use Modules\Hrms\Models\Employee;
use Modules\Hrms\Models\RoleAssigned;
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

        Login::where('usercode', $testEmpCode)->delete();
        RoleAssigned::where('emp_id', $testEmpCode)->delete();
        Employee::where('emp_code', $testEmpCode)->delete();

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
            Login::where('usercode', $testEmpCode)->delete();
            RoleAssigned::where('emp_id', $testEmpCode)->delete();
            Employee::where('emp_code', $testEmpCode)->delete();
        }
    }
}
