<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'body',
        'featured_image', 'meta_title', 'meta_description', 'meta_keywords',
        'schema_markup', 'faq', 'status', 'published_at', 'views', 'read_time',
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'faq' => 'array',
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
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function getUrlAttribute(): string
    {
        return route('website.blog.show', $this->slug);
    }
}