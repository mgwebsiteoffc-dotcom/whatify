<div>
    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">
        <i class="fas fa-briefcase mr-1 text-emerald-600"></i> Partner Details
    </h3>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Company / Business Name *</label>
    <input type="text" name="company_name" value="{{ old('company_name', auth()->user()?->business?->company_name ?? '') }}" required
           class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Partner Type *</label>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach([
            'agency' => '🏢 Agency',
            'reseller' => '🔄 Reseller',
            'influencer' => '📱 Influencer',
            'freelancer' => '💻 Freelancer',
            'technology' => '⚙️ Technology',
            'consultant' => '📊 Consultant',
        ] as $val => $label)
            <label class="flex items-center gap-2 p-2.5 border rounded-lg cursor-pointer hover:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 text-sm">
                <input type="radio" name="type" value="{{ $val }}" {{ old('type') === $val ? 'checked' : '' }} required class="text-emerald-600 focus:ring-emerald-500">
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Website / Social Profile</label>
    <input type="url" name="website" value="{{ old('website') }}" placeholder="https://"
           class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">How will you promote Whatify? *</label>
    <textarea name="description" rows="3" required placeholder="I have a YouTube channel with 10K subscribers about business tools..."
              class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Expected Monthly Referrals</label>
    <select name="expected_referrals" class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
        <option value="1-5">1-5 customers/month</option>
        <option value="5-20">5-20 customers/month</option>
        <option value="20-50">20-50 customers/month</option>
        <option value="50+">50+ customers/month</option>
    </select>
</div>

<div class="flex items-start gap-2">
    <input type="checkbox" required id="partner_terms" class="rounded border-gray-300 text-emerald-600 mt-1 focus:ring-emerald-500">
    <label for="partner_terms" class="text-sm text-gray-600">
        I agree to the <a href="{{ route('website.terms') }}" target="_blank" class="text-emerald-600 underline">Terms of Service</a> and 
        <a href="{{ route('website.privacy') }}" target="_blank" class="text-emerald-600 underline">Privacy Policy</a>
    </label>
</div>

<button type="submit" class="w-full px-6 py-4 bg-emerald-600 text-white rounded-xl font-bold text-lg hover:bg-emerald-700 transition-all shadow-lg hover:shadow-xl">
    <i class="fas fa-rocket mr-2"></i> Submit Partner Application
</button>

<p class="text-center text-xs text-gray-500">
    We review applications within 24 hours. You'll receive an email notification.
</p>