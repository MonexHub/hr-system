<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get all organization units of type department
        $organizationUnits = OrganizationUnit::where('unit_type', OrganizationUnit::TYPE_DEPARTMENT)->get();

        foreach ($organizationUnits as $unit) {
            Department::create([
                'name' => $unit->name,
                'code' => $unit->code,
                'organization_unit_id' => $unit->id,
                'description' => "Department of {$unit->name}",
                'is_active' => true,
                'phone' => '+1' . rand(2000000000, 9999999999),
                'email' => strtolower(str_replace(' ', '.', $unit->name)) . '@simbagrp.co.tz',
                'location' => ['New York', 'London', 'Singapore', 'Tokyo', 'Dubai'][rand(0, 4)],
                'annual_budget' => $unit->annual_budget ?? rand(100000, 500000),
                'current_headcount' => $unit->current_headcount ?? 0,
                'max_headcount' => $unit->max_headcount ?? rand(10, 50),
            ]);
        }
    }
}
