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

        $feeds = Feed::query()
            ->whereBelongsTo($user)
            ->withCount('items')
            ->latest()
            ->get()
            ->map(fn (Feed $feed): array => [
                'id' => $feed->id,
                'title' => $feed->title ?? $feed->url,
                'url' => $feed->url,
                'site_url' => $feed->site_url,
                'description' => $feed->description,
                'last_fetched_at' => $feed->last_fetched_at?->toIso8601String(),
                'items_count' => $feed->items_count,
            ]);

        $items = FeedItem::query()
            ->with('feed')
            ->whereHas('feed', fn ($query) => $query->whereBelongsTo($user))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (FeedItem $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'summary' => $item->summary,
                'published_at' => $item->published_at?->toIso8601String(),
                'feed' => [
                    'id' => $item->feed->id,
                    'title' => $item->feed->title ?? $item->feed->url,
                ],
            ]);

        return Inertia::render('Dashboard', [
            'feeds' => $feeds,
            'items' => $items,
            'status' => $request->session()->get('status'),
        ]);
    }
}
