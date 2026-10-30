<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('examinations.marks_cgpa', 'batch')) {
            Schema::table('examinations.marks_cgpa', function (Blueprint $table) {
                $table->string('batch', 100)->nullable()->index();
            });
        }

        // Backfill existing marks rows from admissions.student_registrations table
        DB::statement('
            UPDATE examinations.marks_cgpa m
            SET batch = s.adm_batch_code
            FROM admissions.student_registrations s
            WHERE (m.regno = s.register_no OR m.appno = s.application_no::text)
              AND s.adm_batch_code IS NOT NULL
              AND (m.batch IS NULL OR m.batch = \'\')
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('examinations.marks_cgpa', 'batch')) {
            Schema::table('examinations.marks_cgpa', function (Blueprint $table) {
                $table->dropColumn('batch');
            });
        }
    }
};
