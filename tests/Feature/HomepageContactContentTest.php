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

test('contact detail icons have a uniform size scoped to the contact details list', function () {
    $home = file_get_contents(resource_path('js/Pages/Public/HomePage.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));

    expect($home)->toContain('class="px-contact-details"')
        ->and($home)->toContain('PlastexLineIcon name="phone"')
        ->and($home)->toContain('PlastexLineIcon name="envelope"')
        ->and($home)->toContain('PlastexLineIcon name="location-dot"')
        ->and($home)->toContain('PlastexLineIcon name="whatsapp"')
        ->and($styles)->toContain('.px-contact-details .px-contact-icon')
        ->and($styles)->toContain('flex: 0 0 auto')
        ->and($styles)->toContain('width: 44px')
        ->and($styles)->toContain('height: 44px')
        ->and($styles)->toContain('width: 40px')
        ->and($styles)->toContain('height: 40px')
        ->and($styles)->toContain('width: 21px')
        ->and($styles)->toContain('text-align: start');

    expect($styles)->toContain('.px-floating-action')
        ->and($styles)->not->toContain('.px-floating-action .px-contact-icon');
});
