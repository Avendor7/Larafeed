<?php

namespace Database\Seeders;

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create();

        Feed::factory()
            ->count(3)
            ->for($user)
            ->has(FeedItem::factory()->count(8), 'items')
            ->create();
    }
}
