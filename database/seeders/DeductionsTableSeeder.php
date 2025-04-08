<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deduction;
use Illuminate\Support\Facades\DB;

class DeductionsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('employee_deductions')->delete();

        Deduction::insert([
            [
                'name' => 'PAYE',
                'code' => 'PAYE',
                'type' => 'percentage',
                'value' => 10,
                'applies_to_all' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NSSF',
                'code' => 'NSSF',
                'type' => 'fixed',
                'value' => 200,
                'applies_to_all' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
