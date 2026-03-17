<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContactSubmission;
use App\Models\Plan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $posts = BlogPost::published()->orderBy('published_at', 'desc')->limit(3)->get();
        return view('website.home', compact('plans', 'posts'));
    }

    public function features()
    {
        return view('website.features');
    }

    public function pricing()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('website.pricing', compact('plans'));
    }

    public function about()
    {
        return view('website.about');
    }

    public function contact()
    {
        return view('website.contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactSubmission::create($validated);

        return back()->with('success', 'Thank you! We will get back to you within 24 hours.');
    }

    public function privacy() { return view('website.legal.privacy'); }
    public function terms() { return view('website.legal.terms'); }
    public function refund() { return view('website.legal.refund'); }
}