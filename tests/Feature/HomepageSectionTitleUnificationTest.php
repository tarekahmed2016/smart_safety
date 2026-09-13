<?php

use App\Models\CompanyInfo;
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
                'headline_ar' => $section->settings['headline_ar'] ?? null,
                'headline_en' => $section->settings['headline_en'] ?? null,
                'highlight_ar' => $section->settings['highlight_ar'] ?? null,
                'highlight_en' => $section->settings['highlight_en'] ?? null,
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
        ->and($cardSource)->toContain("t('homepagePromos.sectionCard.editSectionSettings')")
        ->and($cardSource)->toContain('homepage-sections')
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

test('public homepage and homepage promos share the homepage section title', function () {
    HomepageSection::query()->where('key', 'products')->update([
        'title_ar' => 'منتجات الصفحة',
        'title_en' => 'Homepage products',
    ]);

    CompanyInfo::query()->create([
        'products_section_title_ar' => 'عنوان مختلف',
        'products_section_title_en' => 'Different promo title',
        'products_homepage_limit' => 8,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $products = collect($sections)->firstWhere('key', 'products');

                return is_array($products)
                    && ($products['title_ar'] ?? null) === 'منتجات الصفحة'
                    && ($products['title_en'] ?? null) === 'Homepage products';
            }));

    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sectionCards', function ($cards) {
                $products = collect($cards)->firstWhere('key', 'products');

                return is_array($products)
                    && ($products['section']['title_ar'] ?? null) === 'منتجات الصفحة'
                    && ($products['section']['title_en'] ?? null) === 'Homepage products'
                    && ! array_key_exists('title_ar', $products['section_settings'] ?? [])
                    && ! array_key_exists('products_section_title_en', $products['company_settings'] ?? [])
                    && ($products['edit_section_settings_url'] ?? null) === '/homepage-sections';
            }));
});

test('updating a title from homepage sections appears on the public page and homepage promos', function () {
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

    expect(HomepageSection::query()->where('key', 'gallery')->value('title_en'))->toBe('Updated gallery');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('homepageSections', function ($sections) {
                $gallery = collect($sections)->firstWhere('key', 'gallery');

                return is_array($gallery)
                    && ($gallery['title_ar'] ?? null) === 'معرض محدث'
                    && ($gallery['title_en'] ?? null) === 'Updated gallery';
            }));

    $this->actingAs($this->admin)
        ->get(route('homepage-promos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sectionCards', function ($cards) {
                $gallery = collect($cards)->firstWhere('key', 'gallery');

                return is_array($gallery)
                    && ($gallery['section']['title_ar'] ?? null) === 'معرض محدث'
                    && ($gallery['section']['title_en'] ?? null) === 'Updated gallery';
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
        ->and($companyInfo->contact_section_subtitle_en)->toBe('Updated subtitle');
});
