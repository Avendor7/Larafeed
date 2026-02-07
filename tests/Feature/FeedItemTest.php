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

it('shows a feed item to the owning user', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();
    $item = FeedItem::factory()->for($feed)->create([
        'title' => 'Full story',
        'content' => '<p>Full text.</p>',
        'summary' => 'Full text.',
    ]);

    $this->actingAs($user)
        ->get(route('feed-items.show', $item))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('FeedItem')
            ->where('item.id', $item->id)
            ->where('item.title', 'Full story')
            ->where('item.content', '<p>Full text.</p>')
            ->where('item.feed.id', $feed->id)
        );
});

it('prevents other users from viewing a feed item', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $feed = Feed::factory()->for($owner)->create();
    $item = FeedItem::factory()->for($feed)->create();

    $this->actingAs($otherUser)
        ->get(route('feed-items.show', $item))
        ->assertForbidden();
});
