<?php

use App\Services\RssFeedParser;
use Carbon\Carbon;

it('parses an rss feed', function () {
    $parser = new RssFeedParser();

    $data = $parser->parse(<<<'XML'
<?xml version="1.0"?>
<rss version="2.0">
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
        </item>
    </channel>
</rss>
XML);

    expect($data['title'])->toBe('Example Feed');
    expect($data['items'])->toHaveCount(1);
    expect($data['items'][0]['title'])->toBe('First story');
    expect($data['items'][0]['published_at'])->toBeInstanceOf(Carbon::class);
});

it('parses an atom feed', function () {
    $parser = new RssFeedParser();

    $data = $parser->parse(<<<'XML'
<?xml version="1.0"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Atom Example</title>
    <subtitle>Atom subtitle</subtitle>
    <link href="https://example.com"/>
    <entry>
        <id>tag:example.com,2024:first</id>
        <title>Atom story</title>
        <updated>2024-01-01T10:00:00Z</updated>
        <link href="https://example.com/atom-story"/>
        <summary>Atom summary.</summary>
    </entry>
</feed>
XML);

    expect($data['title'])->toBe('Atom Example');
    expect($data['items'])->toHaveCount(1);
    expect($data['items'][0]['url'])->toBe('https://example.com/atom-story');
});
