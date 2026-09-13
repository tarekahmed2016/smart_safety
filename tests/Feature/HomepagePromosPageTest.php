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
            ->has('sectionCards', 14)
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
            ->where('sectionCards.1.key', 'about')
            ->where('sectionCards.2.key', 'why_us')
            ->where('sectionCards.3.key', 'services')
            ->where('sectionCards.13.key', 'contact'));
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

test('admin homepage promo types mark feature highlight as having no action fields', function () {
    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('promoTypes.2.value', 'business_cta')
            ->where('promoTypes.2.supports_action', true)
            ->where('promoTypes.3.value', 'feature_highlight')
            ->where('promoTypes.3.supports_action', false)
            ->where('promoTypes.5.value', 'custom_manufacturing')
            ->where('promoTypes.5.supports_action', true));
});

test('public homepage feature highlights expose icon title and description without an action', function () {
    HomepagePromoBlock::factory()->featureHighlight()->create([
        'title_ar' => 'جودة عالية',
        'title_en' => 'High Quality',
        'description_ar' => 'وصف الميزة',
        'description_en' => 'Feature description',
        'icon' => 'quality',
        'cta_text_ar' => 'اطلب الآن',
        'cta_text_en' => 'Order now',
        'cta_url' => 'https://example.com/should-not-render',
        'is_active' => true,
        'ordering' => 1,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/HomePage', false)
            ->has('featureHighlights', 1)
            ->where('featureHighlights.0.title_ar', 'جودة عالية')
            ->where('featureHighlights.0.title_en', 'High Quality')
            ->where('featureHighlights.0.description_ar', 'وصف الميزة')
            ->where('featureHighlights.0.description_en', 'Feature description')
            ->where('featureHighlights.0.icon', 'quality')
            ->where('featureHighlights.0.supports_action', false));
});

test('homepage promo form hides action fields unless the type supports a button', function () {
    $formSource = file_get_contents(resource_path('js/Components/Features/HomepagePromos/HomepagePromoFormModal.vue'));

    expect(substr_count($formSource, 'v-if="showActionFields"'))->toBe(3)
        ->and($formSource)->toContain('ctaTextArLabel')
        ->and($formSource)->toContain('ctaTextEnLabel')
        ->and($formSource)->toContain('ctaUrlLabel')
        ->and($formSource)->toContain('showIconField');
});

test('public features section template renders icon title and description without a button', function () {
    $homeSource = file_get_contents(resource_path('js/Pages/Public/HomePage.vue'));

    preg_match("/section\\.type === 'features'.*?<\\/section>/s", $homeSource, $matches);

    expect($matches[0] ?? '')->not->toBe('')
        ->and($matches[0])->toContain('feature.icon')
        ->and($matches[0])->toContain('feature.title')
        ->and($matches[0])->toContain('feature.text')
        ->and($matches[0])->not->toContain('href')
        ->and($matches[0])->not->toContain('px-btn')
        ->and($matches[0])->not->toContain('cta_');
});
