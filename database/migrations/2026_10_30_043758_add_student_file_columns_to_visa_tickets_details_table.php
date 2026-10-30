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
        Schema::table('visa.tickets_details', function (Blueprint $table) {
            if (! Schema::hasColumn('visa.tickets_details', 'std_tkt_file_path')) {
                $table->string('std_tkt_file_path', 500)->nullable();
            }
            if (! Schema::hasColumn('visa.tickets_details', 'std_tkt_file_name')) {
                $table->string('std_tkt_file_name', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa.tickets_details', function (Blueprint $table) {
            if (Schema::hasColumn('visa.tickets_details', 'std_tkt_file_name')) {
                $table->dropColumn('std_tkt_file_name');
            }
            if (Schema::hasColumn('visa.tickets_details', 'std_tkt_file_path')) {
                $table->dropColumn('std_tkt_file_path');
            }
        });
    }
};
