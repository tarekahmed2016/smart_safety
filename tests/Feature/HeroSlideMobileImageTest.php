<?php

use App\Models\HeroSlide;
use App\Models\User;
use App\Services\HeroSlideService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

function validHeroSlidePayloadWithMobile(array $overrides = []): array
{
    return array_merge([
        'title_ar' => 'شريحة البطل',
        'title_en' => 'Hero Slide',
        'description_ar' => 'وصف الشريحة',
        'description_en' => 'Slide description',
        'cta_text_ar' => 'اطلب الآن',
        'cta_text_en' => 'Order Now',
        'cta_url' => 'https://example.com/order',
        'ordering' => 0,
        'is_active' => true,
        'image' => UploadedFile::fake()->image('hero-desktop.jpg', 1920, 1080),
        'mobile_image' => UploadedFile::fake()->image('hero-mobile.jpg', 750, 1334),
    ], $overrides);
}

test('admin can create hero slide with optional mobile image', function () {
    $this->actingAs($this->admin)
        ->post(route('hero-slides.store'), validHeroSlidePayloadWithMobile())
        ->assertRedirect();

    $slide = HeroSlide::with(['attachment', 'mobileAttachment'])->first();

    expect($slide)->not->toBeNull()
        ->and($slide->attachment)->not->toBeNull()
        ->and($slide->mobileAttachment)->not->toBeNull()
        ->and($slide->mobileAttachment->collection)->toBe('mobile');

    Storage::disk('public')->assertExists($slide->attachment->path);
    Storage::disk('public')->assertExists($slide->mobileAttachment->path);
});

test('hero slide mobile image is optional on create', function () {
    $payload = validHeroSlidePayloadWithMobile();
    unset($payload['mobile_image']);

    $this->actingAs($this->admin)
        ->post(route('hero-slides.store'), $payload)
        ->assertRedirect();

    $slide = HeroSlide::with(['attachment', 'mobileAttachment'])->first();

    expect($slide->attachment)->not->toBeNull()
        ->and($slide->mobileAttachment)->toBeNull();
});

test('hero slide rejects invalid mobile image upload', function () {
    $this->actingAs($this->admin)
        ->post(route('hero-slides.store'), validHeroSlidePayloadWithMobile([
            'mobile_image' => UploadedFile::fake()->create('slide.svg', 100, 'image/svg+xml'),
        ]))
        ->assertSessionHasErrors('mobile_image');

    expect(HeroSlide::count())->toBe(0);
});

test('admin can add mobile image when updating hero slide', function () {
    $slide = HeroSlide::factory()->create([
        'title_en' => 'Update Mobile',
        'ordering' => 0,
    ]);
    $slide->attachment()->create([
        'name' => 'hero.jpg',
        'path' => 'hero-slides/existing.jpg',
        'collection' => 'default',
    ]);
    Storage::disk('public')->put('hero-slides/existing.jpg', 'desktop');

    $this->actingAs($this->admin)
        ->post(route('hero-slides.update', $slide), [
            '_method' => 'put',
            'title_ar' => $slide->title_ar,
            'title_en' => $slide->title_en,
            'ordering' => 0,
            'is_active' => true,
            'mobile_image' => UploadedFile::fake()->image('hero-mobile.jpg', 750, 1334),
        ])
        ->assertRedirect();

    $slide->refresh()->load('mobileAttachment');

    expect($slide->mobileAttachment)->not->toBeNull();
    Storage::disk('public')->assertExists($slide->mobileAttachment->path);
});

test('public homepage exposes desktop and mobile hero slide image urls', function () {
    $slide = HeroSlide::factory()->create([
        'title_en' => 'Responsive Slide',
        'is_active' => true,
        'ordering' => 0,
    ]);
    $slide->attachment()->create([
        'name' => 'desktop.jpg',
        'path' => 'hero-slides/desktop.jpg',
        'collection' => 'default',
    ]);
    $slide->mobileAttachment()->create([
        'name' => 'mobile.jpg',
        'path' => 'hero-slides/mobile/mobile.jpg',
        'collection' => 'mobile',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/HomePage', false)
            ->has('heroSlides', 1)
            ->where('heroSlides.0.image', asset('storage/hero-slides/desktop.jpg'))
            ->where('heroSlides.0.mobile_image', asset('storage/hero-slides/mobile/mobile.jpg')));
});

test('public homepage returns null mobile image when not uploaded', function () {
    $slide = HeroSlide::factory()->create([
        'title_en' => 'Desktop Only Slide',
        'is_active' => true,
        'ordering' => 0,
    ]);
    $slide->attachment()->create([
        'name' => 'desktop.jpg',
        'path' => 'hero-slides/desktop-only.jpg',
        'collection' => 'default',
    ]);

    $slides = app(HeroSlideService::class)->getActiveSlidesForPublic();

    expect($slides)->toHaveCount(1)
        ->and($slides[0]['image'])->toBe(asset('storage/hero-slides/desktop-only.jpg'))
        ->and($slides[0]['mobile_image'])->toBeNull();
});

test('deleting hero slide removes desktop and mobile attachments', function () {
    $slide = HeroSlide::factory()->create(['title_en' => 'Delete Both']);
    $slide->attachment()->create([
        'name' => 'desktop.jpg',
        'path' => 'hero-slides/desktop.jpg',
        'collection' => 'default',
    ]);
    $slide->mobileAttachment()->create([
        'name' => 'mobile.jpg',
        'path' => 'hero-slides/mobile/mobile.jpg',
        'collection' => 'mobile',
    ]);
    Storage::disk('public')->put('hero-slides/desktop.jpg', 'desktop');
    Storage::disk('public')->put('hero-slides/mobile/mobile.jpg', 'mobile');

    $this->actingAs($this->admin)
        ->delete(route('hero-slides.destroy', $slide))
        ->assertRedirect();

    Storage::disk('public')->assertMissing('hero-slides/desktop.jpg');
    Storage::disk('public')->assertMissing('hero-slides/mobile/mobile.jpg');
    expect(HeroSlide::count())->toBe(0);
});
