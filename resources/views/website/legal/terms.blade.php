@extends('website.layouts.app')
@section('title', 'Terms of Service')

@section('content')
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 prose prose-emerald">
        <h1>Terms of Service</h1>
        <p class="text-gray-500">Last updated: {{ date('F d, Y') }}</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By using Whatify, you agree to these terms of service. If you do not agree, please do not use our platform.</p>

        <h2>2. Service Description</h2>
        <p>Whatify provides WhatsApp Business API automation tools including broadcast messaging, chatbot builder, shared inbox, CRM and third-party integrations.</p>

        <h2>3. Account Responsibilities</h2>
        <p>You are responsible for maintaining the security of your account credentials and for all activities under your account. You must comply with WhatsApp's Business Policy and Commerce Policy.</p>

        <h2>4. Acceptable Use</h2>
        <p>You agree not to use our platform for spam, unsolicited messages, illegal activities, or any content that violates WhatsApp's policies.</p>

        <h2>5. Billing & Payments</h2>
        <p>Subscription fees are billed monthly. Message costs are deducted from your wallet balance. All payments are non-refundable unless stated otherwise in our refund policy.</p>

        <h2>6. Service Availability</h2>
        <p>We strive for 99.9% uptime but do not guarantee uninterrupted service. We are not liable for downtime caused by Meta's WhatsApp API, payment gateways, or force majeure events.</p>

        <h2>7. Termination</h2>
        <p>We may suspend or terminate accounts that violate these terms. You may cancel your account at any time from your dashboard.</p>

        <h2>8. Contact</h2>
        <p>Questions about these terms? Email <a href="mailto:legal@whatify.com">legal@whatify.com</a>.</p>
    </div>
</section>
@endsection