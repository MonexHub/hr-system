<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->string('status')->default('pending');

            // Basic time tracking
            $table->float('total_hours')->default(0);
            $table->float('standard_hours')->default(0);
            $table->float('overtime_hours')->default(0);
            $table->float('early_hours')->default(0);

            // Excel import specific fields
            $table->float('late_minutes')->default(0);
            $table->float('early_out_minutes')->default(0);
            $table->float('absence_hours')->default(0);
            $table->float('normal_overtime_hours')->default(0);
            $table->float('weekend_overtime_hours')->default(0);
            $table->float('holiday_overtime_hours')->default(0);
            $table->float('ot1_hours')->default(0);
            $table->float('ot2_hours')->default(0);
            $table->float('ot3_hours')->default(0);

            // Leave types
            $table->float('annual_leave_hours')->default(0);
            $table->float('sick_leave_hours')->default(0);
            $table->float('casual_leave_hours')->default(0);
            $table->float('maternity_leave_hours')->default(0);
            $table->float('compassionate_leave_hours')->default(0);
            $table->float('business_trip_hours')->default(0);
            $table->float('compensatory_hours')->default(0);
            $table->float('compensatory_leave_hours')->default(0);

            // Additional notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Add unique constraint for employee and date
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
