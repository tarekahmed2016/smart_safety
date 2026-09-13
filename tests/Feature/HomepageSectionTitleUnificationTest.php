<?php

use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Models\HomepageSection;
use App\Models\User;
use App\Services\HomepageSectionService;
use App\Support\HomepagePromoSectionMap;
use App\Support\HomepageSectionTitleSource;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    HomepageSection::query()->delete();
    app(HomepageSectionService::class)->ensureDefaultsExist();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

/**
 * @param  array<string, array<string, mixed>>  $overridesByKey
 * @return list<array<string, mixed>>
 */
function homepageSectionsUpdatePayload(array $overridesByKey = []): array
{
    return HomepageSection::query()
        ->orderBy('ordering')
        ->get()
        ->map(function (HomepageSection $section) use ($overridesByKey) {
            $overrides = $overridesByKey[$section->key] ?? [];

            return [
                'id' => $section->id,
                'is_visible' => $section->is_visible,
                'ordering' => $section->ordering,
                'title_ar' => $overrides['title_ar'] ?? $section->title_ar,
                'title_en' => $overrides['title_en'] ?? $section->title_en,
                'headline_ar' => $overrides['headline_ar'] ?? $section->settings['headline_ar'] ?? null,
                'headline_en' => $overrides['headline_en'] ?? $section->settings['headline_en'] ?? null,
                'highlight_ar' => $overrides['highlight_ar'] ?? $section->settings['highlight_ar'] ?? null,
                'highlight_en' => $overrides['highlight_en'] ?? $section->settings['highlight_en'] ?? null,
                'subtitle_ar' => $overrides['subtitle_ar'] ?? $section->settings['subtitle_ar'] ?? null,
                'subtitle_en' => $overrides['subtitle_en'] ?? $section->settings['subtitle_en'] ?? null,
                'max_items' => $overrides['max_items'] ?? $section->settings['max_items'] ?? null,
                'show_in_navigation' => $section->show_in_navigation,
                'nav_label_ar' => $section->nav_label_ar,
                'nav_label_en' => $section->nav_label_en,
                'nav_order' => $section->nav_order,
                'anchor_id' => $section->anchor_id,
            ];
        })
        ->all();
}

test('homepage promo settings do not expose a second writable section title', function () {
    expect(HomepagePromoSectionMap::companySettingFields('products'))->not->toContain('products_section_title_ar')
        ->and(HomepagePromoSectionMap::companySettingFields('products'))->not->toContain('products_section_title_en')
        ->and(HomepagePromoSectionMap::companySettingFields('about'))->not->toContain('about_section_title_ar')
        ->and(HomepagePromoSectionMap::sectionSettingFields('services'))->not->toContain('title_ar')
        ->and(HomepagePromoSectionMap::sectionSettingFields('services'))->not->toContain('title_en')
        ->and(HomepagePromoSectionMap::sectionSettingFields('why_us'))->not->toContain('title_ar');

    $cardSource = file_get_contents(resource_path('js/Components/Features/HomepagePromos/HomepagePromoSectionCard.vue'));
    $homeSource = file_get_contents(resource_path('js/Pages/Public/HomePage.vue'));

    expect($cardSource)->not->toContain('v-model="settingsForm.section.title_ar"')
        ->and($cardSource)->not->toContain('v-model="settingsForm.section.title_en"')
        ->and($cardSource)->not->toContain('v-model="settingsForm.section.headline_ar"')
        ->and($cardSource)->not->toContain('v-model="settingsForm.section.max_items"')
        ->and($cardSource)->not->toContain('settingsForm.put')
        ->and($cardSource)->not->toContain('currentTitleAr')
        ->and($cardSource)->not->toContain('company_settings')
        ->and($cardSource)->not->toContain('card.section_settings')
        ->and($cardSource)->not->toContain("t('homepageSections.titleArLabel')")
        ->and($cardSource)->toContain("t('homepagePromos.sectionCard.editSectionSettings')")
        ->and($cardSource)->toContain('/homepage-sections')
        ->and($cardSource)->toContain('HomepagePromoItemsList')
        ->and($cardSource)->toContain('sectionVisible')
        ->and($homeSource)->not->toContain('products_section_title')
        ->and($homeSource)->not->toContain('about_section_title')
        ->and($homeSource)->not->toContain('gallery_section_title')
        ->and($homeSource)->not->toContain('industries_section_title')
        ->and($homeSource)->not->toContain('contact_section_title');
});

