<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard stats and bookmarks reflect user data', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create([
        'last_fetched_at' => now(),
    ]);

    $priorityItem = FeedItem::factory()->for($feed)->create([
        'published_at' => now()->startOfDay()->addHour(),
        'read_at' => null,
    ]);

    $unreadItem = FeedItem::factory()->for($feed)->create([
        'published_at' => now()->subDay(),
        'read_at' => null,
    ]);

    $bookmarkedItem = FeedItem::factory()->for($feed)->create([
        'published_at' => now()->subHours(4),
        'read_at' => now(),
        'bookmarked_at' => now(),
    ]);

    FeedItem::factory()->for($feed)->create([
        'published_at' => now()->subDays(10),
        'read_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('filter', 'today')
            ->where('stats.total_articles', 4)
            ->where('stats.new_this_week', 3)
            ->where('stats.unread', 2)
            ->where('stats.unread_priority', 2)
            ->where('stats.bookmarked', 1)
            ->where('stats.bookmarked_today', 1)
            ->where('stats.active_feeds', 1)
            ->where('stats.failing_feeds', 0)
            ->where('bookmarks.0.id', $bookmarkedItem->id)
            ->where('items.0.id', $priorityItem->id)
        );
});

test('dashboard filters bookmarks', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $bookmarkedItem = FeedItem::factory()->for($feed)->create([
        'bookmarked_at' => now(),
    ]);

    FeedItem::factory()->for($feed)->create([
        'bookmarked_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard', ['filter' => 'bookmarks']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('filter', 'bookmarks')
            ->where('items.0.id', $bookmarkedItem->id)
        );
});
