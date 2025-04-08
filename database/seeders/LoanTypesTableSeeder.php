<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanType;
use Illuminate\Support\Facades\DB;

class LoanTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('loan_types')->delete();

        LoanType::insert([
            [
                'name' => 'Emergency Loan',
                'code' => 'EMERGENCY',
                'minimum_salary_required' => 500,
                'max_amount_cap' => 2000,
                'repayment_months' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Education Loan',
                'code' => 'EDUCATION',
                'minimum_salary_required' => 1000,
                'max_amount_cap' => 5000,
                'repayment_months' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
