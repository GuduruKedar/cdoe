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
        Schema::create('visa.ppr_details', function (Blueprint $table) {
            $table->id('s_no');
            $table->unsignedInteger('prog_id')->nullable();
            $table->string('prog_name', 100);
            $table->string('regname', 255);
            $table->string('regcode', 10);
            $table->string('filename', 255);
            $table->string('filepath', 500);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['prog_name', 'regcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa.ppr_details');
    }
};
