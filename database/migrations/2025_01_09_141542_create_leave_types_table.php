<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('min_days_before_request')->default(0);
            $table->integer('max_days_per_request')->nullable();
            $table->integer('max_days_per_year')->nullable();
            $table->boolean('requires_ceo_approval')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('requires_ceo_approval');
            $table->index(['is_active', 'requires_ceo_approval']);
        });

        // Create default leave types
        $defaultTypes = [
            ['name' => 'Annual Leave', 'description' => 'Regular annual leave entitlement', 'is_paid' => true, 'is_active' => true, 'max_days_per_year' => 21],
            ['name' => 'Sick Leave', 'description' => 'Leave for medical reasons', 'is_paid' => true, 'is_active' => true, 'requires_attachment' => true, 'max_days_per_year' => 14],
            ['name' => 'Maternity Leave', 'description' => 'Leave for childbirth and care', 'is_paid' => true, 'is_active' => true, 'max_days_per_year' => 84],
            ['name' => 'Paternity Leave', 'description' => 'Leave for fathers after childbirth', 'is_paid' => true, 'is_active' => true, 'max_days_per_year' => 3],
            ['name' => 'Compassionate Leave', 'description' => 'Leave for bereavement or family emergencies', 'is_paid' => true, 'is_active' => true, 'max_days_per_year' => 5],
            ['name' => 'Unpaid Leave', 'description' => 'Leave without pay', 'is_paid' => false, 'is_active' => true, 'max_days_per_year' => 30],
            ['name' => 'Study Leave', 'description' => 'Leave for examinations and study', 'is_paid' => true, 'is_active' => true, 'requires_attachment' => true, 'max_days_per_year' => 10],
        ];

        foreach ($defaultTypes as $type) {
            \DB::table('leave_types')->insert(array_merge($type, [
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
