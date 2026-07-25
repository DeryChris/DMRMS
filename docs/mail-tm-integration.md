# Mail.tm Integration

**Purpose:** Create disposable email inboxes on-demand to verify that the Gmail SMTP
mailer (or any other mail driver) is sending emails correctly during development
and testing.

**Service class:** `App\Services\MailTmService`

**API docs:** https://docs.mail.tm

---

## How It Works

```
┌──────────────┐     Gmail SMTP     ┌──────────────────┐     poll via API    ┌─────────┐
│  DMRMS App   │ ──────────────────> │  mail.tm Inbox   │ <────────────────── │  Test   │
│  sends email │                    │  temp@domain      │                    │  Suite  │
└──────────────┘                    └──────────────────┘                    └─────────┘
```

1. The app sends a real email via Gmail SMTP (or `log` driver) to a mail.tm address
2. mail.tm catches that email in its disposable inbox
3. `MailTmService` polls the mail.tm API and retrieves the message
4. Test assertions verify subject, recipient, body content

---

## Api Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| `GET` | `/domains` | List available email domains |
| `POST` | `/accounts` | Create a disposable inbox |
| `POST` | `/token` | Get Bearer token |
| `GET` | `/me` | Get account info |
| `GET` | `/messages` | List received messages |
| `GET` | `/messages/{id}` | Get a single message |
| `PATCH` | `/messages/{id}` | Mark as read |
| `DELETE` | `/messages/{id}` | Delete a message |
| `DELETE` | `/accounts/{id}` | Delete the inbox |

**Auth:** `Authorization: Bearer <token>` (except `/accounts` and `/token`)

**Rate limit:** 8 requests/second

---

## Service Methods

| Method | Signature | Description |
|---|---|---|
| `getDomains()` | `(): array` | Returns available `@domain` list |
| `getDomain()` | `(?string $domainId): ?string` | Gets a domain string by ID or picks the first available |
| `createInbox()` | `(?string $username, ?string $password): ?array` | Creates a new disposable inbox with random credentials |
| `getToken()` | `(string $address, string $password): ?string` | Authenticates and returns JWT |
| `getMe()` | `(string $token): ?array` | Returns account info |
| `getMessages()` | `(string $token, int $page): array` | Lists messages in the inbox |
| `getMessage()` | `(string $messageId, string $token): ?array` | Gets a single message with headers |
| `getMessageHtml()` | `(string $messageId, string $token): ?string` | Extracts the HTML body |
| `getMessageText()` | `(string $messageId, string $token): ?string` | Extracts the plain text body |
| `waitForMessage()` | `(string $token, int $timeout, int $interval): ?array` | Polls every `$interval` seconds until a message arrives or `$timeout` expires |
| `deleteMessage()` | `(string $messageId, string $token): bool` | Deletes a single message |
| `markAsRead()` | `(string $messageId, string $token): bool` | Marks a message as read |
| `deleteInbox()` | `(string $accountId, string $token): bool` | Deletes the inbox (cleanup) |
| `createInboxAndWait()` | `(?string $subject, int $timeout, ?string $username, ?string $password): ?array` | One-shot: create inbox, auth, wait for message, return everything |

---

## Usage Examples

### 1. Tinker — Manual Test

```bash
php artisan tinker
```

```php
$tm = app(App\Services\MailTmService::class);

// Create inbox
$inbox = $tm->createInbox();
$inbox['address'];
// "dmrms_a1b2c3d4e5f6@web-library.net"

// Now trigger an email from the app
// (register an applicant with that email, or run a job)

// Authenticate and wait for the message
$token = $tm->getToken($inbox['address'], $inbox['password']);
$msg = $tm->waitForMessage($token, timeout: 30);

// Inspect
$msg['subject'];               // "Your Verification Code"
$tm->getMessageHtml($msg['id'], $token);  // "<html>..."
$tm->getMessageText($msg['id'], $token);  // "Your code is: 123456"

// Clean up
$tm->deleteInbox($inbox['id'], $token);
```

### 2. PHPUnit Feature Test

```php
<?php

use App\Services\MailTmService;
use App\Models\User;

test('registration email is sent to the applicant', function () {
    $tm = app(MailTmService::class);
    $inbox = $tm->createInbox();
    $token = $tm->getToken($inbox['address'], $inbox['password']);

    // Register with the mail.tm address
    $response = $this->post('/register', [
        'email' => $inbox['address'],
        // ... other fields
    ]);

    $response->assertStatus(201);

    // Wait for the verification email
    $msg = $tm->waitForMessage($token, timeout: 15);

    expect($msg)->not->toBeNull();
    expect($msg['subject'])->toContain('Verification');
    expect($tm->getMessageHtml($msg['id'], $token))->toContain('code');

    // Clean up
    $tm->deleteInbox($inbox['id'], $token);
});
```

### 3. One-Shot Convenience

```php
$result = $tm->createInboxAndWait(timeout: 30);

if ($result['message']) {
    echo $result['message']['subject'];  // email subject
    echo $result['message']['html'];     // email body HTML
} else {
    echo 'No message received within 30 seconds';
}

// Always clean up
$tm->deleteInbox($result['inbox']['id'], $result['token']);
```

---

## Configuration

No API key or config needed. mail.tm is fully free and open.

For tests that use mail.tm, set `MAIL_MAILER=smtp` in `.env` (or `log`) so the app
sends emails that mail.tm can catch. The Gmail SMTP configuration already in
`.env` works as-is.

---

## Rate Limiting

mail.tm allows **8 requests per second**. The `waitForMessage()` method polls
every 3 seconds by default, well within the limit. If you call `getMessages()`
rapidly in a loop, add `usleep(200_000)` between calls.

---

## Cleanup

Always delete the inbox after your test or manual session:

```php
$tm->deleteInbox($inbox['id'], $token);
```

mail.tm may auto-delete inactive inboxes after some time, but explicit cleanup
is recommended to avoid accumulating accounts.
