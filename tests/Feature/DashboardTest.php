<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
        );
});

test('dashboard shows all stories ordered by publish date', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $olderItem = FeedItem::factory()->for($feed)->create([
        'title' => 'Older item',
        'published_at' => now()->subDay(),
    ]);

    $newerItem = FeedItem::factory()->for($feed)->create([
        'title' => 'Newer item',
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('items', 2)
            ->where('items.0.id', $newerItem->id)
            ->where('items.1.id', $olderItem->id)
        );
});
