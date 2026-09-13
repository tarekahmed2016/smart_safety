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

test('admin can update homepage promo section settings endpoint without writing section configuration', function () {
    CompanyInfo::query()->create([
        'products_section_title_en' => 'Old title',
        'products_homepage_limit' => 4,
    ]);

    $productsSection = HomepageSection::query()->where('key', 'products')->firstOrFail();
    $productsSection->update([
        'title_ar' => 'منتجاتنا',
        'title_en' => 'Our products',
    ]);

    $this->actingAs($this->admin)
        ->put(route('homepage-promos.section-settings.update', 'products'), [
            'company' => [
                'products_section_title_ar' => 'عنوان العروض',
                'products_section_title_en' => 'Promo title',
                'products_homepage_limit' => 6,
            ],
            'section' => [
                'title_ar' => 'عنوان مستقل',
                'title_en' => 'Independent title',
            ],
        ])
        ->assertRedirect();

    $companyInfo = CompanyInfo::first();
    $productsSection->refresh();

    expect($companyInfo->products_section_title_en)->toBe('Old title')
        ->and($companyInfo->products_homepage_limit)->toBe(4)
        ->and($productsSection->title_ar)->toBe('منتجاتنا')
        ->and($productsSection->title_en)->toBe('Our products');
});

test('admin can update gallery max items through homepage sections', function () {
    $gallerySection = HomepageSection::query()->where('key', 'gallery')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('homepage-sections.update'), [
            'sections' => HomepageSection::query()
                ->orderBy('ordering')
                ->get()
                ->map(fn (HomepageSection $section) => [
                    'id' => $section->id,
                    'is_visible' => $section->is_visible,
                    'ordering' => $section->ordering,
                    'title_ar' => $section->title_ar,
                    'title_en' => $section->title_en,
                    'headline_ar' => $section->settings['headline_ar'] ?? null,
                    'headline_en' => $section->settings['headline_en'] ?? null,
                    'highlight_ar' => $section->settings['highlight_ar'] ?? null,
                    'highlight_en' => $section->settings['highlight_en'] ?? null,
                    'subtitle_ar' => $section->settings['subtitle_ar'] ?? null,
                    'subtitle_en' => $section->settings['subtitle_en'] ?? null,
                    'max_items' => $section->key === 'gallery' ? 12 : ($section->settings['max_items'] ?? null),
                    'show_in_navigation' => $section->show_in_navigation,
                    'nav_label_ar' => $section->nav_label_ar,
                    'nav_label_en' => $section->nav_label_en,
                    'nav_order' => $section->nav_order,
                    'anchor_id' => $section->anchor_id,
                ])
                ->all(),
        ])
        ->assertRedirect(route('homepage-sections.index'));

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
