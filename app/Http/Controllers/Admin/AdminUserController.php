<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('role', '!=', 'super_admin')
            ->with(['business:id,user_id,company_name,industry', 'wallet:id,user_id,balance', 'subscription.plan:id,name'])
            ->when($request->search, function ($q, $s) {
                $q->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', "%{$s}%")
                       ->orWhere('email', 'like', "%{$s}%")
                       ->orWhere('phone', 'like', "%{$s}%");
                });
            })
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'business',
            'wallet',
            'subscriptions.plan',
            'whatsappAccounts',
            'contacts',
            'campaigns',
            'automations',
        ]);

        $walletTransactions = $user->walletTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        $messageStats = [
            'total' => $user->messages()->count(),
            'today' => $user->messages()->whereDate('created_at', today())->count(),
            'month' => $user->messages()->whereMonth('created_at', now()->month)->count(),
            'sent' => $user->messages()->where('direction', 'outbound')->count(),
            'received' => $user->messages()->where('direction', 'inbound')->count(),
        ];

        return view('admin.users.show', compact('user', 'walletTransactions', 'messageStats'));
    }

    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        \App\Services\ActivityLogger::log('admin_user_status_changed', 'User', $user->id, null, ['status' => $newStatus]);

        return back()->with('success', "User {$newStatus}.");
    }

    public function addCredits(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:100000',
            'description' => 'required|string|max:255',
        ]);

        app(WalletService::class)->credit(
            $user,
            $validated['amount'],
            "Admin credit: {$validated['description']}",
            'bonus'
        );

        \App\Services\ActivityLogger::log('admin_wallet_credit', 'User', $user->id, null, ['amount' => $validated['amount']]);

        return back()->with('success', "₹{$validated['amount']} added to {$user->name}'s wallet.");
    }

    public function loginAs(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot login as another admin.');
        }

        \App\Services\ActivityLogger::log('admin_login_as', 'User', $user->id);

        session(['admin_original_id' => auth()->id()]);
        auth()->login($user);

        return redirect()->route('dashboard')->with('info', "Logged in as {$user->name}. Use the banner to switch back.");
    }

    public function switchBack()
    {
        $adminId = session('admin_original_id');

        if (!$adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);

        if ($admin && $admin->isSuperAdmin()) {
            session()->forget('admin_original_id');
            auth()->login($admin);
            return redirect()->route('admin.dashboard')->with('success', 'Switched back to admin.');
        }

        return redirect()->route('dashboard');
    }
}