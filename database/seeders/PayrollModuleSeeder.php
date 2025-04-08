<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayrollModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DeductionsTableSeeder::class,
            BenefitsTableSeeder::class,
            LoanTypesTableSeeder::class,
            PayeesTableSeeder::class,
        ]);
    }
}
