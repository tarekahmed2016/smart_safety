<?php

test('generic contact CTAs reset the form after a product inquiry', function () {
    $home = file_get_contents(resource_path('js/Pages/Public/HomePage.vue'));
    $layout = file_get_contents(resource_path('js/Layouts/PublicLayout.vue'));
    $composable = file_get_contents(resource_path('js/Composables/usePublicContactForm.js'));
    $navbar = file_get_contents(resource_path('js/Components/Public/PublicNavbar.vue'));

    expect($home)->toContain('const resetContactForm')
        ->and($home)->toContain("inquiredProductName.value = ''")
        ->and($home)->toContain('contactForm.name = \'\'')
        ->and($home)->toContain('contactForm.email = \'\'')
        ->and($home)->toContain('contactForm.phone = \'\'')
        ->and($home)->toContain('contactForm.subject = \'\'')
        ->and($home)->toContain('contactForm.message = \'\'')
        ->and($home)->toContain('contactForm.reset()')
        ->and($home)->toContain('contactForm.clearErrors()')
        ->and($home)->toContain("contactForm.subject = t('public.home.products.inquireSubject'")
        ->and($home)->toContain("contactForm.message = t('public.home.products.inquireMessage'")
        ->and($home)->toContain('inquiredProductName.value = name')
        ->and($layout)->toContain('handlePublicContactLinkClick')
        ->and($composable)->toContain('export function handlePublicContactLinkClick')
        ->and($composable)->toContain('openPublicContactForm({ reset: true')
        ->and($composable)->toContain("scrollIntoView({ behavior: 'smooth'")
        ->and($composable)->not->toContain("addEventListener('scroll'")
        ->and($home)->not->toContain("addEventListener('scroll'")
        ->and($navbar)->toContain('#contact');

    preg_match('/const inquireAboutProduct = \(product\) => \{.*?\n\}/s', $home, $inquireMatch);
    preg_match('/const handlePublicContactOpen = \(event\) => \{.*?\n\}/s', $home, $openMatch);

    expect($inquireMatch[0] ?? '')->toContain("contactForm.message = t('public.home.products.inquireMessage'")
        ->and($inquireMatch[0] ?? '')->not->toContain('resetContactForm(')
        ->and($openMatch[0] ?? '')->toContain('resetContactForm()');
});
