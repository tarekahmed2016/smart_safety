<?php

use Illuminate\Support\Str;

test('public header stays sticky in document flow on all public pages', function () {
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $appStyles = file_get_contents(resource_path('css/app.css'));
    $navbar = file_get_contents(resource_path('js/Components/Public/PublicNavbar.vue'));
    $layout = file_get_contents(resource_path('js/Layouts/PublicLayout.vue'));

    $headerBlock = Str::between($styles, '.px-header {', '}');
    $layoutStart = strpos($layout, '<PublicNavbar');
    $mainStart = strpos($layout, '<main');

    expect($headerBlock)->toContain('position: sticky')
        ->and($headerBlock)->toContain('top: 0')
        ->and($headerBlock)->toContain('z-index: 50')
        ->and($headerBlock)->toContain('background: #fff')
        ->and($navbar)->not->toContain('position: fixed')
        ->and($styles)->toContain('.px-header--scrolled')
        ->and($navbar)->toContain("class=\"px-header\"")
        ->and($navbar)->toContain('px-header--scrolled')
        ->and($navbar)->toContain('px-nav-toggle')
        ->and($navbar)->toContain('px-mobile-nav')
        ->and($appStyles)->not->toContain(".public-layout {\n    overflow-x: hidden")
        ->and($layout)->not->toContain('transform:')
        ->and($layoutStart)->not->toBeFalse()
        ->and($mainStart)->not->toBeFalse()
        ->and($layoutStart)->toBeLessThan($mainStart);
});

test('public header always shows arabic and english company names regardless of locale', function () {
    $navbar = file_get_contents(resource_path('js/Components/Public/PublicNavbar.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));

    $arabicNameStart = strpos($navbar, 'class="px-nav-brand-name-ar"');
    $englishNameStart = strpos($navbar, 'class="px-nav-brand-name-en"');
    $localeGuardedArabic = preg_match(
        '/v-(?:if|show|else-if)="locale === [\'"]ar[\'"]"/',
        $navbar
    );

    expect($navbar)->toContain('{{ companyNameAr }}')
        ->and($navbar)->toContain('{{ companyNameEn }}')
        ->and($navbar)->toContain('class="px-nav-brand-name-ar"')
        ->and($navbar)->toContain('class="px-nav-brand-name-en"')
        ->and($arabicNameStart)->not->toBeFalse()
        ->and($englishNameStart)->not->toBeFalse()
        ->and($arabicNameStart)->toBeLessThan($englishNameStart)
        ->and($localeGuardedArabic)->toBe(0)
        ->and($navbar)->not->toContain("companyInfo.value.name_ar || t('public.home.defaultCompanyName')")
        ->and($styles)->toContain('unicode-bidi: isolate')
        ->and($styles)->toContain('text-align: start');
});
