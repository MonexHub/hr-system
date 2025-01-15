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
            $table->string('code')->unique();
            $table->text('description')->nullable();

            // Leave Allowance Configuration
            $table->integer('max_days')->default(0);
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('is_paid')->default(true);
            $table->integer('min_days_per_request')->default(1);
            $table->integer('max_days_per_request')->nullable();
            $table->integer('advance_notice_days')->default(0);

            // Eligibility Settings
            $table->integer('min_service_months')->default(0);
            $table->boolean('is_gender_specific')->default(false);
            $table->enum('applicable_gender', ['male', 'female', 'all'])->default('all');
            $table->boolean('requires_approval')->default(true);
            $table->boolean('requires_medical_certificate')->default(false);

            // Leave Cycle
            $table->enum('cycle_type', ['annual', 'lifetime', 'custom'])->default('annual');
            $table->date('cycle_start_date')->nullable();
            $table->boolean('can_carry_forward')->default(false);
            $table->integer('max_carry_forward_days')->default(0);

            // System Settings
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('color')->nullable();

            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Create default leave types
        $defaultTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Regular annual leave entitlement',
                'max_days' => 21,
                'is_paid' => true,
                'advance_notice_days' => 7,
                'cycle_type' => 'annual',
                'can_carry_forward' => true,
                'max_carry_forward_days' => 5,
                'color' => '#4CAF50',
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Leave for medical reasons',
                'max_days' => 14,
                'is_paid' => true,
                'requires_medical_certificate' => true,
                'min_days_per_request' => 1,
                'cycle_type' => 'annual',
                'color' => '#F44336',
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Leave for childbirth and care',
                'max_days' => 84,
                'is_paid' => true,
                'is_gender_specific' => true,
                'applicable_gender' => 'female',
                'requires_medical_certificate' => true,
                'cycle_type' => 'lifetime',
                'color' => '#E91E63',
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Leave for fathers after childbirth',
                'max_days' => 3,
                'is_paid' => true,
                'is_gender_specific' => true,
                'applicable_gender' => 'male',
                'cycle_type' => 'lifetime',
                'color' => '#2196F3',
            ],
            [
                'name' => 'Compassionate Leave',
                'code' => 'CL',
                'description' => 'Leave for bereavement or family emergencies',
                'max_days' => 5,
                'is_paid' => true,
                'requires_attachment' => true,
                'cycle_type' => 'annual',
                'color' => '#9C27B0',
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'description' => 'Leave without pay',
                'max_days' => 30,
                'is_paid' => false,
                'advance_notice_days' => 14,
                'min_service_months' => 12,
                'cycle_type' => 'annual',
                'color' => '#607D8B',
            ],
            [
                'name' => 'Study Leave',
                'code' => 'STL',
                'description' => 'Leave for examinations and study',
                'max_days' => 10,
                'is_paid' => true,
                'requires_attachment' => true,
                'min_service_months' => 6,
                'cycle_type' => 'annual',
                'color' => '#FF9800',
            ],
        ];

        foreach ($defaultTypes as $type) {
            \DB::table('leave_types')->insert(array_merge($type, [
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
