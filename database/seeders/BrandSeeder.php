<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'NORMANN', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'FERM LIVING', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MUUTO', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HAY', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MENU', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'BLOOMINGVILLE', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VITRA', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'FRITZ HANSEN', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LOUIS POULSEN', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'STRING', 'logo' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('brands')->insert($brands);
    }
}
