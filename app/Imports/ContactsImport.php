<?php

namespace App\Imports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ContactsImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    protected int $userId;
    protected array $tagIds;
    protected int $rowCount = 0;
    protected int $skippedCount = 0;

    public function __construct(int $userId, array $tagIds = [])
    {
        $this->userId = $userId;
        $this->tagIds = $tagIds;
    }

    public function model(array $row): ?Contact
    {
        $phone = preg_replace('/[^0-9]/', '', $row['phone'] ?? $row['mobile'] ?? $row['phone_number'] ?? '');

        if (empty($phone) || strlen($phone) < 10) {
            $this->skippedCount++;
            return null;
        }

        // Extract last 10 digits
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

        // Check duplicate
        $exists = Contact::where('user_id', $this->userId)
            ->where('phone', $localPhone)
            ->exists();

        if ($exists) {
            $this->skippedCount++;
            return null;
        }

        // Check contact limit
        $owner = \App\Models\User::find($this->userId);
        if ($owner && !$owner->canUseFeature('contacts_limit')) {
            $this->skippedCount++;
            return null;
        }

        $this->rowCount++;

        $contact = new Contact([
            'user_id' => $this->userId,
            'phone' => $localPhone,
            'country_code' => $countryCode,
            'name' => $row['name'] ?? $row['first_name'] ?? null,
            'email' => filter_var($row['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $row['email'] : null,
            'source' => 'import',
            'status' => 'active',
            'custom_attributes' => $this->extractCustomAttributes($row),
            'opted_in_at' => now(),
        ]);

        return $contact;
    }

    protected function extractCustomAttributes(array $row): ?array
    {
        $skipFields = ['name', 'first_name', 'last_name', 'phone', 'mobile', 'phone_number', 'email', 'country_code'];
        $attrs = [];

        foreach ($row as $key => $value) {
            if (!in_array(strtolower($key), $skipFields) && !empty($value)) {
                $attrs[$key] = $value;
            }
        }

        return !empty($attrs) ? $attrs : null;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}