<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
//            RolesAndPermissionsSeeder::class,
            ShieldSeeder::class,
            LeaveTypeSeeder::class,
//            JobPostingSeeder::class,
            OrganizationUnitSeeder::class,
            DepartmentSeeder::class,
//            UpdateHRPermissionsSeeder::class,
            UserEmployeeSeeder::class

        ]);
    }
}
