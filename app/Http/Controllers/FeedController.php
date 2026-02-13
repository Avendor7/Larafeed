<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Jobs\FetchFeedItems;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function manage(Request $request): Response
    {
        $feeds = Feed::query()
            ->whereBelongsTo($request->user())
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

        return Inertia::render('FeedManagement', [
            'feeds' => $feeds,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function show(Feed $feed): Response
    {
        $this->authorize('view', $feed);

        $feed->load([
            'items' => fn ($query) => $query
                ->orderByDesc('published_at')
                ->orderByDesc('created_at'),
        ]);

        return Inertia::render('Feed', [
            'feed' => [
                'id' => $feed->id,
                'title' => $feed->title ?? $feed->url,
                'url' => $feed->url,
                'site_url' => $feed->site_url,
                'description' => $feed->description,
                'last_fetched_at' => $feed->last_fetched_at?->toIso8601String(),
            ],
            'items' => $feed->items->map(fn ($item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'summary' => $item->summary,
                'published_at' => $item->published_at?->toIso8601String(),
            ]),
        ]);
    }

    public function store(StoreFeedRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $feed = Feed::query()->create([
            'user_id' => $request->user()->id,
            'url' => $validated['url'],
            'url_hash' => $validated['url_hash'],
            'title' => $validated['url'],
        ]);

        FetchFeedItems::dispatch($feed);

        return to_route('feeds.manage')->with('status', 'Feed added. Fetching items.');
    }

    public function refresh(Feed $feed): RedirectResponse
    {
        $this->authorize('update', $feed);

        FetchFeedItems::dispatch($feed);

        return back()->with('status', 'Feed refresh queued.');
    }

    public function destroy(Feed $feed): RedirectResponse
    {
        $this->authorize('delete', $feed);

        $feed->delete();

        return back()->with('status', 'Feed removed.');
    }
}
