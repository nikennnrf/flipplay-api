<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ega = User::create([
            'name' => 'Ega',
            'email' => 'ega@flipplay.test',
            'plan' => 'Free',
            'password' => 'password',
        ]);

        $dina = User::create([
            'name' => 'Dina',
            'email' => 'dina@flipplay.test',
            'plan' => 'Premium',
            'password' => 'password',
        ]);

        // Koleksi awal (sama seperti simulasi Modul 3)
        $byTitle = fn (string $title) => Media::where('title', $title)->value('id');

        $ega->media()->attach([
            $byTitle('Sousou no Frieren') => ['is_watched' => false],
            $byTitle('Your Name.') => ['is_watched' => true],
            $byTitle('Bocchi the Rock!') => ['is_watched' => true],
        ]);

        $dina->media()->attach([
            $byTitle('Your Name.') => ['is_watched' => false],
            $byTitle('Death Note') => ['is_watched' => true],
        ]);
    }
}
