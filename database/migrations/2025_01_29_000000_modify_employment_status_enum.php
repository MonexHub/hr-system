<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // First, modify the column to varchar temporarily
            DB::statement('ALTER TABLE employees MODIFY employment_status VARCHAR(20)');

            // Then create the ENUM with correct values
            DB::statement("ALTER TABLE employees MODIFY employment_status ENUM('ACTIVE', 'PROBATION', 'SUSPENDED', 'TERMINATED', 'RESIGNED') NOT NULL DEFAULT 'ACTIVE'");
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            DB::statement('ALTER TABLE employees MODIFY employment_status VARCHAR(20)');
        });
    }
};
