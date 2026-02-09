<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\FeedItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim((string) $request->string('search'));

        $feedQuery = Feed::query()
            ->whereBelongsTo($user)
            ->withCount('items')
            ->withCount([
                'items as unread_count' => fn ($query) => $query->whereNull('read_at'),
            ])
            ->latest()
            ->get();

        $feeds = $feedQuery
            ->map(fn (Feed $feed): array => [
                'id' => $feed->id,
                'title' => $feed->title ?? $feed->url,
                'url' => $feed->url,
                'site_url' => $feed->site_url,
                'description' => $feed->description,
                'last_fetched_at' => $feed->last_fetched_at?->toIso8601String(),
                'items_count' => $feed->items_count,
                'unread_count' => $feed->unread_count,
            ]);

        $itemsQuery = FeedItem::query()
            ->with('feed')
            ->whereHas('feed', fn ($query) => $query->whereBelongsTo($user))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhereHas('feed', fn ($feedQuery) => $feedQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('url', 'like', "%{$search}%")
                        );
                });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        $items = $itemsQuery
            ->limit(50)
            ->get()
            ->map(fn (FeedItem $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'summary' => $item->summary,
                'published_at' => $item->published_at?->toIso8601String(),
                'is_bookmarked' => $item->bookmarked_at !== null,
                'feed' => [
                    'id' => $item->feed->id,
                    'title' => $item->feed->title ?? $item->feed->url,
                ],
            ]);

        $todayItems = $itemsQuery
            ->whereNotNull('published_at')
            ->where('published_at', '>=', now()->startOfDay())
            ->limit(6)
            ->get()
            ->map(fn (FeedItem $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'summary' => $item->summary,
                'published_at' => $item->published_at?->toIso8601String(),
                'is_bookmarked' => $item->bookmarked_at !== null,
                'feed' => [
                    'id' => $item->feed->id,
                    'title' => $item->feed->title ?? $item->feed->url,
                ],
            ]);

        $bookmarks = FeedItem::query()
            ->with('feed')
            ->whereHas('feed', fn ($query) => $query->whereBelongsTo($user))
            ->whereNotNull('bookmarked_at')
            ->orderByDesc('bookmarked_at')
            ->limit(3)
            ->get()
            ->map(fn (FeedItem $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'published_at' => $item->published_at?->toIso8601String(),
                'feed' => [
                    'id' => $item->feed->id,
                    'title' => $item->feed->title ?? $item->feed->url,
                ],
            ]);

        $itemStatsQuery = FeedItem::query()
            ->whereHas('feed', fn ($query) => $query->whereBelongsTo($user));

        return Inertia::render('Dashboard', [
            'search' => $search,
            'feeds' => $feeds,
            'items' => $items,
            'todayItems' => $todayItems,
            'bookmarks' => $bookmarks,
            'stats' => [
                'total_articles' => (clone $itemStatsQuery)->count(),
                'new_this_week' => (clone $itemStatsQuery)
                    ->where('published_at', '>=', now()->subWeek())
                    ->count(),
                'unread' => (clone $itemStatsQuery)->whereNull('read_at')->count(),
                'unread_priority' => (clone $itemStatsQuery)
                    ->whereNull('read_at')
                    ->where('published_at', '>=', now()->subDay())
                    ->count(),
                'bookmarked' => (clone $itemStatsQuery)->whereNotNull('bookmarked_at')->count(),
                'bookmarked_today' => (clone $itemStatsQuery)
                    ->whereDate('bookmarked_at', now()->toDateString())
                    ->count(),
                'active_feeds' => $feedQuery->count(),
                'failing_feeds' => $feedQuery->whereNull('last_fetched_at')->count(),
            ],
            'status' => $request->session()->get('status'),
        ]);
    }
}
