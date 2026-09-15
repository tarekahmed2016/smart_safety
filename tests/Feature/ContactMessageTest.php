<?php

use App\Enums\ActivityLogs\Event;
use App\Enums\ContactMessages\RequestStatus;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'John Visitor',
        'email' => 'visitor@example.com',
        'phone' => '0500000000',
        'subject' => 'General Inquiry',
        'message' => 'Hello, I would like more information.',
    ], $overrides);
}

test('guest can submit a valid contact message', function () {
    $this->post(route('contact.store'), validContactPayload())
        ->assertRedirect()
        ->assertSessionHas('success', 'contact_message_sent');

    $message = ContactMessage::first();

    expect($message)->not->toBeNull()
        ->and($message->name)->toBe('John Visitor')
        ->and($message->email)->toBe('visitor@example.com')
        ->and($message->phone)->toBe('0500000000')
        ->and($message->subject)->toBe('General Inquiry')
        ->and($message->message)->toBe('Hello, I would like more information.')
        ->and($message->is_read)->toBeFalse()
        ->and($message->read_at)->toBeNull();
});

test('authenticated user can submit a contact message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('contact.store'), validContactPayload([
            'name' => 'Logged Visitor',
        ]))
        ->assertRedirect();

    expect(ContactMessage::where('name', 'Logged Visitor')->exists())->toBeTrue();
});

test('contact submission requires name', function () {
    $this->post(route('contact.store'), validContactPayload(['name' => '']))
        ->assertSessionHasErrors('name');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission requires message', function () {
    $this->post(route('contact.store'), validContactPayload(['message' => '']))
        ->assertSessionHasErrors('message');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission rejects invalid email', function () {
    $this->post(route('contact.store'), validContactPayload(['email' => 'not-an-email']))
        ->assertSessionHasErrors('email');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission rejects too long fields', function () {
    $this->post(route('contact.store'), validContactPayload([
        'name' => str_repeat('a', 256),
    ]))->assertSessionHasErrors('name');

    $this->post(route('contact.store'), validContactPayload([
        'message' => str_repeat('a', 5001),
    ]))->assertSessionHasErrors('message');
});

test('contact submission requires email', function () {
    $this->post(route('contact.store'), validContactPayload(['email' => '']))
        ->assertSessionHasErrors('email');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission requires phone', function () {
    $this->post(route('contact.store'), validContactPayload(['phone' => '']))
        ->assertSessionHasErrors('phone');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission requires subject', function () {
    $this->post(route('contact.store'), validContactPayload(['subject' => '']))
        ->assertSessionHasErrors('subject');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission rejects empty fields', function () {
    $this->post(route('contact.store'), [
        'name' => '',
        'email' => '',
        'phone' => '',
        'subject' => '',
        'message' => '',
    ])->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission rejects invalid phone', function () {
    $this->post(route('contact.store'), validContactPayload(['phone' => 'abc']))
        ->assertSessionHasErrors('phone');

    expect(ContactMessage::count())->toBe(0);
});

test('contact submission requires recaptcha when enabled', function () {
    config([
        'services.recaptcha.site_key' => 'test-site-key',
        'services.recaptcha.secret' => 'test-secret',
    ]);

    $this->post(route('contact.store'), validContactPayload())
        ->assertSessionHasErrors('g-recaptcha-response');

    expect(ContactMessage::count())->toBe(0);

    $this->post(route('contact.store'), validContactPayload([
        'g-recaptcha-response' => 'token',
    ]))->assertRedirect();

    expect(ContactMessage::count())->toBe(1);
});

test('contact validation errors are bilingual', function () {
    $this->post(route('contact.store'), validContactPayload(['name' => '']))
        ->assertSessionHasErrors('name');

    $message = session('errors')->first('name');

    expect($message)->toContain('الاسم الكامل')
        ->and($message)->toContain('Please enter your full name.');
});

test('public cannot set is_read on submission', function () {
    $this->post(route('contact.store'), validContactPayload([
        'is_read' => true,
    ]))->assertRedirect();

    expect(ContactMessage::first()->is_read)->toBeFalse();
});

test('public cannot set read_at on submission', function () {
    $this->post(route('contact.store'), validContactPayload([
        'read_at' => now()->toDateTimeString(),
    ]))->assertRedirect();

    expect(ContactMessage::first()->read_at)->toBeNull();
});

