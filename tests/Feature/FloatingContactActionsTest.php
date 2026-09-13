<?php

test('public layout mounts floating contact actions on all public pages', function () {
    $layout = file_get_contents(resource_path('js/Layouts/PublicLayout.vue'));
    $app = file_get_contents(resource_path('js/app.js'));
    $component = file_get_contents(resource_path('js/Components/Public/FloatingContactActions.vue'));

    expect($layout)->toContain('FloatingContactActions')
        ->and($layout)->toContain('<FloatingContactActions />')
        ->and($app)->toContain("name.startsWith('Public/')")
        ->and($app)->toContain('page.default.layout = PublicLayout')
        ->and($component)->toContain('useSocialLinks')
        ->and($component)->toContain('floatingSocialLinks')
        ->and($component)->toContain('px-floating-social')
        ->and($component)->toContain('px-back-to-top');
});

test('floating social icons render only configured company links and open in a new tab', function () {
    $component = file_get_contents(resource_path('js/Components/Public/FloatingContactActions.vue'));
    $composable = file_get_contents(resource_path('js/Composables/useSocialLinks.js'));
    $styles = file_get_contents(resource_path('css/plastex.css'));

    expect($composable)->toContain("['whatsapp', 'instagram', 'linkedin', 'facebook', 'tiktok']")
        ->and($composable)->toContain('filter((link) => Boolean(link.url))')
        ->and($composable)->toContain('FLOATING_SOCIAL_KEYS.map((key) => byKey[key]).filter(Boolean)')
        ->and($component)->toContain('v-if="floatingSocialLinks.length"')
        ->and($component)->toContain('v-for="link in floatingSocialLinks"')
        ->and($component)->toContain(':href="link.url"')
        ->and($component)->toContain('target="_blank"')
        ->and($component)->toContain('rel="noopener noreferrer"')
        ->and($component)->toContain(':aria-label="link.label"')
        ->and($component)->toContain(':data-tooltip="link.label"')
        ->and($component)->not->toContain('https://wa.me')
        ->and($component)->not->toContain('https://facebook.com')
        ->and($component)->not->toContain('https://instagram.com')
        ->and($styles)->toContain('position: fixed')
        ->and($styles)->toContain('left: 20px')
        ->and($styles)->toContain('bottom: 20px')
        ->and($styles)->toContain('right: 20px')
        ->and($styles)->toContain('gap: 8px')
        ->and($styles)->toContain('flex-direction: column-reverse')
        ->and($styles)->toContain('background: transparent');
});

test('back to top button stays on the physical right and scrolls smoothly', function () {
    $component = file_get_contents(resource_path('js/Components/Public/FloatingContactActions.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $ar = file_get_contents(resource_path('js/Plugins/I18n/Locales/ar.json'));
    $en = file_get_contents(resource_path('js/Plugins/I18n/Locales/en.json'));

    expect($component)->toContain('type="button"')
        ->and($component)->toContain('class="px-floating-action px-back-to-top"')
        ->and($component)->toContain("t('public.home.floating.backToTop')")
        ->and($component)->toContain("window.scrollTo({ top: 0, behavior: 'smooth' })")
        ->and($component)->not->toContain('type="submit"')
        ->and($styles)->toContain('.px-back-to-top')
        ->and($styles)->toContain('right: 20px')
        ->and($styles)->toContain('bottom: 20px')
        ->and($styles)->toContain('z-index: 45')
        ->and($styles)->toContain('border-radius: 50%')
        ->and($ar)->toContain('"backToTop": "العودة إلى الأعلى"')
        ->and($en)->toContain('"backToTop": "Back to top"');
});

test('floating contact actions keep physical left and right sides on rtl and mobile', function () {
    $component = file_get_contents(resource_path('js/Components/Public/FloatingContactActions.vue'));
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $productsIndex = file_get_contents(resource_path('js/Pages/Public/ProductsIndex.vue'));
    $customPage = file_get_contents(resource_path('js/Pages/Public/CustomPage.vue'));

    expect($component)->toContain('dir="ltr"')
        ->and($styles)->toContain('.px-floating-social')
        ->and($styles)->toContain('left: 20px')
        ->and($styles)->not->toContain('inset-inline-start: 0.85rem')
        ->and($styles)->not->toContain('inset-inline-end: 0.85rem')
        ->and($styles)->toContain('@media (max-width: 639px)')
        ->and($styles)->toContain('left: 12px')
        ->and($styles)->toContain('bottom: 16px')
        ->and($styles)->toContain('right: 12px')
        ->and($productsIndex)->toContain('layout: PublicLayout')
        ->and($customPage)->toContain('layout: PublicLayout');
});
