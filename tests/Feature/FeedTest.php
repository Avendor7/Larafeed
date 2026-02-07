<?php

use App\Jobs\FetchFeedItems;
use App\Models\Feed;
use App\Models\User;
use App\Services\RssFeedParser;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware([
        VerifyCsrfToken::class,
        ValidateCsrfToken::class,
    ]);
});

it('allows a user to add a feed and queues a refresh', function () {
    Bus::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('feeds.store'), [
        'url' => 'https://example.com/rss.xml',
    ]);

    $response->assertRedirect(route('dashboard'));

    $feed = Feed::query()->where('user_id', $user->id)->firstOrFail();

    expect($feed->url)->toBe('https://example.com/rss.xml');
    expect($feed->url_hash)->toBe(hash('sha1', 'https://example.com/rss.xml'));

    Bus::assertDispatched(FetchFeedItems::class);
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
