<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('benefit_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['fixed', 'percentage'])->nullable(); // Override type
            $table->decimal('value', 12, 2)->nullable(); // Override value
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['employee_id', 'benefit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_benefits');
    }
};
