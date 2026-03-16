@extends('layouts.guest')
@section('title', 'Setup - Industry')
@section('heading', 'Select your industry')
@section('subheading', 'Step 2 of 4 — This helps us provide relevant templates')

@section('content')
<form method="POST" action="{{ route('onboarding.industry') }}" class="space-y-4">
    @csrf
    <div class="grid grid-cols-2 gap-3">
        @foreach($industries as $key => $label)
            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-emerald-50 hover:border-emerald-300 transition-colors">
                <input type="radio" name="industry" value="{{ $key }}" class="text-emerald-600 focus:ring-emerald-500" {{ old('industry') === $key ? 'checked' : '' }}>
                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
        Continue <i class="fas fa-arrow-right ml-2"></i>
    </button>
</form>
@endsection