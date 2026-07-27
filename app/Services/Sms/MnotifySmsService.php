<?php

namespace App\Services\Sms;

use App\Models\SmsLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MnotifySmsService
{
    protected string $apiKey;
    protected string $senderId;
    protected string $baseUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiKey   = config('services.mnotify.api_key');
        $this->senderId = config('services.mnotify.sender_id', 'DMRMS');
        $this->baseUrl  = config('services.mnotify.base_url', 'https://api.mnotify.com/api');
        $this->enabled  = config('services.mnotify.enabled', false);
    }

    /**
     * Send a single SMS via mNotify API.
     *
     * @param  string  $phone    Ghanaian phone number (e.g. 0241234567)
     * @param  string  $message  Message content (max 460 chars)
     * @param  bool    $isOtp    Whether this is an OTP message (uses OTP-optimized route)
     * @return SmsLog
     *
     * @throws \RuntimeException When the API call fails or returns an error
     */
    public function send(string $phone, string $message, bool $isOtp = false): SmsLog
    {
        // Trim leading + if present
        $phone = ltrim($phone, '+');

        // Create a pending log entry
        $smsLog = SmsLog::create([
            'phone'    => $phone,
            'message'  => $message,
            'is_otp'   => $isOtp,
            'status'   => 'pending',
        ]);

        if (!$this->enabled || empty($this->apiKey)) {
            $smsLog->update([
                'status'            => 'failed',
                'provider_response' => 'SMS disabled or API key not configured',
            ]);
            Log::channel('sms')->warning('[MNotify] SMS skipped — service disabled or missing API key', [
                'phone' => $phone,
                'log_id' => $smsLog->id,
            ]);
            return $smsLog;
        }

        // Build the payload
        $payload = [
            'recipient'    => [$phone],
            'sender'       => $this->senderId,
            'message'      => $message,
            'is_schedule'  => false,
            'schedule_date' => '',
        ];

        if ($isOtp) {
            $payload['sms_type'] = 'otp';
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/sms/quick?key={$this->apiKey}", $payload);

            $body = $response->json() ?? [];
            $statusCode = $response->status();

            $campaignId = $body['summary']['message_id'] ?? ($body['summary']['_id'] ?? null);
            $providerMsg = $body['message'] ?? ($body['status'] ?? 'unknown');

            if ($response->successful() && ((int) ($body['code'] ?? 0)) === 2000) {
                $smsLog->update([
                    'campaign_id'        => $campaignId,
                    'status'             => 'sent',
                    'provider_response'  => json_encode($body),
                    'sent_at'            => Carbon::now(),
                ]);

                Log::channel('sms')->info('[MNotify] SMS sent successfully', [
                    'phone'      => $phone,
                    'log_id'     => $smsLog->id,
                    'campaign_id' => $campaignId,
                    'is_otp'     => $isOtp,
                ]);
            } else {
                $smsLog->update([
                    'status'             => 'failed',
                    'provider_response'  => json_encode($body),
                ]);

                Log::channel('sms')->error('[MNotify] SMS send failed', [
                    'phone'        => $phone,
                    'log_id'       => $smsLog->id,
                    'http_status'  => $statusCode,
                    'response'     => $body,
                ]);

                throw new \RuntimeException(
                    "mNotify send failed (HTTP {$statusCode}): {$providerMsg}"
                );
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $smsLog->update([
                'status'             => 'failed',
                'provider_response'  => 'Connection error: ' . $e->getMessage(),
            ]);

            Log::channel('sms')->error('[MNotify] Connection error', [
                'phone'  => $phone,
                'log_id' => $smsLog->id,
                'error'  => $e->getMessage(),
            ]);

            throw new \RuntimeException('Could not connect to mNotify API: ' . $e->getMessage(), 0, $e);
        }

        return $smsLog->fresh();
    }

    /**
     * Check the SMS wallet balance.
     *
     * @return float
     */
    public function getBalance(): float
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return 0.0;
        }

        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/balance/sms?key={$this->apiKey}");

            $body = $response->json();

            if ($response->successful()) {
                // Response can be: {"status":"success","balance":4000,"bonus":70}
                // or a simple numeric value
                if (isset($body['balance'])) {
                    return (float) $body['balance'];
                }
                if (is_numeric($body)) {
                    return (float) $body;
                }
            }

            Log::channel('sms')->warning('[MNotify] Balance check failed', [
                'status' => $response->status(),
                'response' => $body,
            ]);

            return 0.0;
        } catch (\Exception $e) {
            Log::channel('sms')->error('[MNotify] Balance check error', [
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Get delivery report for a campaign.
     *
     * @param  string  $campaignId  The campaign _id returned from send()
     * @return array
     */
    public function getDeliveryReport(string $campaignId): array
    {
        if (!$this->enabled || empty($this->apiKey)) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/sms/report/{$campaignId}?key={$this->apiKey}");

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::channel('sms')->error('[MNotify] Delivery report error', [
                'campaign_id' => $campaignId,
                'error'       => $e->getMessage(),
            ]);

            return [];
        }
    }
}
