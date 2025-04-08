<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // Optional identifier like 'PAYE'
            $table->text('description')->nullable();
            $table->boolean('applies_to_all')->default(false); // TRUE = all employees
            $table->enum('type', ['fixed', 'percentage'])->default('percentage'); // How to calculate
            $table->decimal('value', 12, 2)->default(0); // e.g. 10% or 500
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
