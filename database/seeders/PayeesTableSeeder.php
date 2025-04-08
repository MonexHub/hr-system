<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payee;

class PayeesTableSeeder extends Seeder
{
    public function run(): void
    {
        Payee::truncate();

        Payee::insert([
            [
                'min_amount' => 0,
                'max_amount' => 270000,
                'rate' => 0,
                'fixed_amount' => 0,
            ],
            [
                'min_amount' => 270001,
                'max_amount' => 520000,
                'rate' => 8,
                'fixed_amount' => 0,
            ],
            [
                'min_amount' => 520001,
                'max_amount' => 760000,
                'rate' => 20,
                'fixed_amount' => 20000,
            ],
            [
                'min_amount' => 760001,
                'max_amount' => 1000000,
                'rate' => 25,
                'fixed_amount' => 60000,
            ],
            [
                'min_amount' => 1000001,
                'max_amount' => null,
                'rate' => 30,
                'fixed_amount' => 100000,
            ],
        ]);
    }
}
