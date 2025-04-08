<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Benefit;
use Illuminate\Support\Facades\DB;

class BenefitsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('benefits')->delete();

        Benefit::insert([
            [
                'name' => 'Housing',
                'code' => 'HOUSE',
                'type' => 'fixed',
                'value' => 500,
                'applies_to_all' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Transport',
                'code' => 'TRANSPORT',
                'type' => 'fixed',
                'value' => 300,
                'applies_to_all' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
