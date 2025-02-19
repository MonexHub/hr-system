<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Simba Group locations based on actual data
     *
     * @var array
     */
    private array $locations = [
        'Dar es Salaam' => [
            'regions' => ['SIMBA HQ', 'Ilala', 'Kinondoni', 'Temeke', 'Ubungo'],
            'area' => 'Commercial Capital'
        ],
        'Mwanza' => [
            'regions' => ['COURIER-MWANZA', 'VIT-MWANZA', 'Nyamagana', 'Ilemela'],
            'area' => 'Lake Zone'
        ],
        'Arusha' => [
            'regions' => ['COURIER-ARUSHA', 'Arusha CBD', 'Meru'],
            'area' => 'Northern Zone'
        ],
        'Mbeya' => [
            'regions' => ['COURIER-MBEYA', 'Mbeya CBD', 'Mbalizi'],
            'area' => 'Southern Highlands'
        ],
        'Dodoma' => [
            'regions' => ['COURIER-DODOMA', 'Dodoma CBD', 'Chamwino'],
            'area' => 'Central Zone'
        ],
        'Zanzibar' => [
            'regions' => ['COURIER-ZANZIBAR', 'VIT-ZANZIBAR', 'Urban West'],
            'area' => 'Islands'
        ],
        'Kigoma' => [
            'regions' => ['COURIER-KIGOMA', 'Kigoma CBD', 'Ujiji'],
            'area' => 'Western Zone'
        ],
        'Morogoro' => [
            'regions' => ['COURIER-MOROGORO', 'Morogoro CBD', 'Kilombero'],
            'area' => 'Eastern Zone'
        ]
    ];

    /**
     * Department specific locations and configurations
     */
    private array $departmentConfigs = [
        'SIMBA COURIER' => [
            'locations' => ['Dar es Salaam', 'Mwanza', 'Arusha', 'Mbeya', 'Dodoma', 'Zanzibar', 'Kigoma', 'Morogoro'],
            'email' => 'courier@simbagroup.co.tz',
            'min_headcount' => 30,
            'max_headcount' => 100,
            'budget_range' => [500000000, 1000000000]
        ],
        'SIMBA VIT' => [
            'locations' => ['Dar es Salaam', 'Mwanza', 'Zanzibar'],
            'email' => 'vit@simbagroup.co.tz',
            'min_headcount' => 20,
            'max_headcount' => 80,
            'budget_range' => [400000000, 800000000]
        ],
        'SIMBA LOGISTIC' => [
            'locations' => ['Dar es Salaam'],
            'email' => 'logistics@simbagroup.co.tz',
            'min_headcount' => 25,
            'max_headcount' => 70,
            'budget_range' => [300000000, 600000000]
        ],
        'PUMP' => [
            'locations' => ['Dar es Salaam'],
            'email' => 'pump@simbagroup.co.tz',
            'min_headcount' => 10,
            'max_headcount' => 40,
            'budget_range' => [200000000, 400000000]
        ],
        'SIMBA FOODS' => [
            'locations' => ['Dar es Salaam'],
            'email' => 'foods@simbagroup.co.tz',
            'min_headcount' => 5,
            'max_headcount' => 30,
            'budget_range' => [100000000, 300000000]
        ],
        'SIMBA MONEY' => [
            'locations' => ['Dar es Salaam'],
            'email' => 'money@simbagroup.co.tz',
            'min_headcount' => 5,
            'max_headcount' => 20,
            'budget_range' => [100000000, 200000000]
        ]
    ];

    public function run(): void
    {
        // Get all organization units of type department
        $organizationUnits = OrganizationUnit::where('unit_type', OrganizationUnit::TYPE_DEPARTMENT)->get();

        foreach ($organizationUnits as $unit) {
            $config = $this->departmentConfigs[$unit->name] ?? null;

            if ($config) {
                // For departments with multiple locations, create a department for each location
                foreach ($config['locations'] as $mainLocation) {
                    $regions = $this->locations[$mainLocation]['regions'];
                    $region = $regions[0]; // Use the first region as default
                    $area = $this->locations[$mainLocation]['area'];

                    // Format location string
                    $location = "{$region}, {$mainLocation} - {$area}";

                    // Generate department code with location
                    $locationCode = strtoupper(substr(str_replace(' ', '', $mainLocation), 0, 3));
                    $departmentCode = $mainLocation === 'Dar es Salaam' ?
                        $unit->code :
                        $unit->code . '-' . $locationCode;

                    Department::create([
                        'name' => $unit->name . ($mainLocation === 'Dar es Salaam' ? '' : " - {$mainLocation}"),
                        'code' => $departmentCode,
                        'organization_unit_id' => $unit->id,
                        'description' => "{$unit->name} department in {$mainLocation}",
                        'is_active' => true,
                        'phone' => '+255' . rand(600000000, 799999999),
                        'email' => $config['email'],
                        'location' => $location,
                        'annual_budget' => rand($config['budget_range'][0], $config['budget_range'][1]),
                        'current_headcount' => 0,
                        'max_headcount' => rand($config['min_headcount'], $config['max_headcount']),
                    ]);
                }
            } else {
                // For support departments (HR, Finance, etc.), create single department at HQ
                Department::create([
                    'name' => $unit->name,
                    'code' => $unit->code,
                    'organization_unit_id' => $unit->id,
                    'description' => "Department of {$unit->name}",
                    'is_active' => true,
                    'phone' => '+255' . rand(600000000, 799999999),
                    'email' => strtolower(str_replace(' ', '.', $unit->name)) . '@simbagroup.co.tz',
                    'location' => 'SIMBA HQ, Dar es Salaam - Commercial Capital',
                    'annual_budget' => rand(100000000, 300000000),
                    'current_headcount' => 0,
                    'max_headcount' => rand(10, 30),
                ]);
            }
        }
    }
}
