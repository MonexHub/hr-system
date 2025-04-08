<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // Optional identifier
            $table->decimal('minimum_salary_required', 12, 2)->default(0);
            $table->decimal('max_amount_cap', 12, 2)->default(0); // e.g. 5000 max loan
            $table->integer('repayment_months')->default(12); // Default repayment period
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
