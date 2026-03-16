@extends('layouts.app')
@section('title', 'Account Settings')
@section('page-title', 'Account Settings')

@section('content')
<div class="max-w-2xl space-y-6">
    {{-- Profile --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
        <form method="POST" action="{{ route('account.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                <select name="timezone" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                    <option value="Asia/Kolkata" {{ $user->timezone === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                    <option value="UTC" {{ $user->timezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="America/New_York" {{ $user->timezone === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Avatar</label>
                <input type="file" name="avatar" accept="image/*" class="mt-1">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Save Changes</button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Change Password</h3>
        <form method="POST" action="{{ route('account.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" name="current_password" required
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="password" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Update Password</button>
        </form>
    </div>
</div>
@endsection