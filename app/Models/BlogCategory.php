<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'meta_title', 'meta_description', 'sort_order'];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}