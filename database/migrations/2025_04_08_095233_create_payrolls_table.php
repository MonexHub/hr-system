<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('period'); // e.g. 2025-04-01 (represents the month)
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('total_benefits', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0); // Before tax?
            $table->decimal('net_pay', 12, 2)->default(0); // Final amount paid
            $table->enum('status', ['draft', 'pending', 'approved', 'paid'])->default('draft');
            $table->timestamps();

            $table->unique(['employee_id', 'period']); // Prevent duplicate payrolls per employee per month
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
