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

it('marks feed items as read when viewing them', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();
    $item = FeedItem::factory()->for($feed)->create([
        'read_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('feed-items.show', $item))
        ->assertOk();

    expect($item->refresh()->read_at)->not->toBeNull();
});

it('allows a user to toggle bookmarks', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();
    $item = FeedItem::factory()->for($feed)->create([
        'bookmarked_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('feed-items.bookmark', $item))
        ->assertRedirect();

    expect($item->refresh()->bookmarked_at)->not->toBeNull();

    $this->actingAs($user)
        ->post(route('feed-items.bookmark', $item))
        ->assertRedirect();

    expect($item->refresh()->bookmarked_at)->toBeNull();
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

it('prevents other users from bookmarking a feed item', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $feed = Feed::factory()->for($owner)->create();
    $item = FeedItem::factory()->for($feed)->create();

    $this->actingAs($otherUser)
        ->post(route('feed-items.bookmark', $item))
        ->assertForbidden();
});
