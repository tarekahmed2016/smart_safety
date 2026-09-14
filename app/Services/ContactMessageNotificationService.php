<?php

namespace App\Services;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactMessageNotificationService
{
    /**
     * @return list<string>
     */
    public function recipients(): array
    {
        $raw = (string) config('contact.notification_emails', '');

        $emails = [];

        foreach (preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $email) {
            $normalized = strtolower(trim($email));

            if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emails[$normalized] = $normalized;
        }

        return array_values($emails);
    }

    public function notify(ContactMessage $contactMessage): void
    {
        $recipients = $this->recipients();

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new ContactMessageReceived($contactMessage));
        } catch (Throwable $exception) {
            Log::error('Failed to send contact message notification email.', [
                'contact_message_id' => $contactMessage->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
