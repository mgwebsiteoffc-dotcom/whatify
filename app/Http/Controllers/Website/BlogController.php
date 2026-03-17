<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::published()
            ->with(['category', 'author:id,name'])
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%")->orWhere('body', 'like', "%{$s}%"))
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $categories = BlogCategory::withCount(['posts' => fn($q) => $q->published()])->orderBy('sort_order')->get();
        $featured = BlogPost::published()->orderBy('views', 'desc')->limit(3)->get();

        return view('website.blog.index', compact('posts', 'categories', 'featured'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)->published()->with(['category', 'author:id,name'])->firstOrFail();
        $post->increment('views');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('website.blog.show', compact('post', 'related'));
    }

    public function category(string $category)
    {
        $cat = BlogCategory::where('slug', $category)->firstOrFail();
        $posts = BlogPost::published()->where('category_id', $cat->id)->with('author:id,name')->orderBy('published_at', 'desc')->paginate(12);
        $categories = BlogCategory::withCount(['posts' => fn($q) => $q->published()])->orderBy('sort_order')->get();

        return view('website.blog.index', compact('posts', 'categories', 'cat'));
    }
}