test('contact route uses web middleware with csrf protection', function () {
    $route = Route::getRoutes()->getByName('contact.store');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('web');
});

test('contact submissions are rate limited', function () {
    RateLimiter::clear('127.0.0.1');

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('contact.store'), validContactPayload([
            'name' => "Visitor {$i}",
            'email' => "visitor{$i}@example.com",
        ]))->assertRedirect();
    }

    $this->post(route('contact.store'), validContactPayload([
        'name' => 'Blocked Visitor',
        'email' => 'blocked@example.com',
    ]))->assertStatus(429);
});

test('html content is stored as plain text', function () {
    $payload = validContactPayload([
        'message' => '<script>alert("xss")</script><b>Hello</b>',
    ]);

    $this->post(route('contact.store'), $payload)->assertRedirect();

    expect(ContactMessage::first()->message)->toBe('<script>alert("xss")</script><b>Hello</b>');
});

test('mass assignment attack fields are ignored on submission', function () {
    $this->post(route('contact.store'), validContactPayload([
        'id' => 999,
        'created_at' => '2000-01-01 00:00:00',
        'updated_at' => '2000-01-01 00:00:00',
    ]))->assertRedirect();

    $message = ContactMessage::first();

    expect($message->id)->not->toBe(999)
        ->and($message->created_at->isToday())->toBeTrue();
});

test('guest contact message defaults to new request status', function () {
    $this->post(route('contact.store'), validContactPayload())->assertRedirect();

    $message = ContactMessage::first();

    expect($message->request_status)->toBe(RequestStatus::New)
        ->and($message->is_read)->toBeFalse();
});

test('public cannot set request status on submission', function () {
    $this->post(route('contact.store'), validContactPayload([
        'request_status' => RequestStatus::Completed->value,
    ]))->assertRedirect();

    expect(ContactMessage::first()->request_status)->toBe(RequestStatus::New);
});

test('non admin cannot update contact request status', function () {
    $message = ContactMessage::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('contact-messages.request-status', $message), [
            'request_status' => RequestStatus::Agreed->value,
        ])
        ->assertRedirect(route('login'));

    expect($message->fresh()->request_status)->toBe(RequestStatus::New);
});

test('guest cannot view admin contact messages', function () {
    ContactMessage::factory()->create();

    $this->get(route('contact-messages.index'))
        ->assertRedirect(route('login'));
});

test('non admin cannot view admin contact messages', function () {
    ContactMessage::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('contact-messages.index'))
        ->assertRedirect(route('login'));
});

test('non admin cannot mark contact message as read', function () {
    $message = ContactMessage::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('contact-messages.read', $message))
        ->assertRedirect(route('login'));

    expect($message->fresh()->is_read)->toBeFalse();
});

test('non admin cannot mark contact message as unread', function () {
    $message = ContactMessage::factory()->read()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('contact-messages.unread', $message))
        ->assertRedirect(route('login'));
});

test('non admin cannot delete contact message', function () {
    $message = ContactMessage::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('contact-messages.destroy', $message))
        ->assertRedirect(route('login'));

    expect(ContactMessage::find($message->id))->not->toBeNull();
});

test('admin can view contact messages index', function () {
    ContactMessage::factory()->create([
        'name' => 'Admin Listed Visitor',
        'message' => 'Please call me back.',
    ]);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ContactMessages/ContactMessagesPage', false)
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Admin Listed Visitor')
            ->where('contactMessages.data.0.message', 'Please call me back.')
            ->where('contactMessages.data.0.request_status', RequestStatus::New->value)
            ->has('requestStatuses'));
});

test('admin can mark contact message as read', function () {
    $message = ContactMessage::factory()->unread()->create(['name' => 'Read Me']);

    $this->actingAs($this->admin)
        ->put(route('contact-messages.read', $message))
        ->assertRedirect();

    $message->refresh();

    expect($message->is_read)->toBeTrue()
        ->and($message->read_at)->not->toBeNull();
});

test('admin can mark contact message as unread', function () {
    $message = ContactMessage::factory()->read()->create(['name' => 'Unread Me']);

    $this->actingAs($this->admin)
        ->put(route('contact-messages.unread', $message))
        ->assertRedirect();

    $message->refresh();

    expect($message->is_read)->toBeFalse()
        ->and($message->read_at)->toBeNull();
});

test('admin can delete contact message', function () {
    $message = ContactMessage::factory()->create(['name' => 'Delete Me']);

    $this->actingAs($this->admin)
        ->delete(route('contact-messages.destroy', $message))
        ->assertRedirect();

    expect(ContactMessage::find($message->id))->toBeNull();
});

