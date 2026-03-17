<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('sort_order')->get();
        return view('admin.blog.form', ['post' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'read_time' => 'nullable|integer|min:1',
            'faq' => 'nullable|array',
            'faq.*.question' => 'nullable|string',
            'faq.*.answer' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        $faq = [];
        foreach ($validated['faq'] ?? [] as $item) {
            if (!empty($item['question']) && !empty($item['answer'])) {
                $faq[] = $item;
            }
        }
        $validated['faq'] = !empty($faq) ? $faq : null;

        BlogPost::create($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Post created.');
    }

    public function edit(BlogPost $blog)
    {
        $categories = BlogCategory::orderBy('sort_order')->get();
        return view('admin.blog.form', ['post' => $blog, 'categories' => $categories]);
    }

    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'read_time' => 'nullable|integer|min:1',
            'faq' => 'nullable|array',
        ]);

        if ($validated['status'] === 'published' && !$blog->published_at) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        $faq = [];
        foreach ($validated['faq'] ?? [] as $item) {
            if (!empty($item['question']) && !empty($item['answer'])) {
                $faq[] = $item;
            }
        }
        $validated['faq'] = !empty($faq) ? $faq : null;

        $blog->update($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        return back()->with('success', 'Post deleted.');
    }
}