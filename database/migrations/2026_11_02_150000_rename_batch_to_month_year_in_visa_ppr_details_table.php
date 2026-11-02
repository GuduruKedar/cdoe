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
        Schema::table('visa.ppr_details', function (Blueprint $table) {
            $table->renameColumn('batch', 'month_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa.ppr_details', function (Blueprint $table) {
            $table->renameColumn('month_year', 'batch');
        });
    }
};
