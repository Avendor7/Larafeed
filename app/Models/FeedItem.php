<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedItem extends Model
{
    /** @use HasFactory<\Database\Factories\FeedItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'feed_id',
        'guid',
        'title',
        'url',
        'summary',
        'content',
        'published_at',
        'read_at',
        'bookmarked_at',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'read_at' => 'datetime',
            'bookmarked_at' => 'datetime',
        ];
    }
}
