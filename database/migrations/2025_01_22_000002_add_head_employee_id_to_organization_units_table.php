<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organization_units', function (Blueprint $table) {
            $table->foreignId('head_employee_id')
                ->nullable()
                ->after('description')
                ->constrained('employees')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('organization_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('head_employee_id');
        });
    }
};
