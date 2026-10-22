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
        $menuId = 130;

        DB::table('master.menus')->updateOrInsert(
            ['menu_code' => 'HRMS0005'],
            [
                'menu_id' => $menuId,
                'module_id' => 4,
                'menu_name' => 'Activity Logs',
                'controller_ref' => 'hrms/activitylogs',
                'icon_name' => 'bi bi-clock-history',
                'is_collapsible' => false,
                'lock_status' => 0,
                'project_id' => 1,
                'campus_id' => '1',
                'log_userid' => 'admin',
                'log_ipaddress' => '127.0.0.1',
                'log_timestamp' => now(),
                'parent_menu_id' => 104,
            ]
        );

        // Assign to Role 2 (Administrator)
        DB::table('master.menu_access_roles')->updateOrInsert(
            ['menu_id' => $menuId, 'role_id' => 2],
            [
                'menu_code' => 'HRMS0005',
                'campus_id' => '1',
                'morder' => 3,
                'mposition' => 2,
                'log_userid' => 'admin',
                'log_ipaddress' => '127.0.0.1',
                'log_timestamp' => now(),
            ]
        );

        // Assign to Role 1 (Employee)
        DB::table('master.menu_access_roles')->updateOrInsert(
            ['menu_id' => $menuId, 'role_id' => 1],
            [
                'menu_code' => 'HRMS0005',
                'campus_id' => '1',
                'morder' => 10,
                'mposition' => 2,
                'log_userid' => 'admin',
                'log_ipaddress' => '127.0.0.1',
                'log_timestamp' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('master.menu_access_roles')->where('menu_code', 'HRMS0005')->delete();
        DB::table('master.menus')->where('menu_code', 'HRMS0005')->delete();
    }
};
