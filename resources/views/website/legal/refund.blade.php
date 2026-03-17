@extends('website.layouts.app')
@section('title', 'Refund Policy')

@section('content')
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 prose prose-emerald">
        <h1>Refund Policy</h1>
        <p class="text-gray-500">Last updated: {{ date('F d, Y') }}</p>

        <h2>Subscription Refunds</h2>
        <p>Subscription fees are non-refundable once the billing cycle has started. You can cancel your subscription anytime and continue using the service until the end of the current billing period.</p>

        <h2>Wallet Refunds</h2>
        <p>Wallet balance is non-refundable. If a message fails to send due to a platform error, the message cost is automatically refunded to your wallet balance.</p>

        <h2>Exceptions</h2>
        <p>Refund requests for exceptional circumstances will be reviewed on a case-by-case basis. Contact <a href="mailto:billing@whatify.com">billing@whatify.com</a> within 7 days of the charge.</p>

        <h2>Processing Time</h2>
        <p>Approved refunds are processed within 5-10 business days to the original payment method.</p>
    </div>
</section>
@endsection