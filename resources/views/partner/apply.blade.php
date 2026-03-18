@extends('layouts.app')
@section('title', 'Become a Partner')
@section('page-title', 'Partner Application')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Info Banner --}}
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-8 text-white mb-8">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-14 w-14 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fas fa-handshake text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold">Become a Whatify Partner</h2>
                <p class="text-emerald-100">Earn 20% recurring commission on every referral</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-6">
            <div class="bg-white/10 rounded-lg p-3 text-center">
                <p class="text-xl font-bold">20%</p>
                <p class="text-xs text-emerald-200">Commission</p>
            </div>
            <div class="bg-white/10 rounded-lg p-3 text-center">
                <p class="text-xl font-bold">₹1,000</p>
                <p class="text-xs text-emerald-200">Min Payout</p>
            </div>
            <div class="bg-white/10 rounded-lg p-3 text-center">
                <p class="text-xl font-bold">Lifetime</p>
                <p class="text-xs text-emerald-200">Duration</p>
            </div>
        </div>
    </div>

    {{-- Application Form --}}
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b">
            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-user-check text-emerald-600"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900">Applying as: {{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('partner.submitApplication') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Company / Business Name *</label>
                <input type="text" name="company_name" required
                       value="{{ old('company_name', auth()->user()->business?->company_name ?? '') }}"
                       placeholder="Your company or business name"
                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Partner Type *</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach([
                        'agency' => ['🏢', 'Digital Agency'],
                        'reseller' => ['🔄', 'Reseller'],
                        'influencer' => ['📱', 'Influencer'],
                        'freelancer' => ['💻', 'Freelancer'],
                        'technology' => ['⚙️', 'Technology'],
                        'consultant' => ['📊', 'Consultant'],
                    ] as $val => [$emoji, $label])
                        <label class="relative flex items-center gap-2 p-3 border-2 rounded-xl cursor-pointer hover:bg-emerald-50 transition-colors
                                      has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="type" value="{{ $val }}" required
                                   {{ old('type') === $val ? 'checked' : '' }}
                                   class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-lg">{{ $emoji }}</span>
                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Website / Portfolio URL</label>
                <input type="url" name="website" value="{{ old('website') }}"
                       placeholder="https://yourwebsite.com"
                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tell us about yourself & how you plan to promote Whatify</label>
                <textarea name="description" rows="4"
                          placeholder="I run a digital marketing agency with 50+ clients and want to offer WhatsApp solutions..."
                          class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">What you'll get:</h4>
                <ul class="space-y-1.5">
                    @foreach([
                        'Unique referral link & code',
                        '20% recurring commission on ALL payments',
                        'Real-time partner dashboard',
                        'Marketing materials & banners',
                        'Monthly payouts to bank/UPI',
                    ] as $benefit)
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <button type="submit" class="w-full px-6 py-4 bg-emerald-600 text-white rounded-xl font-bold text-lg hover:bg-emerald-700 transition-all shadow-lg hover:shadow-xl">
                <i class="fas fa-rocket mr-2"></i> Submit Partner Application
            </button>

            <p class="text-center text-xs text-gray-500">
                Applications are usually approved within 24 hours.
                Questions? Email <a href="mailto:partners@whatify.com" class="text-emerald-600 underline">partners@whatify.com</a>
            </p>
        </form>
    </div>
</div>
@endsection