<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // Optional identifier like 'HOUSING'
            $table->text('description')->nullable();
            $table->boolean('applies_to_all')->default(false);
            $table->enum('type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('value', 12, 2)->default(0); // e.g. 500 or 10%
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
};
