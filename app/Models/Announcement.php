<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'category',
        'title',
        'excerpt',
        'content',
        'featured_image',
        'media_gallery',
        'author',
        'tags',
        'views_count',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'media_gallery' => 'array',
            'tags' => 'array',
            'views_count' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    public function scopeRecent(Builder $query, int $limit = 5): Builder
    {
        return $query->published()->orderBy('published_at', 'desc')->take($limit);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
