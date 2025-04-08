<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payees', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 12, 2); // Income range start
            $table->decimal('max_amount', 12, 2)->nullable(); // Null = no upper cap
            $table->decimal('rate', 5, 2); // % tax rate for this band
            $table->decimal('fixed_amount', 12, 2)->default(0); // Add-on base tax
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payees');
    }
};
