<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\WhatsappApiService;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function __construct(
        protected WhatsappApiService $whatsappApi
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        $templates = MessageTemplate::where('user_id', $user->id)
            ->with('whatsappAccount')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->account_id, fn($q, $id) => $q->where('whatsapp_account_id', $id))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $accounts = $user->whatsappAccounts()->where('status', 'connected')->get();

        return view('whatsapp.templates.index', compact('templates', 'accounts'));
    }

    public function create()
    {
        $accounts = auth()->user()->whatsappAccounts()
            ->where('status', 'connected')->get();

        if ($accounts->isEmpty()) {
            return redirect()->route('whatsapp.accounts.index')
                ->with('error', 'Please connect a WhatsApp account first.');
        }

        return view('whatsapp.templates.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id',
            'name' => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'category' => 'required|in:marketing,utility,authentication',
            'language' => 'required|string|max:10',
            'header_type' => 'nullable|in:none,text,image,video,document',
            'header_text' => 'nullable|string|max:60',
            'header_media_url' => 'nullable|url',
            'body' => 'required|string|max:1024',
            'footer' => 'nullable|string|max:60',
            'buttons' => 'nullable|array|max:3',
            'buttons.*.type' => 'required_with:buttons|in:QUICK_REPLY,URL,PHONE_NUMBER',
            'buttons.*.text' => 'required_with:buttons|string|max:25',
            'buttons.*.url' => 'nullable|url',
            'buttons.*.phone_number' => 'nullable|string',
            'sample_body_vars' => 'nullable|array',
            'sample_header_vars' => 'nullable|array',
        ]);

        $account = WhatsappAccount::findOrFail($validated['whatsapp_account_id']);

        // Verify account belongs to user
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        // Build Meta template payload
        $components = [];

        // Header component
        if ($validated['header_type'] && $validated['header_type'] !== 'none') {
            $header = ['type' => 'HEADER'];

            if ($validated['header_type'] === 'text') {
                $header['format'] = 'TEXT';
                $header['text'] = $validated['header_text'];

                if (!empty($validated['sample_header_vars'])) {
                    $header['example'] = [
                        'header_text' => $validated['sample_header_vars'],
                    ];
                }
            } else {
                $header['format'] = strtoupper($validated['header_type']);
                if ($validated['header_media_url']) {
                    $header['example'] = [
                        'header_handle' => [$validated['header_media_url']],
                    ];
                }
            }

            $components[] = $header;
        }

        // Body component
        $bodyComponent = [
            'type' => 'BODY',
            'text' => $validated['body'],
        ];

        if (!empty($validated['sample_body_vars'])) {
            $bodyComponent['example'] = [
                'body_text' => [$validated['sample_body_vars']],
            ];
        }

        $components[] = $bodyComponent;

        // Footer component
        if (!empty($validated['footer'])) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $validated['footer'],
            ];
        }

        // Buttons component
        if (!empty($validated['buttons'])) {
            $buttonsList = [];
            foreach ($validated['buttons'] as $btn) {
                $buttonData = [
                    'type' => $btn['type'],
                    'text' => $btn['text'],
                ];

                if ($btn['type'] === 'URL' && !empty($btn['url'])) {
                    $buttonData['url'] = $btn['url'];
                }
                if ($btn['type'] === 'PHONE_NUMBER' && !empty($btn['phone_number'])) {
                    $buttonData['phone_number'] = $btn['phone_number'];
                }

                $buttonsList[] = $buttonData;
            }

            $components[] = [
                'type' => 'BUTTONS',
                'buttons' => $buttonsList,
            ];
        }

        // Submit to Meta
        $metaPayload = [
            'name' => $validated['name'],
            'category' => strtoupper($validated['category']),
            'language' => $validated['language'],
            'components' => $components,
        ];

        $result = $this->whatsappApi->createTemplate($account, $metaPayload);

        // Save to database
        $template = MessageTemplate::create([
            'user_id' => auth()->id(),
            'whatsapp_account_id' => $account->id,
            'name' => $validated['name'],
            'template_id_meta' => $result['data']['id'] ?? null,
            'category' => $validated['category'],
            'language' => $validated['language'],
            'status' => $result['success'] ? 'pending' : 'rejected',
            'header' => $validated['header_type'] !== 'none' ? [
                'type' => $validated['header_type'],
                'text' => $validated['header_text'] ?? null,
                'media_url' => $validated['header_media_url'] ?? null,
            ] : null,
            'body' => $validated['body'],
            'footer' => $validated['footer'] ?? null,
            'buttons' => $validated['buttons'] ?? null,
            'variables' => $validated['sample_body_vars'] ?? null,
            'rejection_reason' => !$result['success'] ? ($result['data']['error']['message'] ?? 'Submission failed') : null,
        ]);

        if ($result['success']) {
            return redirect()->route('whatsapp.templates.index')
                ->with('success', "Template '{$validated['name']}' submitted for approval.");
        }

        return redirect()->route('whatsapp.templates.index')
            ->with('error', 'Template submission failed: ' . ($result['data']['error']['message'] ?? 'Unknown error'));
    }

    public function show(MessageTemplate $template)
    {
        abort_if($template->user_id !== auth()->id(), 403);

        return view('whatsapp.templates.show', compact('template'));
    }

    public function destroy(MessageTemplate $template)
    {
        abort_if($template->user_id !== auth()->id(), 403);

        $account = $template->whatsappAccount;

        // Delete from Meta
        if ($account && $template->name) {
            $this->whatsappApi->deleteTemplate($account, $template->name);
        }

        $template->delete();

        return redirect()->route('whatsapp.templates.index')
            ->with('success', 'Template deleted.');
    }
}