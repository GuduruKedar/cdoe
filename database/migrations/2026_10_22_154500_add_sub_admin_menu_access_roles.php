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
        $subAdminMenus = [
            // Admissions module & screens
            ['menu_id' => 100, 'menu_code' => 'ADMI0001', 'morder' => 1, 'mposition' => 1],
            ['menu_id' => 122, 'menu_code' => 'ADMI0004', 'morder' => 2, 'mposition' => 2],
            ['menu_id' => 123, 'menu_code' => 'ADMI0005', 'morder' => 3, 'mposition' => 2],
            ['menu_id' => 124, 'menu_code' => 'ADMI0006', 'morder' => 4, 'mposition' => 2],
            ['menu_id' => 125, 'menu_code' => 'ADMI0007', 'morder' => 5, 'mposition' => 2],

            // Academics module & screens
            ['menu_id' => 101, 'menu_code' => 'ACAD0001', 'morder' => 6, 'mposition' => 1],
            ['menu_id' => 126, 'menu_code' => 'ACAD0009', 'morder' => 7, 'mposition' => 2],
            ['menu_id' => 127, 'menu_code' => 'ACAD0010', 'morder' => 8, 'mposition' => 2],

            // Examinations module & screens
            ['menu_id' => 102, 'menu_code' => 'EXAM0001', 'morder' => 9, 'mposition' => 1],
            ['menu_id' => 128, 'menu_code' => 'EXAM0004', 'morder' => 10, 'mposition' => 2],
            ['menu_id' => 129, 'menu_code' => 'EXAM0005', 'morder' => 11, 'mposition' => 2],
        ];

        foreach ($subAdminMenus as $item) {
            DB::table('master.menu_access_roles')->updateOrInsert(
                [
                    'menu_id' => $item['menu_id'],
                    'role_id' => 4, // Sub Admin
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('master.menu_access_roles')->where('role_id', 4)->delete();
    }
};
