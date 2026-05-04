<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $accommodations = ['Sencilla', 'Doble', 'Triple', 'Cuádruple'];

        foreach ($accommodations as $accommodation) {
            DB::table('accommodations')->insertOrIgnore([
                'name'       => $accommodation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