test('contact messages are listed newest first', function () {
    $older = ContactMessage::factory()->create([
        'name' => 'Older Message',
        'created_at' => now()->subDay(),
    ]);
    $newer = ContactMessage::factory()->create([
        'name' => 'Newer Message',
        'created_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contactMessages.data.0.id', $newer->id)
            ->where('contactMessages.data.1.id', $older->id));
});

test('contact messages search finds name', function () {
    ContactMessage::factory()->create(['name' => 'Unique Name Visitor']);
    ContactMessage::factory()->create(['name' => 'Other Visitor']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['search' => 'Unique Name']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Unique Name Visitor'));
});

test('contact messages search finds email', function () {
    ContactMessage::factory()->create(['email' => 'unique-search@example.com']);
    ContactMessage::factory()->create(['email' => 'other@example.com']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['search' => 'unique-search']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.email', 'unique-search@example.com'));
});

test('contact messages search finds phone', function () {
    ContactMessage::factory()->phoneOnly()->create(['phone' => 'PHONE-UNIQUE-AAA']);
    ContactMessage::factory()->phoneOnly()->create(['phone' => 'PHONE-OTHER-BBB']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['search' => 'UNIQUE-AAA']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.phone', 'PHONE-UNIQUE-AAA'));
});

test('contact messages search finds subject', function () {
    ContactMessage::factory()->create(['subject' => 'Unique Subject Line']);
    ContactMessage::factory()->create(['subject' => 'Other Subject']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['search' => 'Unique Subject']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.subject', 'Unique Subject Line'));
});

test('contact messages search finds message body', function () {
    ContactMessage::factory()->create(['message' => 'Unique message body content here']);
    ContactMessage::factory()->create(['message' => 'Different content']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['search' => 'Unique message body']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.message', 'Unique message body content here'));
});

test('contact messages index filters unread only', function () {
    ContactMessage::factory()->unread()->create(['name' => 'Unread Visitor']);
    ContactMessage::factory()->read()->create(['name' => 'Read Visitor']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['status' => 'unread']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Unread Visitor')
            ->where('filters.status', 'unread'));
});

test('contact messages index filters read only', function () {
    ContactMessage::factory()->unread()->create(['name' => 'Unread Visitor']);
    ContactMessage::factory()->read()->create(['name' => 'Read Visitor']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['status' => 'read']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Read Visitor')
            ->where('filters.status', 'read'));
});

test('contact messages index ignores invalid status filter', function () {
    ContactMessage::factory()->unread()->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['status' => 'invalid']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 2)
            ->where('filters.status', 'all'));
});

test('admin can update contact request status', function () {
    $message = ContactMessage::factory()->unread()->create(['name' => 'Status Visitor']);

    $this->actingAs($this->admin)
        ->from(route('contact-messages.index'))
        ->put(route('contact-messages.request-status', $message), [
            'request_status' => RequestStatus::QuoteSent->value,
        ])
        ->assertRedirect();

    $message->refresh();

    expect($message->request_status)->toBe(RequestStatus::QuoteSent)
        ->and($message->is_read)->toBeFalse();
});

test('updated request status is persisted after reopening the list', function () {
    $message = ContactMessage::factory()->create(['name' => 'Persist Status']);

    $this->actingAs($this->admin)
        ->put(route('contact-messages.request-status', $message), [
            'request_status' => RequestStatus::Contacted->value,
        ])
        ->assertRedirect();

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contactMessages.data.0.id', $message->id)
            ->where('contactMessages.data.0.request_status', RequestStatus::Contacted->value)
            ->where('contactMessages.data.0.request_status_formatted.value', RequestStatus::Contacted->value)
            ->where('contactMessages.data.0.request_status_formatted.label', 'تم التواصل مع العميل')
            ->where('contactMessages.data.0.request_status_formatted.label_en', 'Customer contacted'));
});

test('contact messages index filters by request status', function () {
    ContactMessage::factory()->create(['name' => 'New Visitor']);
    ContactMessage::factory()->requestStatus(RequestStatus::Agreed)->create(['name' => 'Agreed Visitor']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['request_status' => RequestStatus::Agreed->value]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Agreed Visitor')
            ->where('filters.request_status', RequestStatus::Agreed->value));
});

test('request status filter can be combined with unread filter', function () {
    ContactMessage::factory()->unread()->requestStatus(RequestStatus::UnderReview)->create(['name' => 'Unread Review']);
    ContactMessage::factory()->read()->requestStatus(RequestStatus::UnderReview)->create(['name' => 'Read Review']);
    ContactMessage::factory()->unread()->create(['name' => 'Unread New']);

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', [
            'status' => 'unread',
            'request_status' => RequestStatus::UnderReview->value,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Unread Review')
            ->where('filters.status', 'unread')
            ->where('filters.request_status', RequestStatus::UnderReview->value));
});

test('changing request status does not change read state', function () {
    $message = ContactMessage::factory()->read()->create();

    $this->actingAs($this->admin)
        ->put(route('contact-messages.request-status', $message), [
            'request_status' => RequestStatus::Rejected->value,
        ])
        ->assertRedirect();

    $message->refresh();

    expect($message->request_status)->toBe(RequestStatus::Rejected)
        ->and($message->is_read)->toBeTrue()
        ->and($message->read_at)->not->toBeNull();
});

test('existing contact messages keep their data and default to new request status', function () {
    $message = ContactMessage::factory()->read()->create([
        'name' => 'Legacy Visitor',
        'email' => 'legacy@example.com',
        'phone' => '123456',
        'subject' => 'Legacy subject',
        'message' => 'Legacy body',
    ]);

    expect($message->fresh()->request_status)->toBe(RequestStatus::New)
        ->and($message->fresh()->name)->toBe('Legacy Visitor')
        ->and($message->fresh()->email)->toBe('legacy@example.com')
        ->and($message->fresh()->phone)->toBe('123456')
        ->and($message->fresh()->subject)->toBe('Legacy subject')
        ->and($message->fresh()->message)->toBe('Legacy body')
        ->and($message->fresh()->is_read)->toBeTrue();

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('contactMessages.data', 1)
            ->where('contactMessages.data.0.name', 'Legacy Visitor')
            ->where('contactMessages.data.0.request_status', RequestStatus::New->value));
});

