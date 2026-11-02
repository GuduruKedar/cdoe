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
        // 1. Menu ID 141 is GENE0002: PPR Books view under General (140)
        // All roles that have General should have access to view PPR Books
        $viewRoles = [
            1,  // Employee
            2,  // Main Admin(BHANU)
            3,  // DSM
            4,  // Sub Admin
            5,  // Student
            6,  // OL/ODL Manager
            7,  // VISA Incharge
            8,  // Jr.Asst.
            9,  // PC,CMM,OD Incharge
            10, // Results Coordinator
            11, // Deputy COE
            12, // Director CDOE
        ];

        foreach ($viewRoles as $roleId) {
            DB::table('master.menu_access_roles')->updateOrInsert(
                [
                    'menu_id' => 141,
                    'role_id' => $roleId,
                ],
                [
                    'menu_code' => 'GENE0002',
                    'campus_id' => '1',
                    'morder' => 100,
                    'mposition' => 2,
                    'log_userid' => '02598',
                    'log_ipaddress' => '127.0.0.1',
                    'log_timestamp' => now(),
                ]
            );
        }

        // 2. Menu ID 143 is GENE0003: PPR Books upload under General (140)
        // Admin & Management roles that can upload regulations
        $uploadRoles = [
            2,  // Main Admin(BHANU)
            4,  // Sub Admin
            10, // Results Coordinator
            12, // Director CDOE
        ];

        foreach ($uploadRoles as $roleId) {
            DB::table('master.menu_access_roles')->updateOrInsert(
                [
                    'menu_id' => 143,
                    'role_id' => $roleId,
                ],
                [
                    'menu_code' => 'GENE0003',
                    'campus_id' => '1',
                    'morder' => 101,
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
            ->whereIn('menu_id', [141, 143])
            ->whereIn('role_id', [1, 2, 3, 4, 6, 8, 9, 11, 12])
            ->delete();
    }
};

