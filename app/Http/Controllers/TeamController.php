<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $team = $user->team;
        $members = $team ? $team->members()->with('member')->get() : collect();

        return view('team.index', compact('team', 'members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'role' => 'required|in:admin,agent,viewer',
        ]);

        $user = auth()->user();

        // Check agent limit
        if (!$user->canUseFeature('agents')) {
            return back()->with('error', 'Agent limit reached. Please upgrade your plan.');
        }

        // Create agent user
        $password = Str::random(10);
        $agentUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($password),
            'role' => 'team_agent',
            'status' => 'active',
            'is_onboarded' => true,
        ]);

        // Add to team
        $team = $user->team ?? Team::create([
            'user_id' => $user->id,
            'name' => $user->business?->company_name ?? 'My Team',
        ]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'member_user_id' => $agentUser->id,
            'role' => $validated['role'],
            'status' => 'active',
            'invited_at' => now(),
            'accepted_at' => now(),
        ]);

        // TODO: Send email with login credentials

        return back()->with('success', "Team member added. Temporary password: {$password}");
    }

    public function destroy(TeamMember $team)
    {
        $member = $team;
        abort_if($member->user_id !== auth()->id(), 403);

        // Deactivate user
        $member->member->update(['status' => 'inactive']);
        $member->update(['status' => 'inactive']);

        return back()->with('success', 'Team member removed.');
    }
}