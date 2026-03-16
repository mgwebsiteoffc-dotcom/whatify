@extends('layouts.guest')
@section('title', 'Setup - Connect WhatsApp')
@section('heading', 'Connect WhatsApp Business')
@section('subheading', 'Step 4 of 4 — You can also do this later')

@section('content')
<div class="space-y-6">
    <div class="text-center py-8">
        <div class="text-6xl text-emerald-500 mb-4"><i class="fab fa-whatsapp"></i></div>
        <p class="text-gray-600 mb-6">Connect your WhatsApp Business API to start automating messages.</p>

        <div class="bg-gray-50 rounded-lg p-4 text-left text-sm text-gray-600 mb-6">
            <p class="font-medium text-gray-800 mb-2">What you'll need:</p>
            <ul class="space-y-1">
                <li><i class="fas fa-check text-emerald-500 mr-2"></i>Facebook Business Manager account</li>
                <li><i class="fas fa-check text-emerald-500 mr-2"></i>Verified business phone number</li>
                <li><i class="fas fa-check text-emerald-500 mr-2"></i>Business verification documents</li>
            </ul>
        </div>

        {{-- Connect button will be implemented in Phase 2 --}}
        <button disabled class="w-full py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
            <i class="fab fa-facebook mr-2"></i> Connect with Facebook (Coming in Phase 2)
        </button>
    </div>

    <div class="flex gap-3">
        <form method="POST" action="{{ route('onboarding.skip-whatsapp') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Skip for now
            </button>
        </form>

        <form method="POST" action="{{ route('onboarding.complete') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                Complete Setup <i class="fas fa-check ml-2"></i>
            </button>
        </form>
    </div>
</div>
@endsection