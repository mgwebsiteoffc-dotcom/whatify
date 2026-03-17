<?php

namespace App\Services\Integrations;

use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService extends BaseIntegrationService
{
    public function getType(): string
    {
        return 'google_sheets';
    }

    public function connect(array $config): bool
    {
        try {
            $spreadsheetId = $this->extractSpreadsheetId($config['spreadsheet_url'] ?? $config['spreadsheet_id'] ?? '');

            if (!$spreadsheetId) {
                $this->logEvent('connection_failed', $config, null, 'failed', 'Invalid spreadsheet URL');
                return false;
            }

            $apiKey = $config['api_key'] ?? config('services.google.sheets_api_key');

            $response = Http::get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}", [
                'key' => $apiKey,
                'fields' => 'properties.title,sheets.properties',
            ]);

            if ($response->successful()) {
                $spreadsheet = $response->json();
                $sheets = collect($spreadsheet['sheets'] ?? [])->map(fn($s) => $s['properties']['title'] ?? 'Sheet1')->toArray();

                $this->integration->update([
                    'config' => [
                        'spreadsheet_id' => $spreadsheetId,
                        'spreadsheet_url' => $config['spreadsheet_url'] ?? '',
                        'spreadsheet_title' => $spreadsheet['properties']['title'] ?? 'Untitled',
                        'api_key' => $apiKey,
                        'service_account_json' => $config['service_account_json'] ?? null,
                        'sheets' => $sheets,
                        'default_sheet' => $config['default_sheet'] ?? $sheets[0] ?? 'Sheet1',
                        'sync_direction' => $config['sync_direction'] ?? 'both',
                        'column_mapping' => $config['column_mapping'] ?? [
                            'A' => 'name',
                            'B' => 'phone',
                            'C' => 'email',
                        ],
                        'auto_sync' => $config['auto_sync'] ?? false,
                        'header_row' => $config['header_row'] ?? true,
                    ],
                    'status' => 'active',
                    'last_synced_at' => now(),
                ]);

                $this->logEvent('connected', ['spreadsheet' => $spreadsheet['properties']['title'] ?? '']);
                return true;
            }

            $this->logEvent('connection_failed', $config, $response->json(), 'failed', 'Cannot access spreadsheet');
            return false;

        } catch (\Exception $e) {
            $this->logEvent('connection_error', $config, null, 'failed', $e->getMessage());
            return false;
        }
    }

    public function disconnect(): bool
    {
        $this->updateStatus('inactive');
        $this->logEvent('disconnected');
        return true;
    }

    public function testConnection(): bool
    {
        try {
            $spreadsheetId = $this->getConfig('spreadsheet_id');
            $apiKey = $this->getConfig('api_key');

            $response = Http::get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}", [
                'key' => $apiKey,
                'fields' => 'properties.title',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function readRows(string $sheet = null, string $range = null, int $limit = 1000): array
    {
        $spreadsheetId = $this->getConfig('spreadsheet_id');
        $apiKey = $this->getConfig('api_key');
        $sheet = $sheet ?? $this->getConfig('default_sheet', 'Sheet1');
        $range = $range ?? "{$sheet}!A1:Z{$limit}";

        try {
            $response = Http::get(
                "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}",
                ['key' => $apiKey]
            );

            if ($response->successful()) {
                $values = $response->json('values', []);
                $this->logEvent('rows_read', ['count' => count($values)]);
                return ['success' => true, 'data' => $values];
            }

            return ['success' => false, 'error' => $response->json('error.message', 'Failed to read')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function appendRow(string $sheet = null, array $values = []): array
    {
        $spreadsheetId = $this->getConfig('spreadsheet_id');
        $serviceAccount = $this->getConfig('service_account_json');
        $sheet = $sheet ?? $this->getConfig('default_sheet', 'Sheet1');

        $accessToken = $this->getAccessToken($serviceAccount);

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Cannot authenticate with Google Sheets'];
        }

        try {
            $response = Http::withToken($accessToken)
                ->post(
                    "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$sheet}!A:Z:append",
                    [
                        'values' => [$values],
                    ],
                    [
                        'valueInputOption' => 'USER_ENTERED',
                        'insertDataOption' => 'INSERT_ROWS',
                    ]
                );

            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$sheet}!A:Z:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS";

            $response = Http::withToken($accessToken)->post($url, [
                'values' => [$values],
            ]);

            if ($response->successful()) {
                $this->logEvent('row_appended', ['values' => $values]);
                return ['success' => true];
            }

            return ['success' => false, 'error' => $response->json('error.message', 'Failed')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function importContacts(string $sheet = null): array
    {
        $result = $this->readRows($sheet);

        if (!$result['success']) return $result;

        $rows = $result['data'];
        if (empty($rows)) return ['success' => true, 'imported' => 0, 'skipped' => 0];

        $hasHeader = $this->getConfig('header_row', true);
        $headers = $hasHeader ? array_shift($rows) : null;
        $columnMapping = $this->getConfig('column_mapping', []);

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $mapped = $this->mapRowToContact($row, $headers, $columnMapping);

            if (empty($mapped['phone'])) {
                $skipped++;
                continue;
            }

            $phone = preg_replace('/[^0-9]/', '', $mapped['phone']);
            $localPhone = substr($phone, -10);

            if (strlen($localPhone) < 10) {
                $skipped++;
                continue;
            }

            $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

            $exists = Contact::where('user_id', $this->user->id)->where('phone', $localPhone)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Contact::create([
                'user_id' => $this->user->id,
                'phone' => $localPhone,
                'country_code' => $countryCode,
                'name' => $mapped['name'] ?? null,
                'email' => filter_var($mapped['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $mapped['email'] : null,
                'source' => 'google_sheets',
                'status' => 'active',
                'custom_attributes' => $mapped['extra'] ?? null,
                'opted_in_at' => now(),
            ]);

            $imported++;
        }

        $this->updateStatus('active');
        $this->logEvent('contacts_imported', ['imported' => $imported, 'skipped' => $skipped]);

        return ['success' => true, 'imported' => $imported, 'skipped' => $skipped];
    }

    public function exportContacts(string $sheet = null, ?string $tagId = null): array
    {
        $sheet = $sheet ?? $this->getConfig('default_sheet', 'Sheet1');

        $contacts = Contact::where('user_id', $this->user->id)
            ->when($tagId, fn($q) => $q->whereHas('tags', fn($q2) => $q2->where('tags.id', $tagId)))
            ->where('status', 'active')
            ->get();

        if ($contacts->isEmpty()) {
            return ['success' => true, 'exported' => 0];
        }

        $rows = [['Name', 'Phone', 'Email', 'Status', 'Source', 'Created At']];

        foreach ($contacts as $contact) {
            $rows[] = [
                $contact->name ?? '',
                $contact->country_code . $contact->phone,
                $contact->email ?? '',
                $contact->status,
                $contact->source ?? '',
                $contact->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $spreadsheetId = $this->getConfig('spreadsheet_id');
        $accessToken = $this->getAccessToken($this->getConfig('service_account_json'));

        if (!$accessToken) {
            return ['success' => false, 'error' => 'Cannot authenticate'];
        }

        try {
            Http::withToken($accessToken)->put(
                "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$sheet}!A1?valueInputOption=USER_ENTERED",
                ['values' => $rows]
            );

            $this->logEvent('contacts_exported', ['count' => $contacts->count()]);
            return ['success' => true, 'exported' => $contacts->count()];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function appendLead(array $data): array
    {
        $columnMapping = $this->getConfig('column_mapping', []);
        $row = [];

        foreach ($columnMapping as $col => $field) {
            $row[] = $data[$field] ?? '';
        }

        if (empty($row)) {
            $row = [
                $data['name'] ?? '',
                $data['phone'] ?? '',
                $data['email'] ?? '',
                $data['source'] ?? 'whatsapp',
                now()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->appendRow(null, $row);
    }

    protected function mapRowToContact(array $row, ?array $headers, array $columnMapping): array
    {
        $mapped = ['extra' => []];

        if ($headers) {
            foreach ($headers as $index => $header) {
                $headerLower = strtolower(trim($header));
                $value = $row[$index] ?? '';

                if (in_array($headerLower, ['name', 'first_name', 'full_name'])) {
                    $mapped['name'] = $value;
                } elseif (in_array($headerLower, ['phone', 'mobile', 'phone_number', 'whatsapp'])) {
                    $mapped['phone'] = $value;
                } elseif (in_array($headerLower, ['email', 'email_address'])) {
                    $mapped['email'] = $value;
                } else {
                    $mapped['extra'][$headerLower] = $value;
                }
            }
        } else {
            foreach ($columnMapping as $colIndex => $field) {
                $index = ord(strtoupper($colIndex)) - ord('A');
                $mapped[$field] = $row[$index] ?? '';
            }
        }

        return $mapped;
    }

    protected function extractSpreadsheetId(string $urlOrId): ?string
    {
        if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $urlOrId, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^[a-zA-Z0-9-_]{20,}$/', $urlOrId)) {
            return $urlOrId;
        }

        return null;
    }

    protected function getAccessToken(?string $serviceAccountJson): ?string
    {
        if (!$serviceAccountJson) return null;

        try {
            $credentials = json_decode($serviceAccountJson, true);

            if (!$credentials) return null;

            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $now = time();
            $claims = base64_encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/spreadsheets',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]));

            $signature = '';
            openssl_sign("{$header}.{$claims}", $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = "{$header}.{$claims}." . base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json('access_token');

        } catch (\Exception $e) {
            Log::error('Google Sheets auth failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}