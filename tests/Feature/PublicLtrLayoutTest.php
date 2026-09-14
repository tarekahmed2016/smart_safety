<?php

test('english public header and hero layout rules are scoped to ltr only', function () {
    $styles = file_get_contents(resource_path('css/plastex.css'));

    expect($styles)->toContain('html[dir="ltr"] .px-nav-links')
        ->and($styles)->toContain('flex: 1 1 auto')
        ->and($styles)->toContain('min-width: 0')
        ->and($styles)->toContain('white-space: nowrap')
        ->and($styles)->toContain('@media (min-width: 1280px) and (max-width: 1679px)')
        ->and($styles)->toContain('@media (min-width: 1680px)')
        ->and($styles)->toContain('html[dir="ltr"] .px-hero-content')
        ->and($styles)->toContain('overflow-wrap: anywhere')
        ->and($styles)->toContain('html[dir="ltr"] .px-hero-title')
        ->and($styles)->toContain('clamp(1.55rem, 1.1rem + 1.6vw, 2.55rem)')
        ->and($styles)->toContain('html[dir="ltr"] .px-hero-actions')
        ->and($styles)->toContain('flex-wrap: wrap')
        ->and($styles)->toContain('html[dir="ltr"] .plastex-site > main')
        ->and($styles)->toContain('overflow-x: clip');

    expect($styles)->toContain('[dir="rtl"] .px-hero-content')
        ->and($styles)->toContain('width: min(36rem, 42vw)');
});

test('english hamburger appears before header overlap and keeps all nav links', function () {
    $styles = file_get_contents(resource_path('css/plastex.css'));
    $navbar = file_get_contents(resource_path('js/Components/Public/PublicNavbar.vue'));

    expect($styles)->toContain('html[dir="ltr"] .px-nav-toggle')
        ->and($styles)->toContain('html[dir="ltr"] .px-mobile-nav')
        ->and($navbar)->toContain('class="px-mobile-nav"')
        ->and($navbar)->toContain('v-for="link in navLinks"')
        ->and($navbar)->toContain('px-nav-bar')
        ->and($navbar)->toContain('px-nav-actions');
});
