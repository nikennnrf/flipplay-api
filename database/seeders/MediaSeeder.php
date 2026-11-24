<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

/**
 * Data katalog yang sama dengan index.php di Modul 3.
 */
class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            // [judul, tipe, genre, tahun, skor, episode, durasi(menit)]
            ['Sousou no Frieren', 'TV', ['Adventure', 'Drama', 'Fantasy'], 2023, 9.30, 28, 24],
            ['Fullmetal Alchemist: Brotherhood', 'TV', ['Action', 'Adventure', 'Drama'], 2009, 9.10, 64, 24],
            ['Steins;Gate', 'TV', ['Drama', 'Sci-Fi', 'Suspense'], 2011, 9.07, 24, 24],
            ['Hunter x Hunter (2011)', 'TV', ['Action', 'Adventure', 'Fantasy'], 2011, 9.00, 148, 24],
            ['Violet Evergarden', 'TV', ['Drama'], 2018, 8.60, 13, 24],
            ['Your Name.', 'Movie', ['Drama', 'Romance', 'Supernatural'], 2016, 8.85, 1, 106],
            ['A Silent Voice', 'Movie', ['Drama', 'Romance'], 2016, 8.93, 1, 130],
            ['Bocchi the Rock!', 'TV', ['Comedy', 'Slice of Life'], 2022, 8.76, 12, 24],
            ['Spy x Family', 'TV', ['Action', 'Comedy'], 2022, 8.60, 12, 24],
            ['Death Note', 'TV', ['Suspense', 'Supernatural'], 2006, 8.62, 37, 24],
            ['Haikyuu!! Karasuno vs Shiratorizawa', 'TV', ['Sports'], 2016, 8.70, 10, 24],
            ['Silver Spoon', 'TV', ['Comedy', 'Slice of Life'], 2013, 8.20, 11, 24],
        ];

        foreach ($catalog as [$title, $type, $genres, $year, $score, $episodes, $duration]) {
            Media::create(compact('title', 'type', 'genres', 'year', 'score', 'episodes', 'duration'));
        }
    }
}
