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
        // 1. Assign Examination menus to COE (role_id = 3)
        $coeMenus = [
            ['menu_id' => 102, 'menu_code' => 'EXAM0001', 'morder' => 1, 'mposition' => 1],
            ['menu_id' => 105, 'menu_code' => 'EXAM0002', 'morder' => 2, 'mposition' => 2],
            ['menu_id' => 108, 'menu_code' => 'EXAM0003', 'morder' => 3, 'mposition' => 2],
            ['menu_id' => 128, 'menu_code' => 'EXAM0004', 'morder' => 4, 'mposition' => 2],
            ['menu_id' => 129, 'menu_code' => 'EXAM0005', 'morder' => 5, 'mposition' => 2],
        ];

        foreach ($coeMenus as $item) {
            DB::table('master.menu_access_roles')->updateOrInsert(
                [
                    'menu_id' => $item['menu_id'],
                    'role_id' => 3, // COE
                ],
                [
                    'menu_code' => $item['menu_code'],
                    'campus_id' => '1',
                    'morder' => $item['morder'],
                    'mposition' => $item['mposition'],
                    'log_userid' => '02598',
                    'log_ipaddress' => '192.168.111.111',
                    'log_timestamp' => now(),
                ]
            );
        }

        // 2. Assign COE role to 00084 (Dedicated COE account)
        DB::table('master.roles_assigned')->updateOrInsert(
            [
                'emp_id' => '00084',
                'roles_role_id' => 3,
            ],
            [
                'campus_id' => 1,
                'start_date' => now(),
                'end_date' => '2100-01-01',
                'last_access_date' => now(),
                'log_userid' => '02598',
                'log_timestamp' => now(),
                'log_ipaddress' => '127.0.0.1',
            ]
        );

        // 3. Also assign COE role to 02266 so admin can switch to COE from role switcher
        DB::table('master.roles_assigned')->updateOrInsert(
            [
                'emp_id' => '02266',
                'roles_role_id' => 3,
            ],
            [
                'campus_id' => 1,
                'start_date' => now(),
                'end_date' => '2100-01-01',
                'last_access_date' => now(),
                'log_userid' => '02598',
                'log_timestamp' => now(),
                'log_ipaddress' => '127.0.0.1',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('master.menu_access_roles')->where('role_id', 3)->delete();
        DB::table('master.roles_assigned')->where('roles_role_id', 3)->delete();
    }
};
