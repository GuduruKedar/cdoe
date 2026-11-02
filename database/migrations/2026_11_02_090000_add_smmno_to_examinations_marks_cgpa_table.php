<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('examinations.marks_cgpa', 'smmno')) {
            Schema::table('examinations.marks_cgpa', function (Blueprint $table) {
                // Same properties as mmno (character varying(20)), nullable and left empty
                $table->string('smmno', 20)->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('examinations.marks_cgpa', 'smmno')) {
            Schema::table('examinations.marks_cgpa', function (Blueprint $table) {
                $table->dropColumn('smmno');
            });
        }
    }
};
