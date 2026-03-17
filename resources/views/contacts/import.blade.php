@extends('layouts.app')
@section('title', 'Import Contacts')
@section('page-title', 'Import Contacts')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Import from CSV/Excel</h3>
            <div class="bg-blue-50 border border-blue-200 rounded p-4 text-sm text-blue-700">
                <p class="font-medium mb-1"><i class="fas fa-info-circle mr-1"></i> File Format Requirements:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    <li>Supported formats: .csv, .xlsx, .xls</li>
                    <li>Required column: <code class="bg-blue-100 px-1 rounded">phone</code> (or <code class="bg-blue-100 px-1 rounded">mobile</code> or <code class="bg-blue-100 px-1 rounded">phone_number</code>)</li>
                    <li>Optional columns: <code class="bg-blue-100 px-1 rounded">name</code>, <code class="bg-blue-100 px-1 rounded">email</code></li>
                    <li>Any additional columns will be stored as custom attributes</li>
                    <li>Duplicates will be automatically skipped</li>
                    <li>Max file size: 10MB</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('contacts.import') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Upload File *</label>
                <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Apply Tags (optional)</label>
                <div class="flex flex-wrap gap-2">
                    @php $tags = \App\Models\Tag::where('user_id', auth()->user()->getBusinessOwner()?->id ?? auth()->id())->get(); @endphp
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-full cursor-pointer hover:bg-gray-50 text-sm">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-emerald-600">
                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $tag->color }}"></span>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                    <i class="fas fa-file-import mr-2"></i>Import Contacts
                </button>
                <a href="{{ route('contacts.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>

        {{-- Sample download --}}
        <div class="mt-6 pt-6 border-t">
            <p class="text-sm text-gray-500 mb-2">Need a sample file?</p>
            <a href="#" onclick="downloadSample()" class="text-sm text-emerald-600 hover:text-emerald-700">
                <i class="fas fa-download mr-1"></i> Download Sample CSV
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function downloadSample() {
    const csv = "name,phone,email,city,company\nJohn Doe,9876543210,john@example.com,Mumbai,ABC Corp\nJane Smith,9876543211,jane@example.com,Delhi,XYZ Ltd";
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sample_contacts.csv';
    a.click();
}
</script>
@endpush
@endsection