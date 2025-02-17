<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Tanzanian major locations
     *
     * @var array
     */
    private array $locations = [
        'Dar es Salaam' => [
            'regions' => ['Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni'],
            'area' => 'Commercial Capital'
        ],
        'Dodoma' => [
            'regions' => ['Dodoma CBD', 'Chamwino', 'Bahi'],
            'area' => 'Administrative Capital'
        ],
        'Arusha' => [
            'regions' => ['Arusha CBD', 'Meru', 'Monduli'],
            'area' => 'Northern Zone'
        ],
        'Mwanza' => [
            'regions' => ['Nyamagana', 'Ilemela', 'Magu'],
            'area' => 'Lake Zone'
        ],
        'Zanzibar' => [
            'regions' => ['Urban West', 'South', 'North'],
            'area' => 'Islands'
        ],
        'Mbeya' => [
            'regions' => ['Mbeya CBD', 'Mbalizi', 'Tukuyu'],
            'area' => 'Southern Highlands'
        ],
        'Tanga' => [
            'regions' => ['Tanga CBD', 'Muheza', 'Pangani'],
            'area' => 'Northern Coast'
        ],
        'Morogoro' => [
            'regions' => ['Morogoro CBD', 'Kilombero', 'Mvomero'],
            'area' => 'Eastern Zone'
        ]
    ];

    public function run(): void
    {
        // Get all organization units of type department
        $organizationUnits = OrganizationUnit::where('unit_type', OrganizationUnit::TYPE_DEPARTMENT)->get();

        foreach ($organizationUnits as $unit) {
            // Get random location
            $mainLocation = array_rand($this->locations);
            $regions = $this->locations[$mainLocation]['regions'];
            $region = $regions[array_rand($regions)];
            $area = $this->locations[$mainLocation]['area'];

            // Format location string
            $location = "{$region}, {$mainLocation} - {$area}";

            Department::create([
                'name' => $unit->name,
                'code' => $unit->code,
                'organization_unit_id' => $unit->id,
                'description' => "Department of {$unit->name}",
                'is_active' => true,
                'phone' => '+255' . rand(600000000, 799999999), // Tanzanian phone format
                'email' => strtolower(str_replace(' ', '.', $unit->name)) . '@monex.co.tz',
                'location' => $location,
                'annual_budget' => $unit->annual_budget ?? rand(100000000, 500000000), // Increased for TZS
                'current_headcount' => $unit->current_headcount ?? 0,
                'max_headcount' => $unit->max_headcount ?? rand(10, 50),
            ]);
        }
    }
}
