<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => '新品',
                'slug' => 'new',
                'color' => '#22C55E', // 綠色
                'icon' => null,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '特價',
                'slug' => 'sale',
                'color' => '#EF4444', // 紅色
                'icon' => null,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '熱銷',
                'slug' => 'hot',
                'color' => '#F97316', // 橘色
                'icon' => null,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('tags')->insert($tags);
    }
}
