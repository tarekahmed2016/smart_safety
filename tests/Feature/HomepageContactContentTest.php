<?php

use App\Models\CompanyInfo;
use App\Support\HomepageContactContentDefaults;
use App\Support\HomepageContentDefaults;
use Inertia\Testing\AssertableInertia as Assert;

test('homepage contact copy matches the original site wording', function () {
    $copy = HomepageContactContentDefaults::companyFields();

    CompanyInfo::create([
        'name_ar' => 'الصناعة الإبداعية',
        'name_en' => 'Creative Industry',
        'products_homepage_limit' => 8,
        ...$copy,
    ]);

    expect(HomepageContentDefaults::companyInfoFields())->toMatchArray($copy)
        ->and($copy['contact_section_title_ar'])->toBe('هل لديك مشروع في ذهنك؟')
        ->and($copy['contact_section_subtitle_ar'])->toBe('نحن هنا لتحويل أفكارك الصناعية إلى واقع ملموس بأعلى معايير الجودة.');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('companyInfo.contact_section_title_ar', $copy['contact_section_title_ar'])
            ->where('companyInfo.contact_section_title_en', $copy['contact_section_title_en'])
            ->where('companyInfo.contact_section_subtitle_ar', $copy['contact_section_subtitle_ar'])
            ->where('companyInfo.contact_section_subtitle_en', $copy['contact_section_subtitle_en']));
});
