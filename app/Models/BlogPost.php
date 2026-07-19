<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt',
        'content', 'featured_image', 'is_published', 'published_at',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeByCategory($query, $slug)
    {
        return $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
