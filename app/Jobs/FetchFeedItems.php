<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Models\FeedItem;
use App\Services\RssFeedParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchFeedItems implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Feed $feed) {}

    /**
     * Execute the job.
     */
    public function handle(RssFeedParser $parser): void
    {
        try {
            $response = Http::timeout(10)
                ->accept('application/rss+xml, application/atom+xml, application/xml, text/xml')
                ->withHeaders([
                    'User-Agent' => 'Larafeed RSS Reader/1.0 (+https://example.com)',
                ])
                ->retry(2, 100)
                ->get($this->feed->url)
                ->throw();
        } catch (RequestException $exception) {
            Log::warning('Failed to fetch RSS feed', [
                'feed_id' => $this->feed->id,
                'url' => $this->feed->url,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $parsed = $parser->parse($response->body());

        DB::transaction(function () use ($parsed): void {
            $this->feed->fill([
                'title' => $parsed['title'] ?? $this->feed->title,
                'site_url' => $parsed['site_url'] ?? $this->feed->site_url,
                'description' => $parsed['description'] ?? $this->feed->description,
                'last_fetched_at' => now(),
            ])->save();

            $rows = collect($parsed['items'])
                ->map(fn (array $item): array => $this->normalizeItem($item))
                ->filter(fn (array $item): bool => $item['guid'] !== null)
                ->values()
                ->all();

            if ($rows !== []) {
                FeedItem::query()->upsert(
                    $rows,
                    ['feed_id', 'guid'],
                    ['title', 'url', 'summary', 'content', 'published_at', 'updated_at']
                );
            }
        });
    }

    /**
     * @param array{
     *     guid: string|null,
     *     title: string|null,
     *     url: string|null,
     *     summary: string|null,
     *     content: string|null,
     *     published_at: Carbon|null
     * } $item
     * @return array{
     *     feed_id: int,
     *     guid: string|null,
     *     title: string|null,
     *     url: string|null,
     *     summary: string|null,
     *     content: string|null,
     *     published_at: Carbon|null,
     *     created_at: Carbon,
     *     updated_at: Carbon
     * }
     */
    private function normalizeItem(array $item): array
    {
        $guid = $item['guid'] ?? null;

        if ($guid === null) {
            $seed = ($item['url'] ?? '').
                ($item['title'] ?? '').
                (($item['published_at'] ?? now())->toAtomString());

            $guid = Str::limit(hash('sha1', $seed), 191, '');
        }

        $timestamp = now();

        return [
            'feed_id' => $this->feed->id,
            'guid' => $guid,
            'title' => $item['title'],
            'url' => $item['url'],
            'summary' => $this->cleanSummary($item['summary']),
            'content' => $this->cleanContent($item['content']),
            'published_at' => $item['published_at'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private function cleanSummary(?string $summary): ?string
    {
        if ($summary === null) {
            return null;
        }

        $cleaned = trim(strip_tags($summary));

        return $cleaned === '' ? null : $cleaned;
    }

    private function cleanContent(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        $cleaned = trim($content);

        return $cleaned === '' ? null : $cleaned;
    }
}
