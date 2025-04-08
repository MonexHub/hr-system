<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Monthly Payroll"
            $table->enum('type', ['recurring', 'one_time'])->default('recurring');
            $table->enum('frequency', ['monthly', 'quarterly', 'annually', 'custom'])->default('monthly')->nullable(); // For recurring
            $table->date('execution_date')->nullable(); // When to trigger payroll
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
