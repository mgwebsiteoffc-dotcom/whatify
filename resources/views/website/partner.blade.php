@extends('website.layouts.app')

@section('title', 'Partner Program - Earn 20% Recurring Commission | Whatify')
@section('meta_description', 'Join the Whatify Partner Program. Earn 20% recurring commission for every customer you refer. Perfect for agencies, freelancers, influencers and consultants.')
@section('meta_keywords', 'whatify partner program, whatsapp api reseller, whatsapp affiliate program, earn commission whatsapp, partner whatsapp business api')

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Whatify Partner Program - Earn 20% Recurring Commission",
    "description": "Join Whatify partner program and earn 20% recurring commission on every referral",
    "url": "{{ route('website.partner') }}"
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {"@type":"Question","name":"How much can I earn as a Whatify partner?","acceptedAnswer":{"@type":"Answer","text":"There is no limit. You earn 20% recurring commission on every payment your referrals make. 10 customers on Growth plan means ₹5,998/month passive income."}},
        {"@type":"Question","name":"Is the partner program free to join?","acceptedAnswer":{"@type":"Answer","text":"Yes, completely free. No fees, no investment required."}},
        {"@type":"Question","name":"When do partners get paid?","acceptedAnswer":{"@type":"Answer","text":"Monthly payouts via bank transfer or UPI. Minimum payout threshold is ₹1,000."}},
        {"@type":"Question","name":"Who can become a Whatify partner?","acceptedAnswer":{"@type":"Answer","text":"Digital agencies, freelancers, influencers, consultants, web developers, resellers — anyone who can refer businesses."}},
        {"@type":"Question","name":"Do I need to be a Whatify customer to join?","acceptedAnswer":{"@type":"Answer","text":"No, you can join the partner program even if you do not use Whatify yourself."}}
    ]
}
</script>
@endsection

@section('content')

