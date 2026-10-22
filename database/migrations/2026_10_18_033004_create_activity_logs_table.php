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
        if (! Schema::hasTable('hrms.activity_logs')) {
            Schema::create('hrms.activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('user_code', 50)->nullable()->index();
                $table->string('user_name', 255)->nullable();
                $table->unsignedInteger('role_id')->nullable();
                $table->string('role_name', 100)->nullable();
                $table->string('module', 50)->index();
                $table->string('action', 100)->index();
                $table->text('description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device_type', 100)->nullable();
                $table->string('status', 20)->default('success');
                $table->jsonb('payload')->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrms.activity_logs');
    }
};
