<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Department;

class ZKBiotimeService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string|null $authToken = null;

    protected array $paycodeLabels = [
        'paycode_1' => 'Regular(H)',
        'paycode_2' => 'Late In(M)',
        'paycode_3' => 'Early Out(M)',
        'paycode_4' => 'Absence(H)',
        'paycode_5' => 'Normal OT(H)',
        'paycode_6' => 'Weekend OT(H)',
        'paycode_7' => 'Holiday OT(H)',
        'paycode_8' => 'OT1(H)',
        'paycode_9' => 'OT2(H)',
        'paycode_10' => 'OT3(H)',
        'paycode_11' => 'Annual Leave(H)',
        'paycode_12' => 'Sick Leave(H)',
        'paycode_13' => 'Casual Leave(H)',
        'paycode_14' => 'Maternity Leave(H)',
        'paycode_15' => 'Compassionate Leave(H)',
        'paycode_16' => 'Business Trip(H)',
        'paycode_17' => 'Compensatory(H)',
        'paycode_18' => 'Compensatory Leave(H)',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.biotime.base_url');
        $this->username = config('services.biotime.username');
        $this->password = config('services.biotime.password');
    }

    public function authenticate(): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/api-token-auth/", [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $this->authToken = $response['token'];
                return true;
            }

            Log::error('Biotime Auth Failed', ['response' => $response->json()]);
        } catch (\Exception $e) {
            Log::error('Biotime Auth Exception', ['error' => $e->getMessage()]);
        }

        return false;
    }

    protected function withAuthHeaders(): array
    {
        if (!$this->authToken && !$this->authenticate()) {
            throw new \Exception('Biotime authentication failed.');
        }

        return [
            'Authorization' => "Token {$this->authToken}",
            'Content-Type' => 'application/json',
        ];
    }

    protected function withAllDepartments(array $params): array
    {
        if (!isset($params['departments']) || $params['departments'] === -1) {
            $params['departments'] = '1,3,4,5,6,7,8,9,10,11,12,13';
        }

        return $params;
    }

    protected function withDefaultPagination(array $params): array
    {
        $params['page'] = $params['page'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 20;
        return $params;
    }

    protected function mapPaycodes(array &$record): void
    {
        foreach ($this->paycodeLabels as $code => $label) {
            if (array_key_exists($code, $record)) {
                $record[$label] = $record[$code];
                unset($record[$code]);
            }
        }
    }

    public function getMonthlyPunchReport(array $params = [])
    {
        try {
            $params = $this->withDefaultPagination($this->withAllDepartments($params));
            $query = http_build_query(array_merge([
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'areas' => -1,
                'groups' => -1,
                'employees' => -1,
            ], $params));

            $url = "{$this->baseUrl}/att/api/monthlyPunchReport/?$query";
            $response = Http::withHeaders($this->withAuthHeaders())->get($url)->json();

            foreach ($response['data'] ?? [] as &$record) {
                $record['days'] = [];
                foreach ($record as $key => $value) {
                    if (preg_match('/^\d{4}$/', $key)) {
                        $record['days'][$key] = $value;
                    }
                }
                $this->mapPaycodes($record);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to fetch monthly punch report', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getTimeCardReport(array $params = [])
    {
        try {
            $params = $this->withDefaultPagination($this->withAllDepartments($params));
            $query = http_build_query(array_merge([
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'areas' => -1,
                'groups' => -1,
                'employees' => -1,
            ], $params));

            $url = "{$this->baseUrl}/att/api/timeCardReport/?$query";

            $response = Http::withHeaders($this->withAuthHeaders())->get($url)->json();

            foreach ($response['data'] ?? [] as &$record) {
                $punches = explode(',', $record['punch_set'] ?? '');
                $record['check_in'] = $punches[0] ?? null;
                $record['check_out'] = count($punches) > 1 ? end($punches) : null;
                $this->mapPaycodes($record);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to fetch time card report', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getEmployees(array $filters = [])
    {
        $query = http_build_query($filters);
        $url = "{$this->baseUrl}/personnel/api/employees/?$query";

        return Http::withHeaders($this->withAuthHeaders())->get($url)->json();
    }

    public function getEmployee(int $id)
    {
        $url = "{$this->baseUrl}/personnel/api/employees/{$id}/";
        return Http::withHeaders($this->withAuthHeaders())->get($url)->json();
    }

    public function createEmployee(array $payload)
    {
        return Http::withHeaders($this->withAuthHeaders())
            ->post("{$this->baseUrl}/personnel/api/employees/", $payload)
            ->json();
    }

    public function updateEmployee(int $id, array $payload)
    {
        return Http::withHeaders($this->withAuthHeaders())
            ->patch("{$this->baseUrl}/personnel/api/employees/{$id}/", $payload)
            ->json();
    }

    public function deleteEmployee(int $id)
    {
        return Http::withHeaders($this->withAuthHeaders())
            ->delete("{$this->baseUrl}/personnel/api/employees/{$id}/")
            ->json();
    }

    public function getAttendanceSummary(array $params = [])
    {
        try {
            $params = $this->withDefaultPagination($this->withAllDepartments($params));
            $query = http_build_query(array_merge([
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'areas' => -1,
                'groups' => -1,
                'employees' => -1,
            ], $params));

            $url = "{$this->baseUrl}/att/api/empSummaryReport/?$query";

            return Http::withHeaders($this->withAuthHeaders())->get($url)->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch attendance summary', ['error' => $e->getMessage()]);
            return null;
        }
    }


    public function getDailyTimeCardReport(array $params = [])
    {
        try {
            $params = $this->withDefaultPagination($this->withAllDepartments($params));
            $query = http_build_query(array_merge([
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'areas' => -1,
                'groups' => -1,
                'employees' => -1,
            ], $params));

            $url = "{$this->baseUrl}/att/api/totalTimeCardReportV2/?$query";

            return Http::withHeaders($this->withAuthHeaders())->get($url)->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch daily time card report', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getScheduledPunchReport(array $params = [])
    {
        try {
            $params = $this->withDefaultPagination($this->withAllDepartments($params));
            $query = http_build_query(array_merge([
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'areas' => -1,
                'groups' => -1,
                'employees' => -1,
            ], $params));

            $url = "{$this->baseUrl}/att/api/scheduledPunchReport/?$query";

            return Http::withHeaders($this->withAuthHeaders())->get($url)->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch scheduled punch report', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
