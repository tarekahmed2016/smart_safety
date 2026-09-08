<?php

use App\Support\CompanyNameBrandTypography;

test('company name brand typography resolves separate font family defaults', function () {
    $resolved = CompanyNameBrandTypography::resolvedFor((object) [
        'company_name_font_family_ar' => null,
        'company_name_font_family_en' => '',
    ]);

    expect($resolved['font_family_ar'])->toBe(CompanyNameBrandTypography::DEFAULT_FONT_FAMILY_AR)
        ->and($resolved['font_family_en'])->toBe(CompanyNameBrandTypography::DEFAULT_FONT_FAMILY_EN);
});

test('company name brand typography keeps custom font families per language', function () {
    $resolved = CompanyNameBrandTypography::resolvedFor((object) [
        'company_name_font_family_ar' => 'Amiri, serif',
        'company_name_font_family_en' => 'Roboto, sans-serif',
    ]);

    expect($resolved['font_family_ar'])->toBe('Amiri, serif')
        ->and($resolved['font_family_en'])->toBe('Roboto, sans-serif');
});
