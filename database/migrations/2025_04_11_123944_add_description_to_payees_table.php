<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            // Adding a new column 'description' to the 'payees' table

            $table->text('description')->nullable()->after('fixed_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
