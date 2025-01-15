<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('preferred_language')->nullable();
            $table->string('nationality')->nullable();
            $table->string('current_job_title')->nullable();
            $table->string('years_of_experience')->nullable();
            $table->enum('availability_status', [
                'immediately',
                'notice_period',
                'employed'
            ])->nullable();
            $table->integer('notice_period_days')->nullable();
            $table->text('professional_summary')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_language',
                'nationality',
                'current_job_title',
                'years_of_experience',
                'availability_status',
                'notice_period_days',
                'professional_summary'
            ]);
        });
    }
};
