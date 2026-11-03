<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove staff menus (HRMS, General/PPR books, staff search) from Student role (role_id 5).
     */
    public function up(): void
    {
        // Staff-only menu IDs that should never be accessible to Students:
        // 104: HRMS, 112: Employee Search, 114: Student Search (Staff), 116: Emp Creation, 130: Activity Logs
        // 140: General, 141: PPR Books view, 143: PPR Books upload
        // 135: Tickets Reply, 136: Tickets KPI, 139: Student Ticket Raise by Emp
        // 144: KPI'S, 145: Exams Reports, 146: Marks KPI, 147: students KPI
        $staffOnlyMenuIds = [
            104, 112, 114, 116, 130, // HRMS & staff employee tools
            140, 141, 143,           // General & PPR documents
            135, 136, 139,           // Staff ticket reply & KPI
            144, 145, 146, 147,      // University-wide KPI & Exam Reports
        ];

        DB::table('master.menu_access_roles')
            ->where('role_id', 5)
            ->whereIn('menu_id', $staffOnlyMenuIds)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-grant default menus if ever needed
    }
};
