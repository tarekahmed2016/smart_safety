<?php

use App\Enums\HomepagePromoType;
use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Models\HomepageSection;
use App\Models\User;
use App\Services\HomepageSectionService;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    HomepageSection::query()->delete();
    app(HomepageSectionService::class)->ensureDefaultsExist();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin homepage promos index returns section cards without hero', function () {
    HomepagePromoBlock::factory()->create([
        'type' => HomepagePromoType::FeatureHighlight,
        'title_en' => 'Quality',
        'ordering' => 1,
    ]);

    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('sectionCards', 11)
            ->where('sectionCards.0.key', 'features')
            ->where('sectionCards.0.items.0.title_en', 'Quality')
            ->missing('homepagePromoBlocks'));
});

test('section cards exclude hero and follow homepage section ordering', function () {
    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sectionCards.0.key', 'features')
            ->where('sectionCards.1.key', 'products')
            ->where('sectionCards.2.key', 'services')
            ->where('sectionCards.10.key', 'contact'));
});

test('admin can update homepage promo section settings for products', function () {
    CompanyInfo::query()->create([
        'products_section_title_en' => 'Old title',
        'products_homepage_limit' => 4,
    ]);

    $this->actingAs($this->admin)
        ->put(route('homepage-promos.section-settings.update', 'products'), [
            'company' => [
                'products_section_title_ar' => 'منتجاتنا',
                'products_section_title_en' => 'Our Products',
                'products_homepage_limit' => 6,
            ],
        ])
        ->assertRedirect();

    $companyInfo = CompanyInfo::first();

    expect($companyInfo->products_section_title_ar)->toBe('منتجاتنا')
        ->and($companyInfo->products_section_title_en)->toBe('Our Products')
        ->and($companyInfo->products_homepage_limit)->toBe(6);
});

test('admin can update gallery max items through homepage promos section settings', function () {
    $gallerySection = HomepageSection::query()->where('key', 'gallery')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('homepage-promos.section-settings.update', 'gallery'), [
            'company' => [
                'gallery_section_title_en' => 'Gallery',
            ],
            'section' => [
                'max_items' => 12,
            ],
        ])
        ->assertRedirect();

    $gallerySection->refresh();

    expect($gallerySection->settings['max_items'])->toBe(12);
});

test('invalid homepage promo section settings key returns not found', function () {
    $this->actingAs($this->admin)
        ->put(route('homepage-promos.section-settings.update', 'hero'), [
            'company' => [],
        ])
        ->assertNotFound();
});