{{-- ============================================ --}}
{{-- HERO --}}
{{-- ============================================ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 py-20 lg:py-28">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 h-64 w-64 bg-yellow-400 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 h-64 w-64 bg-emerald-300 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur text-emerald-100 px-5 py-2 rounded-full text-sm font-medium mb-8 border border-white/20">
            <i class="fas fa-star text-yellow-300"></i> Official Partner Program
        </div>
        <h1 class="text-4xl lg:text-6xl font-extrabold text-white leading-tight">
            Earn <span class="text-yellow-300">₹50,000+/month</span><br>
            With Zero Investment
        </h1>
        <p class="mt-6 text-xl text-emerald-100 max-w-3xl mx-auto leading-relaxed">
            Refer businesses to Whatify and earn <strong class="text-white">20% recurring commission</strong> on every payment they make. Forever. No caps. No limits.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#apply" class="px-10 py-4 bg-yellow-400 text-gray-900 text-lg font-bold rounded-xl hover:bg-yellow-300 shadow-xl transition-all hover:scale-105">
                <i class="fas fa-rocket mr-2"></i> Apply Now — It's Free
            </a>
            <a href="#calculator" class="px-10 py-4 bg-white/10 backdrop-blur text-white text-lg font-semibold rounded-xl hover:bg-white/20 border border-white/30 transition-all">
                <i class="fas fa-calculator mr-2"></i> Calculate Earnings
            </a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-6 text-sm text-emerald-200">
            <span><i class="fas fa-check mr-1"></i> Free to join</span>
            <span><i class="fas fa-check mr-1"></i> Instant approval</span>
            <span><i class="fas fa-check mr-1"></i> Monthly payouts</span>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- STATS --}}
{{-- ============================================ --}}
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <p class="text-3xl lg:text-4xl font-extrabold text-emerald-600">20%</p>
                <p class="text-sm font-semibold text-gray-900 mt-1">Recurring Commission</p>
                <p class="text-xs text-gray-500">Every month, forever</p>
            </div>
            <div>
                <p class="text-3xl lg:text-4xl font-extrabold text-emerald-600">₹1,000</p>
                <p class="text-sm font-semibold text-gray-900 mt-1">Min Payout</p>
                <p class="text-xs text-gray-500">Low threshold, easy payouts</p>
            </div>
            <div>
                <p class="text-3xl lg:text-4xl font-extrabold text-emerald-600">
                    @if($partnerCount > 0)
                        {{ $partnerCount }}+
                    @else
                        500+
                    @endif
                </p>
                <p class="text-sm font-semibold text-gray-900 mt-1">Active Partners</p>
                <p class="text-xs text-gray-500">Growing community</p>
            </div>
            <div>
                <p class="text-3xl lg:text-4xl font-extrabold text-emerald-600">Lifetime</p>
                <p class="text-sm font-semibold text-gray-900 mt-1">Cookie Duration</p>
                <p class="text-xs text-gray-500">Never lose a referral</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- HOW YOU EARN --}}
{{-- ============================================ --}}
<section class="py-20 bg-gray-50" id="how-it-works">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">How You Earn With Whatify</h2>
            <p class="mt-4 text-lg text-gray-600">Three simple steps to passive income</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 relative">
            {{-- Connecting line --}}
            <div class="hidden md:block absolute top-16 left-1/6 right-1/6 h-0.5 bg-emerald-200"></div>

            @foreach([
                ['1', 'Sign Up Free', 'Create your partner account in 2 minutes. No fees, no investment. Get your unique referral link instantly.', 'fas fa-user-plus', 'emerald'],
                ['2', 'Share & Refer', 'Share your link with businesses via social media, email, blog, YouTube, or direct outreach. We provide marketing materials.', 'fas fa-share-alt', 'blue'],
                ['3', 'Earn Every Month', 'When your referral subscribes or recharges wallet, you earn 20% commission. Automatically. Every single time.', 'fas fa-coins', 'yellow'],
            ] as [$num, $title, $desc, $icon, $color])
                <div class="relative bg-white rounded-2xl p-8 shadow-lg border text-center hover:shadow-xl transition-shadow">
                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 h-10 w-10 rounded-full bg-{{ $color }}-600 text-white flex items-center justify-center text-lg font-bold shadow-lg z-10">
                        {{ $num }}
                    </div>
                    <div class="h-16 w-16 mx-auto rounded-2xl bg-{{ $color }}-100 flex items-center justify-center mt-4 mb-6">
                        <i class="{{ $icon }} text-{{ $color }}-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $title }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        {{-- Earning Example --}}
        <div class="mt-16 bg-white rounded-2xl shadow-lg border overflow-hidden max-w-3xl mx-auto">
            <div class="bg-emerald-600 p-4 text-white text-center">
                <h3 class="text-lg font-bold"><i class="fas fa-lightbulb mr-2"></i> Real Earning Example</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 text-gray-500">Scenario</th>
                                <th class="text-center py-3 px-4 text-gray-500">Referrals</th>
                                <th class="text-center py-3 px-4 text-gray-500">Avg Plan</th>
                                <th class="text-right py-3 px-4 text-gray-500">Monthly Earning</th>
                                <th class="text-right py-3 px-4 text-gray-500">Yearly Earning</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['Starter', 5, '₹999', '₹999', '₹11,988'],
                                ['Growing', 15, '₹2,999', '₹8,997', '₹1,07,964'],
                                ['Pro Partner', 50, '₹2,999', '₹29,990', '₹3,59,880'],
                                ['Top Earner', 100, '₹5,000', '₹1,00,000', '₹12,00,000'],
                            ] as [$level, $refs, $plan, $monthly, $yearly])
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium text-gray-900">{{ $level }}</td>
                                    <td class="py-3 px-4 text-center">{{ $refs }}</td>
                                    <td class="py-3 px-4 text-center">{{ $plan }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ $monthly }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-700">{{ $yearly }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-400 mt-4 text-center">*Calculations based on 20% commission rate. Additional wallet recharge commissions not included.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- EARNINGS CALCULATOR --}}
{{-- ============================================ --}}
<section class="py-20" id="calculator">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Calculate Your Earnings</h2>
            <p class="mt-4 text-lg text-gray-600">Slide to see how much you can earn</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border" x-data="{
            customers: 10,
            plan: 2999,
            walletAvg: 2000,
            get planCommission() { return this.customers * this.plan * 0.20 },
            get walletCommission() { return this.customers * this.walletAvg * 0.20 },
            get monthly() { return this.planCommission + this.walletCommission },
            get yearly() { return this.monthly * 12 }
        }">
            <div class="p-8 space-y-8">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <label class="text-sm font-semibold text-gray-700">Number of Referrals</label>
                        <span class="text-2xl font-extrabold text-emerald-600" x-text="customers"></span>
                    </div>
                    <input type="range" x-model="customers" min="1" max="200" class="w-full h-3 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>1</span><span>50</span><span>100</span><span>150</span><span>200</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700 mb-2 block">Average Plan</label>
                        <select x-model="plan" class="w-full rounded-lg border-gray-300 border px-4 py-3 text-sm">
                            <option value="999">Starter — ₹999/mo</option>
                            <option value="2999" selected>Growth — ₹2,999/mo</option>
                            <option value="9999">Pro — ₹9,999/mo</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 mb-2 block">Avg Monthly Wallet Recharge</label>
                        <select x-model="walletAvg" class="w-full rounded-lg border-gray-300 border px-4 py-3 text-sm">
                            <option value="1000">₹1,000</option>
                            <option value="2000" selected>₹2,000</option>
                            <option value="5000">₹5,000</option>
                            <option value="10000">₹10,000</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                    <div class="bg-emerald-50 rounded-xl p-6 text-center border border-emerald-200">
                        <p class="text-sm text-gray-600 mb-2">Your Monthly Earnings</p>
                        <p class="text-4xl font-extrabold text-emerald-700">₹<span x-text="Math.round(monthly).toLocaleString('en-IN')"></span></p>
                        <p class="text-xs text-gray-500 mt-2">
                            Plan: ₹<span x-text="Math.round(planCommission).toLocaleString('en-IN')"></span> +
                            Wallet: ₹<span x-text="Math.round(walletCommission).toLocaleString('en-IN')"></span>
                        </p>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-6 text-center border border-yellow-200">
                        <p class="text-sm text-gray-600 mb-2">Your Yearly Earnings</p>
                        <p class="text-4xl font-extrabold text-yellow-700">₹<span x-text="Math.round(yearly).toLocaleString('en-IN')"></span></p>
                        <p class="text-xs text-gray-500 mt-2">That's passive income 🎉</p>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-600 p-4 text-center">
                <a href="#apply" class="text-white font-bold hover:underline text-lg">
                    Start Earning Now → Apply Below <i class="fas fa-arrow-down ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- WHO CAN JOIN --}}
{{-- ============================================ --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Who Can Become a Partner?</h2>
            <p class="mt-4 text-lg text-gray-600">If you know businesses that use WhatsApp, you can earn</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['Digital Marketing Agencies', 'Offer WhatsApp automation as a service. Add recurring revenue to your agency without building anything.', 'fas fa-bullhorn', 'blue', '25+ referrals avg'],
                ['Web Developers & Designers', 'Recommend Whatify to your clients who need WhatsApp for their business. Easy upsell.', 'fas fa-code', 'purple', '10+ referrals avg'],
                ['YouTubers & Influencers', 'Create content about WhatsApp marketing and monetize your audience with affiliate links.', 'fas fa-video', 'red', '50+ referrals avg'],
                ['Software Resellers', 'Add WhatsApp API to your product catalog. High demand, easy sell, recurring income.', 'fas fa-laptop-code', 'emerald', '30+ referrals avg'],
                ['Business Consultants', 'Help clients automate communication. Earn while delivering value.', 'fas fa-chart-line', 'yellow', '15+ referrals avg'],
                ['Freelancers', 'Earn passive income alongside your projects. Just share your link.', 'fas fa-user-tie', 'cyan', '5+ referrals avg'],
                ['E-commerce Enablers', 'Help D2C brands set up WhatsApp marketing and order notifications.', 'fas fa-shopping-cart', 'pink', '20+ referrals avg'],
                ['CA & Tax Professionals', 'Your business clients need WhatsApp. Refer them and earn.', 'fas fa-calculator', 'orange', '10+ referrals avg'],
            ] as [$title, $desc, $icon, $color, $avg])
                <div class="bg-white rounded-xl p-6 border hover:shadow-lg transition-all hover:-translate-y-1 group">
                    <div class="h-12 w-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="{{ $icon }} text-{{ $color }}-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 mb-3">{{ $desc }}</p>
                    <span class="inline-flex items-center text-xs font-medium text-{{ $color }}-600 bg-{{ $color }}-50 px-2 py-1 rounded-full">
                        <i class="fas fa-users mr-1 text-[10px]"></i> {{ $avg }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- WHY WHATIFY --}}
{{-- ============================================ --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-8">Why Partners Choose Whatify</h2>
                <div class="space-y-6">
                    @foreach([
                        ['fas fa-sync-alt', '20% Recurring Forever', 'Not just first payment. You earn 20% on every payment — subscription AND wallet recharges. For as long as they are a customer.'],
                        ['fas fa-money-bill-wave', 'Fast & Easy Payouts', 'Minimum payout ₹1,000. Get paid monthly to your bank account or UPI. No delays.'],
                        ['fas fa-chart-pie', 'Real-Time Dashboard', 'Track every click, signup, and commission in real-time. Full transparency.'],
                        ['fas fa-bullseye', 'Marketing Support', 'Get banners, email templates, case studies, and landing pages. We help you sell.'],
                        ['fas fa-headset', 'Dedicated Partner Manager', 'Priority support and a dedicated account manager for top partners.'],
                        ['fas fa-gift', 'Zero Investment', 'Completely free. No purchase required. No hidden costs. Ever.'],
                    ] as [$icon, $title, $desc])
                        <div class="flex gap-4">
                            <div class="h-11 w-11 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <i class="{{ $icon }} text-emerald-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                                <p class="text-gray-600 mt-1 text-sm">{{ $desc }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Testimonial --}}
            <div class="space-y-6">
                @foreach([
                    ['I referred 20 clients in 3 months. Now earning ₹60,000+ every month passively. Best decision ever.', 'Rahul S.', 'Digital Agency, Mumbai', '₹60,000/mo'],
                    ['As a freelance developer, I just share the link with my clients. Extra ₹15,000 every month without any effort.', 'Priya K.', 'Freelancer, Bangalore', '₹15,000/mo'],
                    ['My YouTube channel about business tools brings me 10+ referrals monthly. The commissions add up fast!', 'Amit J.', 'YouTuber, Delhi', '₹40,000/mo'],
                ] as [$quote, $name, $role, $earning])
                    <div class="bg-emerald-50 rounded-2xl p-6 border border-emerald-100">
                        <div class="flex items-center gap-1 mb-3">
                            @for($i = 0; $i < 5; $i++)
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                            @endfor
                        </div>
                        <blockquote class="text-gray-700 italic mb-4">"{{ $quote }}"</blockquote>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-emerald-200 flex items-center justify-center text-emerald-700 font-bold">
                                    {{ substr($name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">{{ $name }}</p>
                                    <p class="text-xs text-gray-500">{{ $role }}</p>
                                </div>
                            </div>
                            <span class="text-lg font-bold text-emerald-600">{{ $earning }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- APPLICATION FORM --}}
{{-- ============================================ --}}
<section class="py-20 bg-gray-50" id="apply">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Apply to Become a Partner</h2>
            <p class="mt-4 text-lg text-gray-600">Takes less than 2 minutes. Start earning immediately after approval.</p>
        </div>

        <div class="grid lg:grid-cols-5 gap-8">
            {{-- Form --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-xl p-8 border">
                    @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-lg mb-6">
                            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6">
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @auth
                        @if(auth()->user()->partner)
                            <div class="text-center py-8">
                                <div class="h-20 w-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check text-green-600 text-3xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">You're Already a Partner! 🎉</h3>
                                <p class="text-gray-600 mt-2">Go to your dashboard to get your referral link and start earning.</p>
                                <a href="{{ route('partner.dashboard') }}" class="mt-6 inline-flex items-center px-8 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 shadow-lg">
                                    Open Partner Dashboard <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        @else
                            {{-- Logged in but not partner yet --}}
                            <div class="bg-blue-50 rounded-lg p-4 mb-6 flex items-center gap-3">
                                <i class="fas fa-user-check text-blue-600"></i>
                                <p class="text-sm text-blue-800">Logged in as <strong>{{ auth()->user()->email }}</strong>. Complete the form below.</p>
                            </div>
                            <form method="POST" action="{{ route('website.partner.apply') }}" class="space-y-5">
                                @csrf
                                @include('website.partials.partner-form-fields')
                            </form>
                        @endif
                    @else
                        {{-- Guest - needs signup --}}
                        <div x-data="{ tab: 'signup' }">
                            <div class="flex border-b mb-6">
                                <button @click="tab = 'signup'" :class="tab === 'signup' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                        class="flex-1 py-3 text-sm font-semibold border-b-2 transition-colors text-center">
                                    <i class="fas fa-user-plus mr-1"></i> New Account + Apply
                                </button>
                                <button @click="tab = 'login'" :class="tab === 'login' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                        class="flex-1 py-3 text-sm font-semibold border-b-2 transition-colors text-center">
                                    <i class="fas fa-sign-in-alt mr-1"></i> Existing Account? Login
                                </button>
                            </div>

                            {{-- SIGNUP + APPLY --}}
                            <div x-show="tab === 'signup'" x-cloak>
                                <form method="POST" action="{{ route('website.partner.apply') }}" class="space-y-5">
                                    @csrf
                                    <div class="border-b pb-5 mb-5">
                                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">
                                            <i class="fas fa-user mr-1 text-emerald-600"></i> Your Account
                                        </h3>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                                                <input type="text" name="name" value="{{ old('name') }}" required
                                                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Phone *</label>
                                                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="91XXXXXXXXXX"
                                                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Email *</label>
                                                <input type="email" name="email" value="{{ old('email') }}" required
                                                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Password *</label>
                                                <input type="password" name="password" required minlength="8"
                                                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                                                <input type="password" name="password_confirmation" required
                                                       class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                            </div>
                                        </div>
                                    </div>

                                    @include('website.partials.partner-form-fields')
                                </form>
                            </div>

                            {{-- LOGIN --}}
                            <div x-show="tab === 'login'" x-cloak>
                                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ route('partner.apply') }}">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" required class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Password</label>
                                        <input type="password" name="password" required class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <button type="submit" class="w-full px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-colors">
                                        Login & Apply as Partner
                                    </button>
                                    <p class="text-center text-xs text-gray-500">
                                        Don't have an account? <button type="button" @click="tab = 'signup'" class="text-emerald-600 font-semibold underline">Create one</button>
                                    </p>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl p-6 border shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4"><i class="fas fa-shield-alt text-emerald-600 mr-2"></i> What You Get</h3>
                    <ul class="space-y-3">
                        @foreach([
                            '20% recurring commission on all payments',
                            'Unique referral link & code',
                            'Partner dashboard with real-time tracking',
                            'Marketing materials & banners',
                            'Monthly bank/UPI payouts',
                            'Priority partner support',
                            'Co-branded landing pages',
                            'Exclusive partner offers',
                        ] as $item)
                            <li class="flex items-start gap-2 text-sm">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                                <span class="text-gray-700">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-emerald-700 rounded-xl p-6 text-white">
                    <h3 class="font-bold mb-2"><i class="fas fa-clock mr-2"></i> Quick Approval</h3>
                    <p class="text-sm text-emerald-100">Most applications are approved within 24 hours. Some instantly!</p>
                </div>

                <div class="bg-white rounded-xl p-6 border shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-3">Have Questions?</h3>
                    <div class="space-y-3">
                        <a href="mailto:partners@whatify.com" class="flex items-center gap-2 text-sm text-gray-600 hover:text-emerald-600">
                            <i class="fas fa-envelope w-5"></i> partners@whatify.com
                        </a>
                        <a href="https://wa.me/919999999999?text=Hi%2C%20I%20want%20to%20join%20the%20partner%20program" target="_blank" class="flex items-center gap-2 text-sm text-gray-600 hover:text-emerald-600">
                            <i class="fab fa-whatsapp w-5"></i> Chat on WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- FAQ --}}
{{-- ============================================ --}}
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Partner Program FAQ</h2>
        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['How much can I earn as a Whatify partner?', 'There is no limit. You earn 20% recurring commission on every payment your referrals make. 10 customers on the Growth plan (₹2,999) means ₹5,998/month in passive income. Plus commission on their wallet recharges.'],
                ['Is the partner program free to join?', 'Absolutely free. No fees, no investment, no purchase required. Create an account and start referring.'],
                ['When and how do partners get paid?', 'We process payouts monthly to your bank account or UPI. Minimum payout threshold is just ₹1,000.'],
                ['Do I need to be a Whatify customer to join?', 'No. You can join the partner program even if you do not use Whatify for your own business.'],
                ['What marketing materials do you provide?', 'We provide banners, social media templates, email copy, case studies, comparison sheets, and custom landing pages for top partners.'],
                ['Can I offer discounts to my referrals?', 'Yes! Partners can offer exclusive discounts to their referrals. Contact your partner manager for custom codes.'],
                ['How long does approval take?', 'Most applications are approved within 24 hours. Some are approved instantly if the profile is complete.'],
                ['Is there a minimum number of referrals?', 'No minimum requirements. Even 1 referral starts earning you commission.'],
            ] as $i => [$q, $a])
                <div class="bg-white rounded-xl border overflow-hidden shadow-sm">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex justify-between items-center px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50">
                        <span>{{ $q }}</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform flex-shrink-0 ml-4" :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-collapse>
                        <p class="px-6 pb-4 text-gray-600 leading-relaxed">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- FINAL CTA --}}
{{-- ============================================ --}}
<section class="py-20 bg-gradient-to-br from-emerald-600 to-teal-700">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold text-white">Your Passive Income Journey Starts Here</h2>
        <p class="mt-4 text-xl text-emerald-100">Join 500+ partners already earning with Whatify</p>
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#apply" class="px-10 py-4 bg-yellow-400 text-gray-900 text-lg font-bold rounded-xl hover:bg-yellow-300 shadow-xl transition-all">
                <i class="fas fa-rocket mr-2"></i> Apply Now — Free Forever
            </a>
        </div>
        <div class="mt-6 flex items-center justify-center gap-8 text-sm text-emerald-200">
            <span><i class="fas fa-clock mr-1"></i> 2 min setup</span>
            <span><i class="fas fa-rupee-sign mr-1"></i> ₹0 investment</span>
            <span><i class="fas fa-infinity mr-1"></i> Unlimited earnings</span>
        </div>
    </div>
</section>

@endsection