test('contact messages pagination works', function () {
    ContactMessage::factory()->count(16)->create();

    $this->actingAs($this->admin)
        ->get(route('contact-messages.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contactMessages.current_page', 2)
            ->has('contactMessages.data', 1));
});

test('marking read records safe activity metadata', function () {
    $message = ContactMessage::factory()->unread()->create([
        'name' => 'Activity Visitor',
        'email' => 'secret@example.com',
        'message' => 'Sensitive body content',
    ]);

    $this->actingAs($this->admin)
        ->put(route('contact-messages.read', $message))
        ->assertRedirect();

    $log = ActivityLog::query()->where('event', Event::Updated)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->metadata)->toMatchArray(['action' => 'read'])
        ->and($log->new_values)->toHaveKey('is_read', true)
        ->and($log->new_values)->not->toHaveKey('message')
        ->and($log->new_values)->not->toHaveKey('email')
        ->and($log->new_values)->not->toHaveKey('phone')
        ->and($log->subject_label)->toContain('Activity Visitor');
});

test('deleting contact message records safe activity metadata', function () {
    $message = ContactMessage::factory()->create([
        'name' => 'Deleted Visitor',
        'email' => 'delete@example.com',
        'message' => 'Delete this body',
    ]);

    $this->actingAs($this->admin)
        ->delete(route('contact-messages.destroy', $message))
        ->assertRedirect();

    $log = ActivityLog::query()->where('event', Event::Deleted)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->metadata)->toMatchArray(['action' => 'delete'])
        ->and($log->old_values)->not->toHaveKey('message')
        ->and($log->old_values)->not->toHaveKey('email')
        ->and($log->old_values)->not->toHaveKey('phone');
});

test('public submission does not create admin activity log', function () {
    $before = ActivityLog::count();

    $this->post(route('contact.store'), validContactPayload([
        'name' => 'No Activity Visitor',
    ]))->assertRedirect();

    expect(ActivityLog::count())->toBe($before);
});

test('homepage loads with contact section available', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/HomePage', false));
});

test('homepage works without company contact details', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('companyInfo')
            ->where('companyInfo.phone', fn ($value) => blank($value) || is_string($value)));
});

test('successful contact submission redirects back with flash', function () {
    $this->from(route('home'))
        ->post(route('contact.store'), validContactPayload())
        ->assertRedirect(route('home'))
        ->assertSessionHas('success', 'contact_message_sent');
});