test('legacy promo titles are copied into empty homepage sections once', function () {
    HomepageSection::query()->where('key', 'products')->update([
        'title_ar' => null,
        'title_en' => null,
    ]);
    HomepageSection::query()->where('key', 'about')->update([
        'title_ar' => 'عنوان الأقسام',
        'title_en' => 'Sections title',
    ]);

    CompanyInfo::query()->create([
        'products_section_title_ar' => 'منتجاتنا',
        'products_section_title_en' => 'Our products',
        'about_section_title_ar' => 'عنوان العروض',
        'about_section_title_en' => 'Promo title',
        'products_homepage_limit' => 8,
    ]);

    HomepageSectionTitleSource::copyLegacyPromoTitlesIntoHomepageSections();

    expect(HomepageSection::query()->where('key', 'products')->value('title_ar'))->toBe('منتجاتنا')
        ->and(HomepageSection::query()->where('key', 'products')->value('title_en'))->toBe('Our products')
        ->and(HomepageSection::query()->where('key', 'about')->value('title_en'))->toBe('Sections title');

    CompanyInfo::query()->first()->update([
        'products_section_title_en' => 'Changed promo title',
        'about_section_title_en' => 'Changed about promo title',
    ]);

    HomepageSectionTitleSource::copyLegacyPromoTitlesIntoHomepageSections();

    expect(HomepageSection::query()->where('key', 'products')->value('title_en'))->toBe('Our products')
        ->and(HomepageSection::query()->where('key', 'about')->value('title_en'))->toBe('Sections title')
        ->and(Schema::hasColumn('company_info', 'products_section_title_en'))->toBeTrue();
});

test('homepage promos hide the section title while keeping promo items visible', function () {
    HomepageSection::query()->where('key', 'features')->update([
        'title_ar' => 'عنوان القسم المخفي',
        'title_en' => 'Hidden section title',
    ]);

    $block = HomepagePromoBlock::factory()->featureHighlight()->create([
        'title_ar' => 'جودة عالية',
        'title_en' => 'High Quality',
        'description_en' => 'Feature description',
        'ordering' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sectionCards', function ($cards) use ($block) {
                $features = collect($cards)->firstWhere('key', 'features');

                return is_array($features)
                    && ! array_key_exists('title_ar', $features['section'] ?? [])
                    && ! array_key_exists('title_en', $features['section'] ?? [])
                    && ! array_key_exists('title_ar', $features['section_settings'] ?? [])
                    && ! array_key_exists('title_en', $features['section_settings'] ?? [])
                    && ($features['items'][0]['id'] ?? null) === $block->id
                    && ($features['items'][0]['title_en'] ?? null) === 'High Quality'
                    && ($features['items'][0]['description_en'] ?? null) === 'Feature description';
            }));
});

test('public homepage shows the homepage section title once from homepage sections', function () {
    HomepageSection::query()->where('key', 'products')->update([
        'title_ar' => 'منتجات الصفحة',
        'title_en' => 'Homepage products',
    ]);

    CompanyInfo::query()->create([
        'products_section_title_ar' => 'عنوان مختلف',
        'products_section_title_en' => 'Different promo title',
        'products_homepage_limit' => 8,
    ]);

    $homeSource = file_get_contents(resource_path('js/Pages/Public/HomePage.vue'));

    expect(substr_count($homeSource, "resolveSectionTitle(section, 'public.home.products.title')"))->toBe(1)
        ->and($homeSource)->not->toContain('products_section_title');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $products = collect($sections)->where('key', 'products')->values();

                return $products->count() === 1
                    && ($products[0]['title_ar'] ?? null) === 'منتجات الصفحة'
                    && ($products[0]['title_en'] ?? null) === 'Homepage products';
            }));
});

test('updating a title from homepage sections appears once on the public homepage', function () {
    $this->actingAs($this->admin)
        ->put(route('homepage-sections.update'), [
            'sections' => homepageSectionsUpdatePayload([
                'gallery' => [
                    'title_ar' => 'معرض محدث',
                    'title_en' => 'Updated gallery',
                ],
            ]),
        ])
        ->assertRedirect(route('homepage-sections.index'));

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $gallery = collect($sections)->where('key', 'gallery')->values();

                return $gallery->count() === 1
                    && ($gallery[0]['title_ar'] ?? null) === 'معرض محدث'
                    && ($gallery[0]['title_en'] ?? null) === 'Updated gallery';
            }));

    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sectionCards', function ($cards) {
                $gallery = collect($cards)->firstWhere('key', 'gallery');

                return is_array($gallery)
                    && ! array_key_exists('title_ar', $gallery['section'] ?? [])
                    && ! array_key_exists('title_en', $gallery['section'] ?? []);
            }));
});

