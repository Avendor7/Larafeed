<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feed>
 */
class FeedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url = $this->faker->url();

        return [
            'user_id' => User::factory(),
            'url' => $url,
            'url_hash' => hash('sha1', $url),
            'title' => $this->faker->sentence(4),
            'site_url' => $this->faker->url(),
            'description' => $this->faker->sentence(12),
            'last_fetched_at' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
        ];
    }
}
