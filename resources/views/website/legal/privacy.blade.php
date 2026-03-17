@extends('website.layouts.app')
@section('title', 'Privacy Policy')
@section('meta_description', 'Whatify privacy policy. Learn how we collect, use and protect your data.')

@section('content')
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 prose prose-emerald">
        <h1>Privacy Policy</h1>
        <p class="text-gray-500">Last updated: {{ date('F d, Y') }}</p>

        <h2>1. Information We Collect</h2>
        <p>We collect information you provide when registering, using our services, or contacting us. This includes your name, email, phone number, business details, and payment information.</p>

        <h2>2. How We Use Your Information</h2>
        <p>We use your information to provide and improve our services, process payments, send service notifications, and communicate with you about your account.</p>

        <h2>3. WhatsApp Data</h2>
        <p>We process WhatsApp messages on behalf of your business. Messages are transmitted through Meta's WhatsApp Business API. We store message logs for your reference and analytics.</p>

        <h2>4. Data Security</h2>
        <p>We implement industry-standard security measures including encryption, access controls, and regular security audits to protect your data.</p>

        <h2>5. Data Sharing</h2>
        <p>We do not sell your personal data. We share data only with service providers necessary to operate our platform (payment processors, cloud infrastructure, etc.).</p>

        <h2>6. Your Rights</h2>
        <p>You have the right to access, update, or delete your personal data. Contact us at privacy@whatify.com for any data-related requests.</p>

        <h2>7. Contact Us</h2>
        <p>For privacy concerns, email us at <a href="mailto:privacy@whatify.com">privacy@whatify.com</a>.</p>
    </div>
</section>
@endsection