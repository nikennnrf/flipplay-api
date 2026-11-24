<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan dengan: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            MediaSeeder::class,   // media dulu, karena koleksi user butuh ID media
            UserSeeder::class,
        ]);
    }
}
