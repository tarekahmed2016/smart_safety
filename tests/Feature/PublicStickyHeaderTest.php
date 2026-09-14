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
