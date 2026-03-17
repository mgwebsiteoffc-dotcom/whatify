<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ContactsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(
        protected int $userId,
        protected ?string $tagId = null,
        protected ?string $status = null,
    ) {}

    public function query()
    {
        return Contact::query()
            ->where('user_id', $this->userId)
            ->with('tags')
            ->when($this->tagId, fn($q) => $q->whereHas('tags', fn($q2) => $q2->where('tags.id', $this->tagId)))
            ->when($this->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Country Code', 'Email', 'Status', 'Source', 'Tags', 'Last Message', 'Created At'];
    }

    public function map($contact): array
    {
        return [
            $contact->name,
            $contact->phone,
            $contact->country_code,
            $contact->email,
            $contact->status,
            $contact->source,
            $contact->tags->pluck('name')->implode(', '),
            $contact->last_message_at?->format('Y-m-d H:i'),
            $contact->created_at->format('Y-m-d H:i'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}