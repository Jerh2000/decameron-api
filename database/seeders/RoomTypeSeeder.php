<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Estándar', 'Junior', 'Suite'];

        foreach ($types as $type) {
            DB::table('room_types')->insertOrIgnore([
                'name'       => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
