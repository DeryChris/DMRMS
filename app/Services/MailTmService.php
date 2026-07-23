<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MailTmService
{
    protected string $baseUrl = 'https://api.mail.tm';

    public function getDomains(): array
    {
        $response = Http::get("{$this->baseUrl}/domains");

        if ($response->failed()) {
            Log::error('Mail.tm: failed to fetch domains', ['status' => $response->status()]);
            return [];
        }

        return $response->json('hydra:member', []);
    }

    public function getDomain(?string $domainId = null): ?string
    {
        if ($domainId) {
            $response = Http::get("{$this->baseUrl}/domains/{$domainId}");
            if ($response->successful()) {
                return $response->json('domain');
            }
        }

        $domains = $this->getDomains();
        if (empty($domains)) {
            return null;
        }

        return $domains[0]['domain'] ?? null;
    }

    public function createInbox(?string $username = null, ?string $password = null): ?array
    {
        $domain = $this->getDomain();
        if (!$domain) {
            Log::error('Mail.tm: no domain available to create inbox');
            return null;
        }

        $username = $username ?? 'dmrms_' . Str::random(12);
        $password = $password ?? Str::random(16);
        $address = "{$username}@{$domain}";

        $response = Http::post("{$this->baseUrl}/accounts", [
            'address' => $address,
            'password' => $password,
        ]);

        if ($response->failed()) {
            Log::error('Mail.tm: failed to create inbox', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return [
            'id'       => $response->json('id'),
            'address'  => $response->json('address'),
            'password' => $password,
            'domain'   => $domain,
        ];
    }

    public function getToken(string $address, string $password): ?string
    {
        $response = Http::post("{$this->baseUrl}/token", [
            'address'  => $address,
            'password' => $password,
        ]);

        if ($response->failed()) {
            Log::error('Mail.tm: failed to get token', ['status' => $response->status()]);
            return null;
        }

        return $response->json('token');
    }

    public function getAccountId(string $address, string $password): ?string
    {
        $response = Http::post("{$this->baseUrl}/token", [
            'address'  => $address,
            'password' => $password,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('id');
    }

    public function getMe(string $token): ?array
    {
        $response = Http::withToken($token)->get("{$this->baseUrl}/me");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    public function getMessages(string $token, int $page = 1): array
    {
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/messages", ['page' => $page]);

        if ($response->failed()) {
            Log::error('Mail.tm: failed to fetch messages', ['status' => $response->status()]);
            return [];
        }

        return $response->json('hydra:member', []);
    }

    public function getMessage(string $messageId, string $token): ?array
    {
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/messages/{$messageId}");

        if ($response->failed()) {
            Log::error('Mail.tm: failed to fetch message', [
                'id'     => $messageId,
                'status' => $response->status(),
            ]);
            return null;
        }

        return $response->json();
    }

    public function waitForMessage(string $token, int $timeout = 60, int $interval = 3): ?array
    {
        $start = time();

        while (time() - $start < $timeout) {
            $messages = $this->getMessages($token);

            if (!empty($messages)) {
                $msgId = $messages[0]['id'] ?? null;
                if ($msgId) {
                    return $this->getMessage($msgId, $token);
                }
            }

            sleep($interval);
        }

        Log::warning('Mail.tm: waitForMessage timed out', ['timeout' => $timeout]);
        return null;
    }

    public function getMessageHtml(string $messageId, string $token): ?string
    {
        $msg = $this->getMessage($messageId, $token);
        if (!$msg) {
            return null;
        }

        $htmlParts = $msg['html'] ?? [];

        if (is_array($htmlParts)) {
            foreach ($htmlParts as $part) {
                if (isset($part['html'])) {
                    return $part['html'];
                }
            }
        }

        return $msg['html'] ?? null;
    }

    public function getMessageText(string $messageId, string $token): ?string
    {
        $msg = $this->getMessage($messageId, $token);
        if (!$msg) {
            return null;
        }

        $textParts = $msg['text'] ?? [];

        if (is_array($textParts)) {
            foreach ($textParts as $part) {
                if (isset($part['text'])) {
                    return $part['text'];
                }
            }
        }

        return $msg['text'] ?? null;
    }

    public function deleteMessage(string $messageId, string $token): bool
    {
        $response = Http::withToken($token)
            ->delete("{$this->baseUrl}/messages/{$messageId}");

        return $response->successful();
    }

    public function markAsRead(string $messageId, string $token): bool
    {
        $response = Http::withToken($token)
            ->patch("{$this->baseUrl}/messages/{$messageId}", [
                'seen' => true,
            ]);

        return $response->successful();
    }

    public function deleteInbox(string $accountId, string $token): bool
    {
        $response = Http::withToken($token)
            ->delete("{$this->baseUrl}/accounts/{$accountId}");

        if ($response->successful()) {
            Log::info('Mail.tm: inbox deleted', ['account_id' => $accountId]);
            return true;
        }

        Log::error('Mail.tm: failed to delete inbox', [
            'account_id' => $accountId,
            'status'     => $response->status(),
        ]);
        return false;
    }

    public function createInboxAndWait(string $subject = null, int $timeout = 60, ?string $username = null, ?string $password = null): ?array
    {
        $inbox = $this->createInbox($username, $password);
        if (!$inbox) {
            return null;
        }

        $token = $this->getToken($inbox['address'], $inbox['password']);
        if (!$token) {
            return null;
        }

        $message = $this->waitForMessage($token, $timeout);

        if ($message) {
            $message['html'] = $this->getMessageHtml($message['id'], $token);
            $message['text'] = $this->getMessageText($message['id'], $token);
        }

        return [
            'inbox'    => $inbox,
            'token'    => $token,
            'message'  => $message,
        ];
    }
}
