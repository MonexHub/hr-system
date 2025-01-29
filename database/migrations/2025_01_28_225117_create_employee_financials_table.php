<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Bank Details
            $table->string('bank_name');
            $table->string('account_number')->unique();
            $table->string('branch_name');

            // Health Insurance
            $table->string('insurance_provider');
            $table->string('insurance_number')->unique();
            $table->date('insurance_expiry_date');

            // NSSF Details
            $table->string('nssf_number')->unique();
            $table->date('nssf_registration_date');

            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_financials');
    }
};
