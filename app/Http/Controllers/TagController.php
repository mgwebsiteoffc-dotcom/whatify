<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $tags = Tag::where('user_id', $owner->id)
            ->withCount('contacts')
            ->orderBy('name')
            ->get();

        return view('tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $exists = Tag::where('user_id', $owner->id)->where('name', $validated['name'])->exists();
        if ($exists) {
            return back()->with('error', 'Tag with this name already exists.');
        }

        Tag::create([
            'user_id' => $owner->id,
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Tag created.');
    }

    public function update(Request $request, Tag $tag)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($tag->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7',
        ]);

        $tag->update($validated);

        return back()->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($tag->user_id !== $owner->id, 403);

        $tag->contacts()->detach();
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }
}