<?php

namespace App\Common\Services;

use App\Models\Menu;
use App\Models\MenuAccessRole;
use App\Models\Role;

class MenuService
{
    public static function getMenusForUser($user): ?array
    {
        // Prefer session role (switcher may have changed it)
        $sessionRoleId = session('user.role_id');
        $roleId = $sessionRoleId ?: ($user->role_id ?? null);

        if (! $user || ! $roleId) {
            return null;
        }

        $role = Role::where('role_id', $roleId)
            ->where('role_status', 1)
            ->first();
        if (! $role) {
            return null;
        }

        $rows = MenuAccessRole::where('role_id', $roleId)
            ->orderBy('mposition')
            ->orderBy('morder')
            ->get();
        if ($rows->isEmpty()) {
            return null;
        }

        $allowedIds = $rows->pluck('menu_id')->toArray();

        $menuRecords = Menu::whereIn('menu_id', $allowedIds)
            ->where('lock_status', 0)
            ->get()
            ->keyBy('menu_id');
        if ($menuRecords->isEmpty()) {
            return null;
        }

        $meta = [];
        foreach ($rows as $r) {
            $meta[$r->menu_id] = [
                'mposition' => (int) $r->mposition,
                'morder' => (int) $r->morder,
            ];
        }

        return self::buildTree($menuRecords, $meta);
    }

    private static function buildTree($menuRecords, $meta, $parentId = null): array
    {
        $branch = [];

        foreach ($menuRecords as $menu) {
            if ((int) $menu->parent_menu_id !== (int) $parentId) {
                continue;
            }

            $m = $meta[$menu->menu_id] ?? ['mposition' => 1, 'morder' => 0];

            $branch[] = [
                'menu_id' => $menu->menu_id,
                'menu_code' => $menu->menu_code,
                'menu_name' => $menu->menu_name,
                'controller_ref' => $menu->controller_ref,
                'icon_name' => $menu->icon_name,
                'is_collapsible' => (bool) $menu->is_collapsible,
                'mposition' => $m['mposition'],
                'morder' => $m['morder'],
                'url' => self::resolveUrl($menu->controller_ref),
                'children' => self::buildTree($menuRecords, $meta, $menu->menu_id),
            ];
        }

        usort($branch, fn ($a, $b) => $a['morder'] <=> $b['morder']);

        return $branch;
    }

    public static function resolveUrl(?string $ref): ?string
    {
        if (! $ref) {
            return null;
        }

        if (strpos($ref, '/') !== false && strpos($ref, '@') === false) {
            return '/'.ltrim($ref, '/');
        }

        $controller = explode('@', $ref)[0] ?? '';

        return [
            'DashboardController' => '/dashboard',
            'AcademicController' => '/academics',
            'AdmissionController' => '/admissions',
            'AdmissionApplicationController' => '/admissions/applications',
            'ExaminationController' => '/examinations',
            'FinanceController' => '/finance',
            'HrmsController' => '/hrms',
            'MasterController' => '/master',
            'VisaController' => '/visa',
            'TimetableController' => '/academics/timetable',
            'FacultyController' => '/academics/faculty',
            'ClassAttendanceController' => '/academics/attendance/class',
            'HostelAttendanceController' => '/academics/attendance/hostel',
        ][$controller] ?? null;
    }
}
