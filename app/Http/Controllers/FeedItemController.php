<?php

namespace App\Http\Controllers;

use App\Models\FeedItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedItemController extends Controller
{
    public function show(Request $request, FeedItem $feedItem): Response
    {
        $feedItem->load('feed');

        $this->authorize('view', $feedItem);

        if (! $feedItem->read_at) {
            $feedItem->forceFill([
                'read_at' => now(),
            ])->save();
        }

        return Inertia::render('FeedItem', [
            'item' => [
                'id' => $feedItem->id,
                'title' => $feedItem->title,
                'url' => $feedItem->url,
                'summary' => $feedItem->summary,
                'content' => $feedItem->content,
                'published_at' => $feedItem->published_at?->toIso8601String(),
                'is_bookmarked' => $feedItem->bookmarked_at !== null,
                'feed' => [
                    'id' => $feedItem->feed->id,
                    'title' => $feedItem->feed->title ?? $feedItem->feed->url,
                ],
            ],
        ]);
    }

    public function toggleBookmark(Request $request, FeedItem $feedItem): RedirectResponse
    {
        $this->authorize('bookmark', $feedItem);

        $feedItem->forceFill([
            'bookmarked_at' => $feedItem->bookmarked_at ? null : now(),
        ])->save();

        return back(303);
    }
}
