<?php

use App\Models\CompanyInfo;
use App\Models\User;
use App\Support\GoogleMapsEmbedUrl;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

const VALID_GOOGLE_MAPS_EMBED_URL = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3654.123';

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('company info table has google maps embed url column', function () {
    expect(Schema::hasColumn('company_info', 'google_maps_embed_url'))->toBeTrue();
});

test('admin can save a google maps embed url from company info', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'footer_description_ar' => 'نص تعريف الشركة',
        'footer_description_en' => 'Company about text',
    ]);

    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            'footer_description_ar' => 'نص تعريف الشركة',
            'footer_description_en' => 'Company about text',
            'google_maps_embed_url' => VALID_GOOGLE_MAPS_EMBED_URL,
        ])
        ->assertRedirect();

    $companyInfo = CompanyInfo::first();

    expect($companyInfo->google_maps_embed_url)->toBe(VALID_GOOGLE_MAPS_EMBED_URL)
        ->and($companyInfo->name_ar)->toBe('الصناعة الإبداعية')
        ->and($companyInfo->footer_description_ar)->toBe('نص تعريف الشركة');
});

test('company info rejects non google maps urls and html iframe snippets', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'footer_description_ar' => 'نص تعريف الشركة',
    ]);

    $invalid = [
        'https://example.com/maps/embed?pb=1',
        'https://www.youtube.com/embed/abc123',
        '<iframe src="'.VALID_GOOGLE_MAPS_EMBED_URL.'"></iframe>',
        'http://www.google.com/maps/embed?pb=1',
        'javascript:alert(1)',
    ];

    foreach ($invalid as $url) {
        $this->actingAs($this->admin)
            ->from(route('company-info.index'))
            ->put(route('company-info.update'), [
                'name_ar' => 'الصناعة الإبداعية',
                'name_en' => 'Creative Industry',
                'google_maps_embed_url' => $url,
            ])
            ->assertSessionHasErrors('google_maps_embed_url');
    }

    expect(CompanyInfo::first()->footer_description_ar)->toBe('نص تعريف الشركة')
        ->and(CompanyInfo::first()->google_maps_embed_url)->toBeNull();
});

test('homepage shares the google maps embed url for the footer map', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'footer_description_ar' => 'نص تعريف الشركة',
        'google_maps_embed_url' => VALID_GOOGLE_MAPS_EMBED_URL,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('companyInfo.google_maps_embed_url', VALID_GOOGLE_MAPS_EMBED_URL)
            ->where('companyInfo.footer_description_ar', 'نص تعريف الشركة'));
});

test('homepage falls back to company description when the maps url is empty', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'footer_description_ar' => 'نص تعريف الشركة',
        'footer_description_en' => 'Company about text',
        'google_maps_embed_url' => null,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('companyInfo.google_maps_embed_url', '')
            ->where('companyInfo.footer_description_ar', 'نص تعريف الشركة'));
});

