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
        DB::statement("
            UPDATE admissions.student_registrations
            SET adm_batch_code = 'JANUARY_2025', admyear = 2025
            WHERE register_no = '261BHANU99' OR application_no = '202699999999'
        ");

        DB::statement("
            UPDATE examinations.marks_cgpa
            SET batch = 'JANUARY_2025'
            WHERE regno = '261BHANU99' OR appno::text = '202699999999'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE admissions.student_registrations
            SET adm_batch_code = 'JANUARY_2026', admyear = 2026
            WHERE register_no = '261BHANU99' OR application_no = '202699999999'
        ");

        DB::statement("
            UPDATE examinations.marks_cgpa
            SET batch = 'JANUARY_2026'
            WHERE regno = '261BHANU99' OR appno::text = '202699999999'
        ");
    }
};

