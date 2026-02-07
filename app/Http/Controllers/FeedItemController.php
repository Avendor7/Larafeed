<?php

namespace App\Http\Controllers;

use App\Models\FeedItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedItemController extends Controller
{
    public function show(Request $request, FeedItem $feedItem): Response
    {
        $feedItem->load('feed');

        $this->authorize('view', $feedItem);

        return Inertia::render('FeedItem', [
            'item' => [
                'id' => $feedItem->id,
                'title' => $feedItem->title,
                'url' => $feedItem->url,
                'summary' => $feedItem->summary,
                'content' => $feedItem->content,
                'published_at' => $feedItem->published_at?->toIso8601String(),
                'feed' => [
                    'id' => $feedItem->feed->id,
                    'title' => $feedItem->feed->title ?? $feedItem->feed->url,
                ],
            ],
        ]);
    }
}
