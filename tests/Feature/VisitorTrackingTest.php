<?php

use App\Enums\VisitorEventType;
use App\Models\User;
use App\Models\VisitorEvent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

function visitorRequest(array $cookies = [], string $ip = '203.0.113.50', string $userAgent = 'Mozilla/5.0 TestBrowser'): TestResponse
{
    $test = test()->disableCookieEncryption()
        ->withHeaders(['User-Agent' => $userAgent])
        ->withServerVariables(['REMOTE_ADDR' => $ip]);

    foreach ($cookies as $name => $value) {
        $test->withCookie($name, $value);
    }

    return $test->get(route('home'));
}

function visitorEventCount(?VisitorEventType $type = null): int
{
    return VisitorEvent::query()
        ->when($type, fn ($query) => $query->where('event_type', $type))
        ->count();
}

test('public page view records unique visitor visit and page view', function () {
    visitorRequest()->assertOk()->assertCookie('visitor_id');

    expect(visitorEventCount(VisitorEventType::Visitor))->toBe(1)
        ->and(visitorEventCount(VisitorEventType::Visit))->toBe(1)
        ->and(visitorEventCount(VisitorEventType::PageView))->toBe(1);
});

test('the same visitor is not counted twice in the same day', function () {
    $visitorId = (string) Str::uuid();
    $sessionId = (string) Str::uuid();
    $cookies = [
        'visitor_id' => $visitorId,
        'visitor_sid' => $sessionId,
    ];

    visitorRequest($cookies)->assertOk();
    visitorRequest($cookies)->assertOk();
    visitorRequest($cookies)->assertOk();

    expect(visitorEventCount(VisitorEventType::Visitor))->toBe(1)
        ->and(visitorEventCount(VisitorEventType::Visit))->toBe(1)
        ->and(visitorEventCount(VisitorEventType::PageView))->toBe(3);
});

test('cookieless requests with the same ip and user agent count as one unique visitor', function () {
    visitorRequest()->assertOk();
    visitorRequest()->assertOk();

    expect(visitorEventCount(VisitorEventType::Visitor))->toBe(1)
        ->and(visitorEventCount(VisitorEventType::PageView))->toBe(2);
});

test('a new visit is counted after thirty minutes', function () {
    $visitorId = (string) Str::uuid();

    visitorRequest([
        'visitor_id' => $visitorId,
        'visitor_sid' => (string) Str::uuid(),
    ])->assertOk();

    $this->travel(31)->minutes();

    visitorRequest([
        'visitor_id' => $visitorId,
    ])->assertOk();

    expect(visitorEventCount(VisitorEventType::Visitor))->toBe(1)
        ->and(visitorEventCount(VisitorEventType::Visit))->toBe(2)
        ->and(visitorEventCount(VisitorEventType::PageView))->toBe(2);
});

test('page views increment for each public page', function () {
    $visitorId = (string) Str::uuid();
    $sessionId = (string) Str::uuid();
    $cookies = [
        'visitor_id' => $visitorId,
        'visitor_sid' => $sessionId,
    ];

    visitorRequest($cookies)->assertOk();
    $this->disableCookieEncryption()
        ->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
        ->withCookie('visitor_id', $visitorId)
        ->withCookie('visitor_sid', $sessionId)
        ->get(route('public.products.index'))
        ->assertOk();

    expect(visitorEventCount(VisitorEventType::PageView))->toBe(2)
        ->and(VisitorEvent::query()->where('event_type', VisitorEventType::PageView)->pluck('path'))
        ->toContain('/')
        ->toContain('/catalog');
});

test('authenticated users and dashboard requests are not tracked', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get(route('login'))
        ->assertOk();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get(route('home'))
        ->assertOk();

    $this->actingAs($user)
        ->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get(route('dashboard'))
        ->assertOk();

    expect(visitorEventCount())->toBe(0);
});

test('bots and static files and api requests are excluded', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Googlebot/2.1'])
        ->get(route('home'))
        ->assertOk();

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get('/css/app.css')
        ->assertNotFound();

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get('/js/app.js')
        ->assertNotFound();

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get('/images/logo.png')
        ->assertNotFound();

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->get('/api/visitors')
        ->assertNotFound();

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 TestBrowser'])
        ->post(route('contact.store'), []);

    expect(visitorEventCount())->toBe(0);
});

test('raw ip addresses and full user agents are not stored', function () {
    $ip = '203.0.113.50';
    $userAgent = 'Mozilla/5.0 TestBrowser UniqueAgentString/99.0';

    visitorRequest(ip: $ip, userAgent: $userAgent)->assertOk();

    $stored = json_encode(VisitorEvent::query()->get()->toArray(), JSON_THROW_ON_ERROR);

    expect($stored)->not->toContain($ip)
        ->and($stored)->not->toContain($userAgent)
        ->and(Schema::getColumnListing('visitor_events'))
        ->not->toContain('ip')
        ->not->toContain('ip_address')
        ->not->toContain('user_agent');
});

test('homepage shares the real today visitor count', function () {
    visitorRequest()->assertOk();

    visitorRequest()->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('todayVisitors', 1));
});

test('dashboard and api expose visitor statistics', function () {
    visitorRequest()->assertOk();
    visitorRequest([
        'visitor_id' => (string) Str::uuid(),
        'visitor_sid' => (string) Str::uuid(),
    ], ip: '203.0.113.51')->assertOk();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/IndexPage', false)
            ->where('visitorStats.today.unique_visitors', 2)
            ->where('visitorStats.today.visits', 2)
            ->where('visitorStats.today.page_views', 2)
            ->has('visitorStats.last_7_days', 7)
            ->where('visitorStats.this_month.unique_visitors', 2));

    $this->actingAs($user)
        ->getJson(route('dashboard.visitor-stats'))
        ->assertOk()
        ->assertJsonPath('today.unique_visitors', 2)
        ->assertJsonPath('today.visits', 2)
        ->assertJsonPath('today.page_views', 2)
        ->assertJsonCount(7, 'last_7_days')
        ->assertJsonPath('this_month.unique_visitors', 2);
});
