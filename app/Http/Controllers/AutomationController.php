<?php

namespace App\Http\Controllers;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\AutomationStep;
use App\Models\Tag;
use App\Models\WhatsappAccount;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AutomationController extends Controller
{
    public function index(Request $request)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $automations = Automation::where('user_id', $owner->id)
            ->withCount('steps')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->trigger, fn($q, $t) => $q->where('trigger_type', $t))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Automation::where('user_id', $owner->id)->count(),
            'active' => Automation::where('user_id', $owner->id)->where('status', 'active')->count(),
            'draft' => Automation::where('user_id', $owner->id)->where('status', 'draft')->count(),
            'total_executions' => Automation::where('user_id', $owner->id)->sum('execution_count'),
        ];

        return view('automations.index', compact('automations', 'stats'));
    }

    public function create()
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        if (!$owner->canUseFeature('automation_flows')) {
            return back()->with('error', 'Automation limit reached. Please upgrade your plan.');
        }

        return view('automations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'required|string',
            'whatsapp_account_id' => 'nullable|exists:whatsapp_accounts,id',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        if (!$owner->canUseFeature('automation_flows')) {
            return back()->with('error', 'Automation limit reached.');
        }

        $automation = Automation::create([
            'user_id' => $owner->id,
            'whatsapp_account_id' => $validated['whatsapp_account_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'trigger_type' => $validated['trigger_type'],
            'status' => 'draft',
        ]);

        return redirect()->route('automations.builder', $automation)
            ->with('success', 'Automation created. Now build your flow.');
    }

    public function show(Automation $automation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($automation->user_id !== $owner->id, 403);

        $automation->load('steps');

        $recentLogs = AutomationLog::where('automation_id', $automation->id)
            ->with('contact:id,name,phone')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $logStats = [
            'total' => AutomationLog::where('automation_id', $automation->id)->count(),
            'completed' => AutomationLog::where('automation_id', $automation->id)->where('status', 'completed')->count(),
            'failed' => AutomationLog::where('automation_id', $automation->id)->where('status', 'failed')->count(),
            'running' => AutomationLog::where('automation_id', $automation->id)->where('status', 'running')->count(),
        ];

        return view('automations.show', compact('automation', 'recentLogs', 'logStats'));
    }

    public function builder(Automation $automation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($automation->user_id !== $owner->id, 403);

        $automation->load('steps');
        $tags = Tag::where('user_id', $owner->id)->get();
        $accounts = WhatsappAccount::where('user_id', $owner->id)->where('status', 'connected')->get();
        $templates = MessageTemplate::where('user_id', $owner->id)->where('status', 'approved')->get();
        $teamMembers = [];

        if ($owner->team) {
            $teamMembers = $owner->team->members()->with('member:id,name')->where('status', 'active')->get();
        }

        return view('automations.builder', compact('automation', 'tags', 'accounts', 'templates', 'teamMembers'));
    }

    public function saveFlow(Request $request, Automation $automation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($automation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'trigger_config' => 'nullable|array',
            'whatsapp_account_id' => 'nullable|exists:whatsapp_accounts,id',
            'steps' => 'nullable|array',
            'steps.*.step_id' => 'required|string',
            'steps.*.type' => 'required|string',
            'steps.*.config' => 'nullable|array',
            'steps.*.next_step_id' => 'nullable|string',
            'steps.*.branches' => 'nullable|array',
            'steps.*.position_x' => 'nullable|integer',
            'steps.*.position_y' => 'nullable|integer',
            'steps.*.sort_order' => 'nullable|integer',
        ]);

        $automation->update([
            'trigger_config' => $validated['trigger_config'] ?? $automation->trigger_config,
            'whatsapp_account_id' => $validated['whatsapp_account_id'] ?? $automation->whatsapp_account_id,
            'flow_data' => $validated['steps'] ?? [],
        ]);

        $automation->steps()->delete();

        foreach ($validated['steps'] ?? [] as $index => $stepData) {
            AutomationStep::create([
                'automation_id' => $automation->id,
                'step_id' => $stepData['step_id'],
                'type' => $stepData['type'],
                'config' => $stepData['config'] ?? [],
                'next_step_id' => $stepData['next_step_id'] ?? null,
                'branches' => $stepData['branches'] ?? null,
                'position_x' => $stepData['position_x'] ?? 0,
                'position_y' => $stepData['position_y'] ?? 0,
                'sort_order' => $stepData['sort_order'] ?? $index,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Flow saved successfully.']);
    }

    public function toggleStatus(Automation $automation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($automation->user_id !== $owner->id, 403);

        if ($automation->status === 'active') {
            $automation->update(['status' => 'inactive']);
            return back()->with('success', 'Automation deactivated.');
        }

        if ($automation->steps()->count() === 0) {
            return back()->with('error', 'Cannot activate an automation with no steps. Build your flow first.');
        }

        $automation->update(['status' => 'active']);
        return back()->with('success', 'Automation activated!');
    }

    public function duplicate(Automation $automation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($automation->user_id !== $owner->id, 403);

        $new = $automation->replicate();
        $new->name = $automation->name . ' (Copy)';
        $new->status = 'draft';
        $new->execution_count = 0;
        $new->save();

        foreach ($automation->steps as $step) {
            $newStep = $step->replicate();
            $newStep->automation_id = $new->id;
            $newStep->save();
        }

        return redirect()->route('automations.builder', $new)
            ->with('success', 'Automation duplicated.');
    }

    public function destroy(Automation $automation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($automation->user_id !== $owner->id, 403);

        $automation->delete();
        return redirect()->route('automations.index')->with('success', 'Automation deleted.');
    }
}