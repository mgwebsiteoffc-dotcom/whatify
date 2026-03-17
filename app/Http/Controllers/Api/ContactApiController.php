<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $contacts = Contact::where('user_id', $owner->id)
            ->with('tags:id,name,color')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->tag_id, fn($q, $id) => $q->whereHas('tags', fn($q2) => $q2->where('tags.id', $id)))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($contacts);
    }

    public function show(Request $request, Contact $contact)
    {
        $owner = $request->user()->getBusinessOwner() ?? $request->user();
        if ($contact->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $contact->load('tags');

        return response()->json([
            'contact' => $contact,
            'recent_messages' => $contact->messages()->orderBy('created_at', 'desc')->limit(10)->get(),
        ]);
    }
}