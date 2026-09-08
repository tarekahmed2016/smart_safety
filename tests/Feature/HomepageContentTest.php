<?php

use App\Enums\HomepagePromoType;
use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\HomepageContentSeeder;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('company info table contains homepage content columns', function () {
    expect(Schema::hasColumns('company_info', [
        'hero_highlight_ar',
        'hero_primary_cta_text_ar',
        'products_homepage_limit',
        'footer_copyright_en',
    ]))->toBeTrue();
});

test('homepage promo blocks table contains icon column', function () {
    expect(Schema::hasColumn('homepage_promo_blocks', 'icon'))->toBeTrue();
});

test('admin can save homepage content fields on company info', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            'hero_highlight_ar' => 'مستقبل أفضل',
            'hero_highlight_en' => 'better future',
            'hero_primary_cta_text_ar' => 'اطلب عرض سعر',
            'hero_primary_cta_text_en' => 'Request a quote',
            'hero_primary_cta_url' => '#contact',
            'products_section_title_ar' => 'منتجاتنا',
            'products_section_title_en' => 'Our products',
            'products_homepage_limit' => 6,
            'footer_copyright_en' => '© {year} {company}. All rights reserved.',
        ])
        ->assertRedirect();

    $companyInfo = CompanyInfo::first();

    expect($companyInfo->hero_highlight_en)->toBe('better future')
        ->and($companyInfo->products_homepage_limit)->toBe(6)
        ->and($companyInfo->footer_copyright_en)->toContain('{year}');
});

test('homepage content seeder seeds feature highlights and industries', function () {
    $this->seed(HomepageContentSeeder::class);

    expect(HomepagePromoBlock::where('type', HomepagePromoType::FeatureHighlight)->count())->toBeGreaterThanOrEqual(4)
        ->and(HomepagePromoBlock::where('type', HomepagePromoType::Industry)->count())->toBeGreaterThanOrEqual(6)
        ->and(HomepagePromoBlock::where('type', HomepagePromoType::CustomManufacturing)->count())->toBeGreaterThanOrEqual(1);
});

test('homepage respects configured products limit', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'products_homepage_limit' => 3,
    ]);

    Product::factory()->count(5)->create(['is_active' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 3));
});

test('homepage exposes cms hero cta fields from company info', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'hero_primary_cta_text_en' => 'Custom quote',
        'hero_primary_cta_url' => '#custom-contact',
        'products_homepage_limit' => 8,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('companyInfo.hero_primary_cta_text_en', 'Custom quote')
            ->where('companyInfo.hero_primary_cta_url', '#custom-contact'));
});
