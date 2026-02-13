<?php

use App\Jobs\FetchFeedItems;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\User;
use App\Services\RssFeedParser;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([
        VerifyCsrfToken::class,
        ValidateCsrfToken::class,
    ]);

    $this->withoutVite();
});

it('allows a user to add a feed and queues a refresh', function () {
    Bus::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('feeds.store'), [
        'url' => 'https://example.com/rss.xml',
    ]);

    $response->assertRedirect(route('feeds.manage'));

    $feed = Feed::query()->where('user_id', $user->id)->firstOrFail();

    expect($feed->url)->toBe('https://example.com/rss.xml');
    expect($feed->url_hash)->toBe(hash('sha1', 'https://example.com/rss.xml'));

    Bus::assertDispatched(FetchFeedItems::class);
});

it('shows the new feed on the management page after adding it', function () {
    Bus::fake();

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('feeds.store'), [
        'url' => 'https://example.com/rss.xml',
    ]);

    $this->actingAs($user)
        ->get(route('feeds.manage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('FeedManagement')
            ->has('feeds', 1)
            ->where('feeds.0.url', 'https://example.com/rss.xml')
            ->where('feeds.0.title', 'https://example.com/rss.xml')
        );
});

it('prevents duplicate feeds per user', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('feeds.store'), [
        'url' => $feed->url,
    ]);

    $response->assertSessionHasErrors('url_hash');
    expect(Feed::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('fetches items for a feed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->for($user)->create([
        'url' => 'https://example.com/rss.xml',
        'url_hash' => hash('sha1', 'https://example.com/rss.xml'),
    ]);

    Http::fake([
        'https://example.com/rss.xml' => Http::response(
            <<<'XML'
<?xml version="1.0"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>Example Feed</title>
        <link>https://example.com</link>
        <description>Demo feed</description>
        <item>
            <title>First story</title>
            <link>https://example.com/first</link>
            <guid>first-1</guid>
            <pubDate>Mon, 01 Jan 2024 10:00:00 +0000</pubDate>
            <description>Summary for first story.</description>
            <content:encoded><![CDATA[<p>Full story <strong>content</strong>.</p>]]></content:encoded>
        </item>
    </channel>
</rss>
XML,
            200
        ),
    ]);

    $job = new FetchFeedItems($feed);
    $job->handle(app(RssFeedParser::class));

    $feed->refresh();

    expect($feed->title)->toBe('Example Feed');
    expect($feed->items()->count())->toBe(1);
    $item = $feed->items()->firstOrFail();
    expect($item->content)->toBe('<p>Full story <strong>content</strong>.</p>');
});

it('shows feed stories ordered by publish date to the owning user', function () {
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
        ->get(route('feeds.show', $feed))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Feed')
            ->where('feed.id', $feed->id)
            ->has('items', 2)
            ->where('items.0.id', $newerItem->id)
            ->where('items.1.id', $olderItem->id)
        );
});

it('prevents other users from viewing a feed timeline', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $feed = Feed::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('feeds.show', $feed))
        ->assertForbidden();
});
