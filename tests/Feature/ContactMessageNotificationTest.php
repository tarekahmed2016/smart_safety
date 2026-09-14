<?php

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Services\ContactMessageNotificationService;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

function notificationContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'John Visitor',
        'email' => 'visitor@example.com',
        'phone' => '0500000000',
        'subject' => 'General Inquiry',
        'message' => 'Hello, I would like more information.',
    ], $overrides);
}

test('contact submission notifies both configured recipients once', function () {
    config([
        'contact.notification_emails' => 'tarekahmedsalah@gmail.com, info@creativesindustry.com, tarekahmedsalah@gmail.com',
    ]);

    Mail::fake();

    $this->post(route('contact.store'), notificationContactPayload())
        ->assertRedirect()
        ->assertSessionHas('success', 'contact_message_sent');

    expect(ContactMessage::count())->toBe(1);

    Mail::assertSent(ContactMessageReceived::class, 1);
    Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
        expect($mail->hasTo('tarekahmedsalah@gmail.com'))->toBeTrue()
            ->and($mail->hasTo('info@creativesindustry.com'))->toBeTrue()
            ->and($mail->envelope()->subject)->toContain('رسالة تواصل')
            ->and($mail->envelope()->subject)->toContain('John Visitor')
            ->and($mail->content()->html)->toBe('emails.contact-message')
            ->and($mail->content()->markdown)->toBeNull();

        $html = $mail->render();

        expect($html)->toContain('dir="rtl"')
            ->and($html)->toContain('dir="ltr"')
            ->and($html)->toContain('رسالة تواصل جديدة')
            ->and($html)->toContain('John Visitor')
            ->and($html)->toContain('visitor@example.com')
            ->and($html)->toContain('0500000000')
            ->and($html)->toContain('General Inquiry')
            ->and($html)->toContain('Hello, I would like more information.')
            ->and($html)->toContain('طلب جديد')
            ->and($html)->not->toContain('**')
            ->and($html)->not->toContain('# رسالة')
            ->and($html)->not->toContain('<x-mail');

        return true;
    });
});

test('product inquiry notification includes request type and product name', function () {
    config([
        'contact.notification_emails' => 'tarekahmedsalah@gmail.com,info@creativesindustry.com',
    ]);

    Mail::fake();

    $this->post(route('contact.store'), notificationContactPayload([
        'subject' => 'Inquiry about Steel Pipe',
        'message' => "Please send details about Steel Pipe.\nSecond line.",
    ]))->assertRedirect();

    Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
        $html = $mail->render();

        expect($mail->envelope()->subject)->toContain('استفسار منتج')
            ->and($mail->hasTo('tarekahmedsalah@gmail.com'))->toBeTrue()
            ->and($mail->hasTo('info@creativesindustry.com'))->toBeTrue()
            ->and($mail->content()->html)->toBe('emails.contact-message')
            ->and($html)->toContain('Steel Pipe')
            ->and($html)->toContain('Please send details about Steel Pipe.')
            ->and($html)->toContain('Second line.')
            ->and($html)->toContain('<br>')
            ->and($html)->not->toContain('**');

        return true;
    });
});

test('quote request notification uses quote type in the subject', function () {
    config([
        'contact.notification_emails' => 'tarekahmedsalah@gmail.com,info@creativesindustry.com',
    ]);

    Mail::fake();

    $this->post(route('contact.store'), notificationContactPayload([
        'subject' => 'طلب عرض سعر',
        'message' => 'أحتاج عرض سعر للمنتجات.',
    ]))->assertRedirect();

    Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
        expect($mail->envelope()->subject)->toContain('طلب عرض سعر');

        return true;
    });
});

test('duplicate notification emails are sent only once', function () {
    $service = app(ContactMessageNotificationService::class);

    config([
        'contact.notification_emails' => 'TarekAhmedSalah@gmail.com, info@creativesindustry.com, tarekahmedsalah@gmail.com',
    ]);

    expect($service->recipients())->toBe([
        'tarekahmedsalah@gmail.com',
        'info@creativesindustry.com',
    ]);
});

test('html notification escapes user input and preserves line breaks', function () {
    config([
        'contact.notification_emails' => 'tarekahmedsalah@gmail.com,info@creativesindustry.com',
    ]);

    Mail::fake();

    $this->post(route('contact.store'), notificationContactPayload([
        'name' => 'Ali <script>alert(1)</script>',
        'message' => "Line one\nLine two",
    ]))->assertRedirect();

    Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
        $html = $mail->render();

        expect($mail->content()->html)->toBe('emails.contact-message')
            ->and($html)->toContain('Ali &lt;script&gt;alert(1)&lt;/script&gt;')
            ->and($html)->not->toContain('<script>alert(1)</script>')
            ->and($html)->toContain('Line one')
            ->and($html)->toContain('Line two')
            ->and($html)->toContain('<br>');

        return true;
    });
});

test('failed notification email does not prevent saving the contact message', function () {
    config([
        'contact.notification_emails' => 'tarekahmedsalah@gmail.com,info@creativesindustry.com',
    ]);

    $logged = [];

    Log::listen(function ($message) use (&$logged) {
        $logged[] = $message;
    });

    $pendingMail = Mockery::mock(PendingMail::class);
    $pendingMail->shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP failed'));

    Mail::shouldReceive('to')
        ->once()
        ->with(['tarekahmedsalah@gmail.com', 'info@creativesindustry.com'])
        ->andReturn($pendingMail);

    $this->post(route('contact.store'), notificationContactPayload([
        'name' => 'Saved Despite Mail Failure',
    ]))
        ->assertRedirect()
        ->assertSessionHas('success', 'contact_message_sent');

    expect(ContactMessage::where('name', 'Saved Despite Mail Failure')->exists())->toBeTrue()
        ->and(collect($logged)->contains(function ($entry) {
            return ($entry->level ?? null) === 'error'
                && str_contains((string) $entry->message, 'Failed to send contact message notification email.');
        }))->toBeTrue();
});
