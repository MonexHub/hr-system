<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BeemService
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $source;

    public function __construct()
    {
        $this->apiKey = config('services.beem.api_key');
        $this->secretKey = config('services.beem.secret_key');
        $this->source = config('services.beem.sender_name');
    }

    public function sendSMS(string $recipient, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->secretKey),
            ])->post('https://apisms.beem.africa/v1/send', [
                'source_addr' => $this->source,
                'schedule_time' => '',
                'encoding' => 0,
                'message' => $message,
                'recipients' => [
                    [
                        'recipient_id' => 1,
                        'dest_addr' => $this->formatPhoneNumber($recipient),
                    ],
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'SMS sending failed: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'SMS sending failed: ' . $e->getMessage(),
            ];
        }
    }

    protected function formatPhoneNumber(string $number): string
    {
        // Remove any spaces, dashes, or plus signs
        $number = preg_replace('/[\s+-]/', '', $number);

        // If number starts with 0, replace with 255
        if (str_starts_with($number, '0')) {
            $number = '255' . substr($number, 1);
        }

        // If number doesn't start with 255, add it
        if (!str_starts_with($number, '255')) {
            $number = '255' . $number;
        }

        return $number;
    }
}
