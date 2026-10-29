<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menuId = 137;

        DB::table('master.menus')->updateOrInsert(
            ['menu_code' => 'EXAM0006'],
            [
                'menu_id' => $menuId,
                'module_id' => 3, // Examinations
                'menu_name' => 'Marks KPI',
                'controller_ref' => 'examinations/markskpi',
                'icon_name' => 'fa fa-bar-chart',
                'is_collapsible' => false,
                'lock_status' => 0,
                'project_id' => 1,
                'campus_id' => '1',
                'log_userid' => '02598',
                'log_ipaddress' => '127.0.0.1',
                'log_timestamp' => now(),
                'parent_menu_id' => 102, // Examinations parent menu
            ]
        );

        // Assign to all administrative, academic, and examination roles
        $roles = [1, 2, 3, 4, 5, 6, 9, 10, 11, 12];
        foreach ($roles as $roleId) {
            DB::table('master.menu_access_roles')->updateOrInsert(
                ['menu_id' => $menuId, 'role_id' => $roleId],
                [
                    'menu_code' => 'EXAM0006',
                    'campus_id' => '1',
                    'morder' => 12,
                    'mposition' => 2,
                    'log_userid' => '02598',
                    'log_ipaddress' => '127.0.0.1',
                    'log_timestamp' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('master.menu_access_roles')->where('menu_id', 137)->delete();
        DB::table('master.menus')->where('menu_id', 137)->delete();
    }
};

