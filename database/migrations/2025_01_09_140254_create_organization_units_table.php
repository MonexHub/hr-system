<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organization_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->text('description')->nullable();
            $table->enum('unit_type', ['company', 'division', 'department', 'team', 'unit']);
            $table->integer('level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->decimal('annual_budget', 15, 2)->nullable();
            $table->integer('current_headcount')->default(0);
            $table->integer('max_headcount')->default(0);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('organization_units');
    }
};
