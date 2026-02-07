<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedRequest;
use App\Jobs\FetchFeedItems;
use App\Models\Feed;
use Illuminate\Http\RedirectResponse;

class FeedController extends Controller
{
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

        return to_route('dashboard')->with('status', 'Feed added. Fetching items.');
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
