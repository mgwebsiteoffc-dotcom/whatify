@extends('layouts.guest')

@section('title', 'Register')
@section('heading', 'Create your account')
@section('subheading')
    Already have an account? <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-500">Sign in</a>
@endsection

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-6">
    @csrf
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="91XXXXXXXXXX"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label for="referral_code" class="block text-sm font-medium text-gray-700">Referral Code (optional)</label>
        <input type="text" name="referral_code" id="referral_code" value="{{ old('referral_code', request('ref')) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
        Create Account
    </button>
</form>
@endsection