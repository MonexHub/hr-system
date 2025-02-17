<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->onDelete('cascade');

            $table->date('date');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();

            $table->enum('status', [
                'pending',
                'present',
                'absent',
                'late',
                'half_day',
                'overtime'
            ])->default('pending');

            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('standard_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            // Ensure unique attendance per employee per day
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
