<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_sw')->nullable(); // Swahili name
            $table->text('description')->nullable();
            $table->text('description_sw')->nullable(); // Swahili description
            $table->date('date');
            $table->boolean('is_recurring')->default(true);
            $table->enum('type', [
                'public', // National holidays
                'religious', // Religious holidays
                'company' // Company-specific holidays
            ])->default('public');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('send_notification')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
