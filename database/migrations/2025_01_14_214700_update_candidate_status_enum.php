<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->enum('status', [
                'applied',
                'screening',
                'shortlisted',
                'interview',
                'offer',
                'hired',
                'rejected',
                'withdrawn'
            ])->default('applied')->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('status')->nullable();
        });
    }
};
