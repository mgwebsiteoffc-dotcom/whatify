@extends('website.layouts.app')

@section('title', 'Contact Us - Get in Touch')
@section('meta_description', 'Contact Whatify for WhatsApp Business API solutions. Reach out for sales, support or partnership inquiries. We respond within 24 hours.')

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-16">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900">Get in <span class="text-emerald-600">Touch</span></h1>
                <p class="mt-6 text-lg text-gray-600">Have a question about Whatify? Want a custom plan? We would love to hear from you.</p>

                <div class="mt-10 space-y-6">
                    @foreach([
                        ['fas fa-envelope', 'Email Us', 'hello@whatify.com', 'mailto:hello@whatify.com'],
                        ['fab fa-whatsapp', 'WhatsApp', '+91 99999 99999', 'https://wa.me/919999999999'],
                        ['fas fa-clock', 'Working Hours', 'Mon-Sat, 10 AM - 7 PM IST', null],
                        ['fas fa-map-marker-alt', 'Office', 'Mumbai, India', null],
                    ] as [$icon, $label, $value, $link])
                        <div class="flex items-start gap-4">
                            <div class="h-10 w-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <i class="{{ $icon }} text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $label }}</p>
                                @if($link)
                                    <a href="{{ $link }}" class="text-sm text-emerald-600 hover:underline">{{ $value }}</a>
                                @else
                                    <p class="text-sm text-gray-600">{{ $value }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-8">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-700 text-sm">
                        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('website.contact.submit') }}" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm px-4 py-3 border focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm px-4 py-3 border focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm px-4 py-3 border focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <input type="text" name="company" value="{{ old('company') }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm px-4 py-3 border focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <select name="subject" class="mt-1 w-full rounded-lg border-gray-300 text-sm px-4 py-3 border focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Sales">Sales / Pricing</option>
                            <option value="Support">Technical Support</option>
                            <option value="Partnership">Partnership</option>
                            <option value="Enterprise">Enterprise Plan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message *</label>
                        <textarea name="message" rows="5" required class="mt-1 w-full rounded-lg border-gray-300 text-sm px-4 py-3 border focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-6 py-3.5 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 transition-colors text-lg">
                        Send Message <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection