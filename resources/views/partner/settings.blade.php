@extends('layouts.app')
@section('title', 'Partner Settings')
@section('page-title', 'Partner Settings')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Company & Payout Details</h3>
        <form method="POST" action="{{ route('partner.updateSettings') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                <input type="text" name="company_name" value="{{ old('company_name', $partner->company_name) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Bank Details (for payouts)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $partner->payout_details['bank_name'] ?? '') }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Account Holder</label>
                        <input type="text" name="account_holder" value="{{ old('account_holder', $partner->payout_details['account_holder'] ?? '') }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $partner->payout_details['account_number'] ?? '') }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">IFSC Code</label>
                        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $partner->payout_details['ifsc_code'] ?? '') }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-500">UPI ID (optional)</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $partner->payout_details['upi_id'] ?? '') }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Save Settings</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Partner Info</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between border-b py-1"><span class="text-gray-500">Partner Type</span><span class="capitalize font-medium">{{ $partner->type }}</span></div>
            <div class="flex justify-between border-b py-1"><span class="text-gray-500">Referral Code</span><span class="font-mono font-medium">{{ $partner->referral_code }}</span></div>
            <div class="flex justify-between border-b py-1"><span class="text-gray-500">Commission Rate</span><span class="font-medium">{{ $partner->commission_rate }}%</span></div>
            <div class="flex justify-between border-b py-1"><span class="text-gray-500">Status</span><span class="capitalize font-medium">{{ $partner->status }}</span></div>
            <div class="flex justify-between py-1"><span class="text-gray-500">Joined</span><span>{{ $partner->created_at->format('M d, Y') }}</span></div>
        </div>
    </div>
</div>
@endsection