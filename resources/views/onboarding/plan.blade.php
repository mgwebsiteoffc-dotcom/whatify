@extends('layouts.guest')
@section('title', 'Setup - Choose Plan')
@section('heading', 'Choose your plan')
@section('subheading', 'Step 3 of 4 — Start with a 14-day free trial')

@section('content')
<form method="POST" action="{{ route('onboarding.plan') }}" class="space-y-4">
    @csrf
    @foreach($plans as $plan)
        <label class="block p-4 border-2 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors {{ $loop->first ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <input type="radio" name="plan_id" value="{{ $plan->id }}" class="text-emerald-600 focus:ring-emerald-500" {{ $loop->first ? 'checked' : '' }}>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $plan->name }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $plan->whatsapp_numbers == -1 ? 'Unlimited' : $plan->whatsapp_numbers }} WA numbers •
                            {{ $plan->agents == -1 ? 'Unlimited' : $plan->agents }} agents •
                            {{ $plan->automation_flows == -1 ? 'Unlimited' : $plan->automation_flows }} automations
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-gray-900">₹{{ number_format($plan->price) }}</div>
                    <div class="text-xs text-gray-500">/month</div>
                </div>
            </div>
        </label>
    @endforeach

    <p class="text-center text-xs text-gray-500">All plans include a 14-day free trial. No credit card required.</p>

    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
        Start Free Trial <i class="fas fa-arrow-right ml-2"></i>
    </button>
</form>
@endsection