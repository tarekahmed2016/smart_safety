<?php

use App\Enums\ClientPartnerType;
use App\Enums\HomepagePromoType;
use App\Models\ClientPartner;
use App\Models\CompanyGoal;
use App\Models\CompanyInfo;
use App\Models\HomepagePromoBlock;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use Database\Seeders\CreativeIndustryContentSeeder;
use Database\Seeders\PlastexContentSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Http::fake([
        'creative-industry-kappa.vercel.app/*' => Http::response('fake-image', 200),
    ]);
});

test('creative industry content seeder imports cms records without changing hero branding', function () {
    $this->seed(PlastexContentSeeder::class);

    $before = CompanyInfo::first();
    $heroTitleAr = $before->hero_title_ar;
    $heroTitleEn = $before->hero_title_en;

    $this->seed(CreativeIndustryContentSeeder::class);

    $companyInfo = CompanyInfo::first();

    expect($companyInfo->name_ar)->toBe('الصناعة الإبداعية')
        ->and($companyInfo->name_en)->toBe('Creative Industry')
        ->and($companyInfo->hero_title_ar)->toBe($heroTitleAr)
        ->and($companyInfo->hero_title_en)->toBe($heroTitleEn)
        ->and($companyInfo->vision_ar)->toContain('مجموعة شركات صناعية')
        ->and($companyInfo->phone)->toBe('+968 9513 6368')
        ->and($companyInfo->email)->toBe('info@creativesindustry.com');

    expect(Service::count())->toBe(2)
        ->and(Product::count())->toBe(8)
        ->and(ClientPartner::where('type', ClientPartnerType::Client)->count())->toBe(7)
        ->and(Page::where('slug', 'goals')->exists())->toBeTrue()
        ->and(CompanyGoal::count())->toBe(6)
        ->and(Page::where('slug', 'why-us')->exists())->toBeTrue()
        ->and(HomepagePromoBlock::where('type', HomepagePromoType::Stat)->count())->toBe(2)
        ->and(HomepagePromoBlock::where('type', HomepagePromoType::AboutHighlight)->count())->toBe(4)
        ->and(HomepagePromoBlock::where('type', HomepagePromoType::WhyUsHighlight)->count())->toBe(3)
        ->and($companyInfo->about_section_title_ar)->toBe('شركة عمانية بكوادر متميزة')
        ->and($companyInfo->about_highlight_ar)->toBe('متميزة')
        ->and(HomepagePromoBlock::where('type', HomepagePromoType::BusinessCta)->count())->toBeGreaterThanOrEqual(1);
});

test('creative industry content seeder is idempotent', function () {
    $this->seed(CreativeIndustryContentSeeder::class);
    $this->seed(CreativeIndustryContentSeeder::class);

    expect(Product::count())->toBe(8)
        ->and(Service::count())->toBe(2);
});
