<?php

namespace App\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\RoleService;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RoleSwitchController extends Controller
{
    public function switch(Request $request, int $roleId)
    {
        $userCode = Session::get('user.usercode');
        if (! $userCode) {
            return redirect()->route('login');
        }

        // Validate user really has this role (checked only at click time)
        if (! RoleService::userHasRole($userCode, $roleId)) {
            return back()->with('error', 'You do not have access to this role.');
        }

        $user = Session::get('user');
        $oldRoleId = $user['role_id'] ?? null;
        $user['role_id'] = $roleId;
        Session::put('user', $user);

        $roleObj = Role::where('role_id', $roleId)->first();
        $newRoleName = $roleObj ? $roleObj->role_name : "Role #{$roleId}";

        ActivityLogger::log('AUTH', 'SWITCH_ROLE', "User {$userCode} switched active role to {$newRoleName}.", [
            'user_code' => $userCode,
            'role_id' => $roleId,
            'role_name' => $newRoleName,
            'payload' => [
                'previous_role_id' => $oldRoleId,
                'new_role_id' => $roleId,
            ],
            'status' => 'success',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Role switched successfully.');
    }
}
