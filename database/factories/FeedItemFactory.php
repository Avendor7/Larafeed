<?php

namespace Database\Factories;

use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedItem>
 */
class FeedItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feed_id' => Feed::factory(),
            'guid' => (string) Str::uuid(),
            'title' => $this->faker->sentence(6),
            'url' => $this->faker->url(),
            'summary' => $this->faker->paragraph(2),
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