test('saving homepage promo settings cannot create a conflicting section title', function () {
    HomepageSection::query()->where('key', 'contact')->update([
        'title_ar' => 'تواصل معنا',
        'title_en' => 'Contact us',
    ]);

    CompanyInfo::query()->create([
        'contact_section_title_ar' => 'عنوان قديم',
        'contact_section_title_en' => 'Old contact title',
        'contact_section_subtitle_en' => 'Reach out',
        'products_homepage_limit' => 8,
    ]);

    $this->actingAs($this->admin)
        ->put(route('homepage-promos.section-settings.update', 'contact'), [
            'company' => [
                'contact_section_title_ar' => 'عنوان العروض',
                'contact_section_title_en' => 'Promo contact title',
                'contact_section_subtitle_ar' => 'وصف محدث',
                'contact_section_subtitle_en' => 'Updated subtitle',
            ],
            'section' => [
                'title_ar' => 'عنوان ثاني',
                'title_en' => 'Second title',
            ],
        ])
        ->assertRedirect();

    $contact = HomepageSection::query()->where('key', 'contact')->firstOrFail();
    $companyInfo = CompanyInfo::query()->first();

    expect($contact->title_ar)->toBe('تواصل معنا')
        ->and($contact->title_en)->toBe('Contact us')
        ->and($companyInfo->contact_section_title_en)->toBe('Old contact title')
        ->and($companyInfo->contact_section_subtitle_en)->toBe('Reach out');
});

test('homepage promos keep item content while section visibility stays independent', function () {
    $features = HomepageSection::query()->where('key', 'features')->firstOrFail();
    $features->update(['is_visible' => true]);

    $block = HomepagePromoBlock::factory()->featureHighlight()->create([
        'title_en' => 'High Quality',
        'is_active' => true,
        'ordering' => 1,
    ]);

    $this->actingAs($this->admin)
        ->put(route('homepage-sections.update'), [
            'sections' => homepageSectionsUpdatePayload([
                'features' => [],
            ]),
        ]);

    HomepageSection::query()->where('key', 'features')->update(['is_visible' => false]);
    $block->refresh();

    expect($block->is_active)->toBeTrue()
        ->and($block->title_en)->toBe('High Quality')
        ->and(HomepageSection::query()->where('key', 'features')->value('is_visible'))->toBeFalse();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->where('key', 'features')->isEmpty())
            ->has('featureHighlights', 1)
            ->where('featureHighlights.0.title_en', 'High Quality'));

    $block->update(['is_active' => false]);
    HomepageSection::query()->where('key', 'features')->update(['is_visible' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', fn ($sections) => collect($sections)->contains('key', 'features'))
            ->has('featureHighlights', 0));
});

test('homepage sections remain the only writable source for section-wide settings', function () {
    expect(HomepagePromoSectionMap::companySettingFields('products'))->toBe([])
        ->and(HomepagePromoSectionMap::sectionSettingFields('gallery'))->toBe([])
        ->and(HomepagePromoSectionMap::sectionSettingFields('why_us'))->toBe([])
        ->and(HomepagePromoSectionMap::sectionSettingFields('services'))->toBe([]);

    $this->actingAs($this->admin)
        ->put(route('homepage-sections.update'), [
            'sections' => homepageSectionsUpdatePayload([
                'why_us' => [
                    'title_en' => 'Why us updated',
                    'headline_en' => 'Updated headline',
                ],
                'gallery' => [
                    'max_items' => 7,
                ],
                'contact' => [
                    'subtitle_en' => 'Updated contact subtitle',
                ],
            ]),
        ])
        ->assertRedirect(route('homepage-sections.index'));

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $whyUs = collect($sections)->firstWhere('key', 'why_us');
                $gallery = collect($sections)->firstWhere('key', 'gallery');
                $contact = collect($sections)->firstWhere('key', 'contact');

                return ($whyUs['title_en'] ?? null) === 'Why us updated'
                    && ($whyUs['headline_en'] ?? null) === 'Updated headline'
                    && (int) ($gallery['settings']['max_items'] ?? 0) === 7
                    && ($contact['settings']['subtitle_en'] ?? null) === 'Updated contact subtitle';
            }));
});
