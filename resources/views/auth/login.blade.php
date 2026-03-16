@extends('layouts.guest')

@section('title', 'Login')
@section('heading', 'Sign in to your account')
@section('subheading')
    Don't have an account? <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-500">Register</a>
@endsection

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-6">
    @csrf
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
    </div>

    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
        Sign in
    </button>
</form>
@endsection