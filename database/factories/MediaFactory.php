<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $type = fake()->randomElement(Media::TYPES);

        return [
            'title' => fake()->unique()->words(3, true),
            'type' => $type,
            'genres' => fake()->randomElements(['Action', 'Adventure', 'Comedy', 'Drama', 'Romance', 'Fantasy'], 2),
            'year' => fake()->numberBetween(1995, 2026),
            'score' => fake()->randomFloat(2, 6, 9.5),
            'episodes' => $type === 'Movie' ? 1 : fake()->numberBetween(12, 64),
            'duration' => $type === 'Movie' ? fake()->numberBetween(90, 150) : 24,
            'synopsis' => fake()->sentence(12),
        ];
    }
}
