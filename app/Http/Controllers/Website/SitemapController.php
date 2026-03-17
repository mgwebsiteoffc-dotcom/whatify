<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];

        $staticPages = [
            ['url' => route('website.home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('website.features'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => route('website.pricing'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => route('website.usecases'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => route('website.industries'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => route('website.blog'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => route('website.about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => route('website.contact'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('website.privacy'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['url' => route('website.terms'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['url' => route('website.refund'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = $page + ['lastmod' => now()->toDateString()];
        }

        $useCaseSlugs = ['ecommerce', 'education', 'healthcare', 'real-estate', 'restaurant', 'travel'];
        foreach ($useCaseSlugs as $slug) {
            $urls[] = [
                'url' => route('website.usecases.show', $slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => now()->toDateString(),
            ];
        }

        $posts = BlogPost::published()->orderBy('published_at', 'desc')->get();
        foreach ($posts as $post) {
            $urls[] = [
                'url' => $post->url,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $post->updated_at->toDateString(),
            ];
        }

        return response()->view('website.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}