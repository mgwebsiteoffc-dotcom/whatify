<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

   public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status === 'suspended') {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been suspended.']);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        \App\Services\ActivityLogger::log('user_login');

        // Handle redirect_to parameter (for partner apply etc)
        if ($request->filled('redirect_to')) {
            return redirect($request->redirect_to);
        }

        if (!$user->is_onboarded && $user->role === 'business_owner') {
            return redirect()->route('onboarding.index');
        }

        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match.',
    ])->onlyInput('email');
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}