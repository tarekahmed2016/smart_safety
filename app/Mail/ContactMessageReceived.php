<?php

namespace App\Mail;

use App\Enums\ContactMessages\RequestStatus;
use App\Models\ContactMessage;
use App\Support\ContactMessageClassifier;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessageReceived extends Mailable
{
    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        $kind = ContactMessageClassifier::kind($this->contactMessage);
        $name = trim((string) $this->contactMessage->name) ?: 'Visitor';

        return new Envelope(
            subject: $kind->labelAr().' — '.$name,
        );
    }

    public function content(): Content
    {
        $status = $this->contactMessage->request_status instanceof RequestStatus
            ? $this->contactMessage->request_status
            : RequestStatus::tryFrom((string) $this->contactMessage->request_status) ?? RequestStatus::New;

        $kind = ContactMessageClassifier::kind($this->contactMessage);
        $sentAt = ($this->contactMessage->created_at ?? now())
            ->timezone(config('app.timezone'))
            ->format('Y-m-d H:i');

        return new Content(
            html: 'emails.contact-message',
            with: [
                'kindLabelAr' => $kind->labelAr(),
                'kindLabelEn' => $kind->labelEn(),
                'productName' => ContactMessageClassifier::productName($this->contactMessage),
                'statusLabelAr' => $status->labelAr(),
                'statusLabelEn' => $status->labelEn(),
                'sentAt' => $sentAt,
            ],
        );
    }
}
