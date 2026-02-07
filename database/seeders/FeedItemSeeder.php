<?php

namespace Database\Seeders;

use App\Models\Feed;
use App\Models\FeedItem;
use Illuminate\Database\Seeder;

class FeedItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feed = Feed::query()->first() ?? Feed::factory()->create();

        FeedItem::factory()->count(10)->for($feed)->create();
    }
}
