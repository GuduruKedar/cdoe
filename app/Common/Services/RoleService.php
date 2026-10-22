<?php

namespace App\Common\Services;

use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Get all ACTIVE roles assigned to a user.
     * Returns [] if none — never throws.
     */
    public static function getAssignedRoles(string $employeeId): array
    {
        try {
            return DB::connection('pgsql')
                ->table('master.roles_assigned as ra')
                ->join('master.roles as r', 'ra.roles_role_id', '=', 'r.role_id')
                ->where('ra.emp_id', $employeeId)
                ->where(function ($q) {
                    $q->whereNull('ra.end_date')
                        ->orWhere('ra.end_date', '>=', now()->toDateString());
                })
                ->where('r.role_status', 1)
                ->select('r.role_id', 'r.role_name', 'ra.start_date', 'ra.end_date')
                ->orderBy('r.role_name')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();
        } catch (\Throwable $e) {
            \Log::warning('RoleService::getAssignedRoles failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Check if user has a valid assigned role.
     */
    public static function userHasRole(string $employeeId, int $roleId): bool
    {
        try {
            return DB::connection('pgsql')
                ->table('master.roles_assigned as ra')
                ->where('ra.emp_id', $employeeId)
                ->where('ra.roles_role_id', $roleId)
                ->where(function ($q) {
                    $q->whereNull('ra.end_date')
                        ->orWhere('ra.end_date', '>=', now()->toDateString());
                })
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
