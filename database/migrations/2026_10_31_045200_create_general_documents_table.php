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
        Schema::create('general_documents', function (Blueprint $table) {
            $table->id();
            $table->string('program', 50)->default('MBA'); // MBA, BBA, COMMON
            $table->string('sub_category', 100)->default('Regulations'); // Regulations, Syllabus, Exam Guidelines, etc.
            $table->string('title', 255);
            $table->string('regulation_code', 50)->nullable(); // e.g. R22, R21, R24
            $table->text('description')->nullable();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('file_type', 50)->default('pdf');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('uploaded_by', 100)->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['program', 'sub_category']);
            $table->index('status');
        });

        // Grant GENE0001 (140) to all active roles in master.menu_access_roles
        try {
            $roles = DB::table('master.roles')->where('role_status', 1)->pluck('role_id');
            foreach ($roles as $roleId) {
                $exists = DB::table('master.menu_access_roles')
                    ->where('menu_code', 'GENE0001')
                    ->where('role_id', $roleId)
                    ->exists();

                if (! $exists) {
                    DB::table('master.menu_access_roles')->insert([
                        'menu_code' => 'GENE0001',
                        'menu_id' => 140,
                        'role_id' => $roleId,
                        'campus_id' => '1',
                        'morder' => 99,
                        'mposition' => 1,
                        'log_userid' => '02598',
                        'log_ipaddress' => '127.0.0.1',
                        'log_timestamp' => now(),
                    ]);
                }
            }
        } catch (Throwable $e) {
            // Ignore if master.menu_access_roles does not exist or has duplicate
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_documents');
    }
};