test('footer map markup stays in the brand column and is responsive', function () {
    $footer = file_get_contents(resource_path('js/Components/Public/PublicFooter.vue'));
    $page = file_get_contents(resource_path('js/Pages/CompanyInfo/CompanyInfoPage.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $ar = file_get_contents(resource_path('js/Plugins/I18n/Locales/ar.json'));
    $en = file_get_contents(resource_path('js/Plugins/I18n/Locales/en.json'));

    $brandBlock = Str::between($footer, 'class="px-footer-brand"', 'class="px-footer-links"');
    $mapBlock = Str::between($styles, '.px-footer-map {', '}');
    $iframeBlock = Str::between($styles, '.px-footer-map iframe {', '}');

    expect($brandBlock)->toContain('v-if="googleMapsEmbedUrl"')
        ->and($brandBlock)->toContain('px-footer-map')
        ->and($brandBlock)->toContain('width="100%"')
        ->and($brandBlock)->toContain('height="220"')
        ->and($brandBlock)->toContain('loading="lazy"')
        ->and($brandBlock)->toContain('allowfullscreen')
        ->and($brandBlock)->toContain('referrerpolicy="no-referrer-when-downgrade"')
        ->and($brandBlock)->toContain("t('public.home.footer.mapTitle')")
        ->and($brandBlock)->toContain('v-else')
        ->and($brandBlock)->toContain('footerDescription')
        ->and($footer)->not->toContain('grid-column')
        ->and($footer)->not->toContain('order:')
        ->and($page)->toContain('form.google_maps_embed_url')
        ->and($page)->toContain("t('companyInfo.form.googleMapsEmbedUrlLabel')")
        ->and($ar)->toContain('رابط خريطة Google')
        ->and($ar)->toContain('من Google Maps اختر Share ثم Embed a map وانسخ رابط iframe فقط، وليس كود HTML كاملًا.')
        ->and($en)->toContain('Google Maps Embed URL')
        ->and($en)->toContain('Company location map')
        ->and($ar)->toContain('خريطة موقع الشركة')
        ->and($mapBlock)->toContain('width: 100%')
        ->and($mapBlock)->toContain('overflow: hidden')
        ->and($mapBlock)->toContain('border-radius: var(--px-radius)')
        ->and($iframeBlock)->toContain('width: 100%')
        ->and($iframeBlock)->toContain('overflow: hidden')
        ->and($styles)->toContain('.px-footer-map iframe {')
        ->and($styles)->toContain('height: 12.5rem')
        ->and($styles)->toContain('height: 14rem');
});

test('google maps embed url sanitizer allows google maps and rejects others', function () {
    expect(GoogleMapsEmbedUrl::sanitize(VALID_GOOGLE_MAPS_EMBED_URL))->toBe(VALID_GOOGLE_MAPS_EMBED_URL)
        ->and(GoogleMapsEmbedUrl::sanitize('https://maps.google.com/maps?q=Muscat&output=embed'))
        ->toBe('https://maps.google.com/maps?q=Muscat&output=embed')
        ->and(GoogleMapsEmbedUrl::sanitize('https://www.google.com.om/maps/embed?pb=1'))
        ->toBe('https://www.google.com.om/maps/embed?pb=1')
        ->and(GoogleMapsEmbedUrl::sanitize('<iframe src="'.VALID_GOOGLE_MAPS_EMBED_URL.'"></iframe>'))
        ->toBeNull()
        ->and(GoogleMapsEmbedUrl::sanitize('https://evil.example/maps/embed'))
        ->toBeNull()
        ->and(GoogleMapsEmbedUrl::sanitize(''))
        ->toBeNull();

    $script = <<<'JS'
import { sanitizeGoogleMapsEmbedUrl } from './resources/js/Utils/googleMapsEmbedUrl.js';

console.log(JSON.stringify({
    valid: sanitizeGoogleMapsEmbedUrl('https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3654.123'),
    iframe: sanitizeGoogleMapsEmbedUrl('<iframe src="https://www.google.com/maps/embed?pb=1"></iframe>'),
    other: sanitizeGoogleMapsEmbedUrl('https://example.com/maps/embed?pb=1'),
    empty: sanitizeGoogleMapsEmbedUrl(''),
}));
JS;

    $result = json_decode(
        Process::path(base_path())->run(['node', '--input-type=module', '-e', $script])->throw()->output(),
        true,
    );

    expect($result['valid'])->toBe(VALID_GOOGLE_MAPS_EMBED_URL)
        ->and($result['iframe'])->toBe('')
        ->and($result['other'])->toBe('')
        ->and($result['empty'])->toBe('');
});

test('public footer hides the newsletter subscribe column on all locales', function () {
    $footer = file_get_contents(resource_path('js/Components/Public/PublicFooter.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $ar = file_get_contents(resource_path('js/Plugins/I18n/Locales/ar.json'));
    $en = file_get_contents(resource_path('js/Plugins/I18n/Locales/en.json'));

    expect($footer)->toContain('const showFooterNewsletter = false')
        ->and($footer)->toContain('v-if="showFooterNewsletter"')
        ->and($footer)->toContain('id="footer-newsletter-email"')
        ->and($footer)->toContain("route('newsletter.store')")
        ->and($footer)->toContain('px-footer-brand')
        ->and($footer)->toContain("t('public.home.footer.quickLinks')")
        ->and($footer)->toContain("t('public.home.footer.contact')")
        ->and($footer)->toMatch('/<div v-if="showFooterNewsletter">[\s\S]*id="footer-newsletter-email"[\s\S]*newsletterButton/')
        ->and($styles)->toContain('grid-template-columns: 1.4fr 0.9fr 1fr;')
        ->and($styles)->not->toContain('grid-template-columns: 1.3fr 0.8fr 0.9fr 1.1fr;')
        ->and($ar)->toContain('ابق على اطلاع')
        ->and($en)->toContain('Stay in the Loop');
});
