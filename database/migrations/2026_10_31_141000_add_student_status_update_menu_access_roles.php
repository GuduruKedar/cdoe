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
        // Menu ID 142 is ADMI0009: Student Status Update under module Admissions (100)
        $roles = [
            1,  // Employee
            2,  // Main Admin(BHANU)
            3,  // DSM
            4,  // Sub Admin
            6,  // OL/ODL Manager
            7,  // VISA Incharge
            11, // Deputy COE
            12, // Director CDOE
        ];

        foreach ($roles as $roleId) {
            DB::table('master.menu_access_roles')->updateOrInsert(
                [
                    'menu_id' => 142,
                    'role_id' => $roleId,
                ],
                [
                    'menu_code' => 'ADMI0009',
                    'campus_id' => '1',
                    'morder' => 8,
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
        DB::table('master.menu_access_roles')
            ->where('menu_id', 142)
            ->whereIn('role_id', [1, 2, 3, 4, 6, 11, 12])
            ->delete();
    }
};
