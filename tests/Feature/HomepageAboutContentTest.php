<?php

use App\Enums\HomepagePromoType;
use App\Models\CompanyInfo;
use App\Models\User;
use App\Services\HomepageSectionService;
use App\Support\HomepageAboutContentDefaults;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    app(HomepageSectionService::class)->ensureDefaultsExist();
});

test('company info table contains about highlight columns', function () {
    expect(Schema::hasColumns('company_info', [
        'about_section_title_ar',
        'about_highlight_ar',
        'about_highlight_en',
    ]))->toBeTrue();
});

test('homepage exposes original about copy stats and highlight cards from cms', function () {
    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        ...HomepageAboutContentDefaults::companyFields(),
        'about_ar' => 'كادر مميز من مهندسين عمانيين ذوي خبرة أكثر من عشر سنوات.',
        'about_en' => 'A distinguished team of Omani engineers with more than ten years of experience.',
        'products_homepage_limit' => 8,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('companyInfo.about_section_title_ar', 'شركة عمانية بكوادر متميزة')
            ->where('companyInfo.about_highlight_ar', 'متميزة')
            ->where('companyInfo.about_ar', fn ($about) => str_contains((string) $about, 'كادر مميز من مهندسين عمانيين'))
            ->has('stats', 2)
            ->has('aboutHighlights', 4)
            ->where('stats.0.title_ar', '10+')
            ->where('stats.1.title_ar', '100%')
            ->where('aboutHighlights.0.title_ar', 'معايير عالمية')
            ->where('aboutHighlights.2.icon', 'shield')
            ->where('aboutHighlights.2.description_ar', 'بيئة عمل آمنة ومعتمدة'));
});

test('admin can save about title highlight on company info', function () {
    $this->actingAs($this->admin)
        ->put(route('company-info.update'), [
            'name_ar' => 'الصناعة الإبداعية',
            'name_en' => 'Creative Industry',
            'about_section_title_ar' => 'شركة عمانية بكوادر متميزة',
            'about_section_title_en' => 'An Omani company with distinguished talent',
            'about_highlight_ar' => 'متميزة',
            'about_highlight_en' => 'distinguished',
        ])
        ->assertRedirect();

    $companyInfo = CompanyInfo::first();

    expect($companyInfo->about_section_title_ar)->toBe('شركة عمانية بكوادر متميزة')
        ->and($companyInfo->about_highlight_en)->toBe('distinguished');
});

test('about highlight is a homepage promo type', function () {
    expect(HomepagePromoType::values())->toContain('about_highlight')
        ->and(HomepagePromoType::AboutHighlight->labelEn())->toBe('About Highlight');
});
