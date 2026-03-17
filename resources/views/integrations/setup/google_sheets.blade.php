@extends('layouts.app')
@section('title', 'Connect Google Sheets')
@section('page-title')
    <a href="{{ route('integrations.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    Connect Google Sheets
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <h4 class="text-sm font-semibold text-green-800 mb-2"><i class="fas fa-table mr-1"></i> Google Sheets Setup</h4>
        <ol class="text-sm text-green-700 space-y-1 list-decimal list-inside">
            <li>Open your Google Spreadsheet</li>
            <li>Click "Share" and make it accessible (Anyone with link → Viewer for read-only)</li>
            <li>Copy the spreadsheet URL</li>
            <li>For write access: Create a Google Cloud service account and share the sheet with it</li>
            <li>Ensure first row has column headers: Name, Phone, Email, etc.</li>
        </ol>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('integrations.store', 'google_sheets') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Spreadsheet URL *</label>
                <input type="text" name="spreadsheet_url" value="{{ old('spreadsheet_url', $existing?->config['spreadsheet_url'] ?? '') }}"
                       required placeholder="https://docs.google.com/spreadsheets/d/SPREADSHEET_ID/edit"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Google API Key (for read access)</label>
                <input type="text" name="api_key" value="{{ old('api_key') }}" placeholder="AIzaSy..."
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                <p class="text-xs text-gray-500 mt-1">Get from Google Cloud Console → APIs & Services → Credentials</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Service Account JSON (for write access)</label>
                <textarea name="service_account_json" rows="4" placeholder='Paste the entire JSON key file content here...'
                          class="mt-1 w-full rounded-md border-gray-300 text-xs px-3 py-2 border font-mono">{{ old('service_account_json') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Required for writing data. Share the spreadsheet with the service account email.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Default Sheet Name</label>
                    <input type="text" name="default_sheet" value="{{ old('default_sheet', 'Sheet1') }}" placeholder="Sheet1"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sync Direction</label>
                    <select name="sync_direction" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="both">Both (Import & Export)</option>
                        <option value="import">Import Only</option>
                        <option value="export">Export Only</option>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-3">
                    <input type="hidden" name="header_row" value="0">
                    <input type="checkbox" name="header_row" value="1" checked class="rounded border-gray-300 text-emerald-600">
                    <span class="text-sm text-gray-700">First row contains headers</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="hidden" name="auto_sync" value="0">
                    <input type="checkbox" name="auto_sync" value="1" class="rounded border-gray-300 text-emerald-600">
                    <span class="text-sm text-gray-700">Auto-sync contacts daily</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                    <i class="fas fa-table mr-2"></i>Connect Google Sheets
                </button>
                <a href="{{ route('integrations.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection