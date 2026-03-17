<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Tag;
use App\Exports\ContactsExport;
use App\Imports\ContactsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $contacts = Contact::where('user_id', $owner->id)
            ->with('tags')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->tag, function ($q, $tagId) {
                $q->whereHas('tags', fn($q2) => $q2->where('tags.id', $tagId));
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->source, fn($q, $s) => $q->where('source', $s))
            ->when($request->sort, function ($q, $sort) {
                return match ($sort) {
                    'name_asc' => $q->orderBy('name', 'asc'),
                    'name_desc' => $q->orderBy('name', 'desc'),
                    'recent_message' => $q->orderBy('last_message_at', 'desc'),
                    'oldest' => $q->orderBy('created_at', 'asc'),
                    default => $q->orderBy('created_at', 'desc'),
                };
            }, fn($q) => $q->orderBy('created_at', 'desc'))
            ->paginate(25)
            ->withQueryString();

        $tags = Tag::where('user_id', $owner->id)->orderBy('name')->get();

        $stats = [
            'total' => Contact::where('user_id', $owner->id)->count(),
            'active' => Contact::where('user_id', $owner->id)->where('status', 'active')->count(),
            'opted_out' => Contact::where('user_id', $owner->id)->where('status', 'opted_out')->count(),
            'blocked' => Contact::where('user_id', $owner->id)->where('status', 'blocked')->count(),
        ];

        return view('contacts.index', compact('contacts', 'tags', 'stats'));
    }

    public function create()
    {
        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if (!$owner->canUseFeature('contacts_limit')) {
            return back()->with('error', 'Contact limit reached. Please upgrade your plan.');
        }

        $tags = Tag::where('user_id', $owner->id)->orderBy('name')->get();
        return view('contacts.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'country_code' => 'required|string|max:5',
            'source' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'custom_attributes' => 'nullable|array',
            'custom_attributes.*.key' => 'required_with:custom_attributes|string',
            'custom_attributes.*.value' => 'required_with:custom_attributes|string',
        ]);

        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if (!$owner->canUseFeature('contacts_limit')) {
            return back()->with('error', 'Contact limit reached. Please upgrade.');
        }

        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        // Check duplicate
        $exists = Contact::where('user_id', $owner->id)->where('phone', $phone)->exists();
        if ($exists) {
            return back()->with('error', 'Contact with this phone number already exists.')
                ->withInput();
        }

        // Format custom attributes
        $customAttrs = null;
        if (!empty($validated['custom_attributes'])) {
            $customAttrs = [];
            foreach ($validated['custom_attributes'] as $attr) {
                if (!empty($attr['key']) && !empty($attr['value'])) {
                    $customAttrs[$attr['key']] = $attr['value'];
                }
            }
        }

        $contact = Contact::create([
            'user_id' => $owner->id,
            'phone' => $phone,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'country_code' => $validated['country_code'] ?? '91',
            'source' => $validated['source'] ?? 'manual',
            'status' => 'active',
            'custom_attributes' => $customAttrs,
            'opted_in_at' => now(),
        ]);

        // Attach tags
        if (!empty($validated['tags'])) {
            $contact->tags()->sync($validated['tags']);
        }

        \App\Services\ActivityLogger::log('contact_created', 'Contact', $contact->id);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'contact' => $contact->load('tags')]);
        }

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully.');
    }

    public function show(Contact $contact)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($contact->user_id !== $owner->id, 403);

        $contact->load('tags');

        // Recent messages
        $messages = $contact->messages()
            ->with('conversation')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Conversations
        $conversations = $contact->conversations()
            ->with('assignedAgent:id,name')
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Campaigns received
        $campaigns = \App\Models\CampaignContact::where('contact_id', $contact->id)
            ->with('campaign:id,name,status,created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Automation logs
        $automationLogs = \App\Models\AutomationLog::where('contact_id', $contact->id)
            ->with('automation:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $tags = Tag::where('user_id', $owner->id)->orderBy('name')->get();

        return view('contacts.show', compact(
            'contact', 'messages', 'conversations', 'campaigns', 'automationLogs', 'tags'
        ));
    }

    public function edit(Contact $contact)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($contact->user_id !== $owner->id, 403);

        $tags = Tag::where('user_id', $owner->id)->orderBy('name')->get();
        return view('contacts.edit', compact('contact', 'tags'));
    }

    public function update(Request $request, Contact $contact)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($contact->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'country_code' => 'required|string|max:5',
            'status' => 'required|in:active,inactive,blocked,opted_out',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'custom_attributes' => 'nullable|array',
            'custom_attributes.*.key' => 'nullable|string',
            'custom_attributes.*.value' => 'nullable|string',
        ]);

        $customAttrs = null;
        if (!empty($validated['custom_attributes'])) {
            $customAttrs = [];
            foreach ($validated['custom_attributes'] as $attr) {
                if (!empty($attr['key']) && !empty($attr['value'])) {
                    $customAttrs[$attr['key']] = $attr['value'];
                }
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'country_code' => $validated['country_code'],
            'status' => $validated['status'],
            'custom_attributes' => $customAttrs,
        ];

        if ($validated['status'] === 'opted_out' && $contact->status !== 'opted_out') {
            $updateData['opted_out_at'] = now();
        }

        $contact->update($updateData);
        $contact->tags()->sync($validated['tags'] ?? []);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'contact' => $contact->fresh()->load('tags')]);
        }

        return redirect()->route('contacts.show', $contact)->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($contact->user_id !== $owner->id, 403);

        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted.');
    }

    // ──── Bulk Actions ────
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,add_tag,remove_tag,block,activate,opt_out',
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
            'tag_id' => 'required_if:action,add_tag,remove_tag|nullable|exists:tags,id',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        $contacts = Contact::where('user_id', $owner->id)
            ->whereIn('id', $validated['contact_ids'])
            ->get();

        $count = $contacts->count();

        switch ($validated['action']) {
            case 'delete':
                Contact::whereIn('id', $contacts->pluck('id'))->delete();
                break;
            case 'add_tag':
                foreach ($contacts as $contact) {
                    $contact->tags()->syncWithoutDetaching([$validated['tag_id']]);
                }
                break;
            case 'remove_tag':
                foreach ($contacts as $contact) {
                    $contact->tags()->detach($validated['tag_id']);
                }
                break;
            case 'block':
                Contact::whereIn('id', $contacts->pluck('id'))->update(['status' => 'blocked']);
                break;
            case 'activate':
                Contact::whereIn('id', $contacts->pluck('id'))->update(['status' => 'active']);
                break;
            case 'opt_out':
                Contact::whereIn('id', $contacts->pluck('id'))->update([
                    'status' => 'opted_out',
                    'opted_out_at' => now(),
                ]);
                break;
        }

        return back()->with('success', "Bulk action applied to {$count} contacts.");
    }

    // ──── Import ────
    public function importForm()
    {
        return view('contacts.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        try {
            $import = new ContactsImport($owner->id, $request->tag_ids ?? []);
            Excel::import($import, $request->file('file'));

            $imported = $import->getRowCount();
            $skipped = $import->getSkippedCount();

            return redirect()->route('contacts.index')
                ->with('success', "Imported {$imported} contacts. {$skipped} skipped (duplicates/invalid).");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ──── Export ────
    public function export(Request $request)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $filename = 'contacts_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new ContactsExport($owner->id, $request->tag, $request->status),
            $filename
        );
    }
}