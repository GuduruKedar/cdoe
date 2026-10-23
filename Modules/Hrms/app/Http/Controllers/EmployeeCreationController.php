<?php

namespace Modules\Hrms\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\Login;
use Modules\Hrms\Models\Campus;
use Modules\Hrms\Models\Department;
use Modules\Hrms\Models\Designation;
use Modules\Hrms\Models\Employee;
use Modules\Hrms\Models\RoleAssigned;

class EmployeeCreationController extends Controller
{
    use RequiresLogin;

    /**
     * Show form to create new employee.
     */
    public function create(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $designations = Designation::where('lock_status', 0)
            ->orderBy('designation_level')
            ->orderBy('designation_name')
            ->get();

        $campuses = Campus::where('lock_status', 0)
            ->orderBy('campus_name')
            ->get();

        $roles = Role::where('role_status', 1)
            ->orderBy('role_name')
            ->get();

        $departments = Department::orderBy('department_name')
            ->get();

        $empTypes = ['PERMANENT', 'INTERN', 'CONTRACT', 'VISITING', 'GUEST'];

        return view('hrms::empcreation', compact(
            'user',
            'menus',
            'designations',
            'campuses',
            'roles',
            'departments',
            'empTypes'
        ));
    }

    /**
     * Store newly created employee in database.
     */
    public function store(Request $request)
    {
        $user = $this->requireLogin();

        $validated = $request->validate([
            'emp_code' => [
                'required',
                'string',
                'max:50',
                'unique:pgsql.hrms.employees,emp_code',
                'unique:pgsql.master.login,usercode',
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'ofc_mailid' => ['required', 'email', 'max:255'],
            'personal_mailid' => ['nullable', 'email', 'max:255'],
            'phone_primary' => ['required', 'string', 'max:20'],
            'phone_secondary' => ['nullable', 'string', 'max:20'],
            'emp_type' => ['required', 'string', 'max:50'],
            'date_of_join' => ['nullable', 'date'],
            'designation_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'campus_id' => ['nullable', 'string', 'max:20'],
            'role_id' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'create_login' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'emp_code.required' => 'Employee Code is required.',
            'emp_code.unique' => 'An employee with this Employee Code already exists.',
            'full_name.required' => 'Full Name is required.',
            'ofc_mailid.required' => 'Official Email is required.',
            'ofc_mailid.email' => 'Please provide a valid Official Email address.',
            'phone_primary.required' => 'Primary Phone Number is required.',
            'emp_type.required' => 'Employment Type is required.',
        ]);

        $isActive = $request->boolean('is_active', true);
        $createLogin = $request->boolean('create_login', true);
        $empCode = trim($validated['emp_code']);

        DB::transaction(function () use ($validated, $empCode, $isActive, $createLogin, $user, $request) {
            // 1. Insert into hrms.employees via Employee model
            Employee::create([
                'emp_code' => $empCode,
                'full_name' => trim($validated['full_name']),
                'ofc_mailid' => trim($validated['ofc_mailid']),
                'personal_mailid' => ! empty($validated['personal_mailid']) ? trim($validated['personal_mailid']) : null,
                'phone_primary' => trim($validated['phone_primary']),
                'phone_secondary' => ! empty($validated['phone_secondary']) ? trim($validated['phone_secondary']) : null,
                'designation_id' => ! empty($validated['designation_id']) ? (int) $validated['designation_id'] : null,
                'department_id' => ! empty($validated['department_id']) ? (int) $validated['department_id'] : null,
                'campus_id' => ! empty($validated['campus_id']) ? (string) $validated['campus_id'] : null,
                'role_id' => ! empty($validated['role_id']) ? (int) $validated['role_id'] : null,
                'emp_type' => $validated['emp_type'],
                'date_of_join' => ! empty($validated['date_of_join']) ? $validated['date_of_join'] : null,
                'is_active' => $isActive,
                'log_userid' => $user->usercode ?? session('user.usercode', 'admin'),
                'log_ipaddress' => $request->ip(),
                'log_timestamp' => now(),
            ]);

            // 2. Optionally create portal login account via Login model
            if ($createLogin) {
                $plainPassword = $validated['password'] ?? 'Vignan@123';
                Login::create([
                    'usercode' => $empCode,
                    'phnno' => trim($validated['phone_primary']),
                    'officialmail' => trim($validated['ofc_mailid']),
                    'personalmail' => ! empty($validated['personal_mailid']) ? trim($validated['personal_mailid']) : null,
                    'pwd' => Hash::make($plainPassword),
                    'pwd_changed_at' => now(),
                    'user_type' => 2, // Staff
                    'role_id' => ! empty($validated['role_id']) ? (int) $validated['role_id'] : 1, // Default Employee role
                    'campus_id' => ! empty($validated['campus_id']) ? (string) $validated['campus_id'] : '1',
                    'designation_id' => ! empty($validated['designation_id']) ? (string) $validated['designation_id'] : null,
                    'department_id' => ! empty($validated['department_id']) ? (string) $validated['department_id'] : null,
                    'is_active' => $isActive,
                    'lock_status' => 0,
                    'log_userid' => $user->usercode ?? session('user.usercode', 'admin'),
                    'log_ipaddress' => $request->ip(),
                    'log_timestamp' => now(),
                ]);

                // 3. Assign default or selected role in master.roles_assigned via RoleAssigned model
                $assignedRoleId = ! empty($validated['role_id']) ? (int) $validated['role_id'] : 1;
                RoleAssigned::create([
                    'emp_id' => $empCode,
                    'campus_id' => ! empty($validated['campus_id']) ? (string) $validated['campus_id'] : '1',
                    'roles_role_id' => $assignedRoleId,
                    'start_date' => ! empty($validated['date_of_join']) ? $validated['date_of_join'] : now(),
                    'end_date' => null,
                    'last_access_date' => now(),
                    'log_userid' => $user->usercode ?? session('user.usercode', 'admin'),
                    'log_ipaddress' => $request->ip(),
                    'log_timestamp' => now(),
                ]);
            }
        });

        ActivityLogger::log('HRMS', 'CREATE_EMPLOYEE', "Created employee {$validated['full_name']} (Emp Code: {$empCode}).", [
            'module' => 'HRMS',
            'action' => 'CREATE_EMPLOYEE',
            'payload' => [
                'emp_code' => $empCode,
                'full_name' => $validated['full_name'],
                'ofc_mailid' => $validated['ofc_mailid'],
                'role_id' => $validated['role_id'] ?? null,
                'create_login' => $createLogin,
            ],
            'status' => 'success',
        ]);

        return redirect()->route('hrms.empsearch', ['q' => $empCode])
            ->with('success', "Employee {$validated['full_name']} ({$empCode}) was created successfully!");
    }
}
