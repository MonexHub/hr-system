<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('net_salary_min', 12, 2)->nullable();
            $table->decimal('net_salary_max', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add job_title_id to employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('job_title_id')->nullable()->after('reporting_to')
                ->constrained('job_titles')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_title_id');
        });
        Schema::dropIfExists('job_titles');
    }
};
