<?php

use App\Enums\HomepagePromoType;

test('feature highlight promo type does not support an action button', function () {
    expect(HomepagePromoType::FeatureHighlight->supportsAction())->toBeFalse();
});

test('promo types that render a public button support action fields', function () {
    expect(HomepagePromoType::FeatureBand->supportsAction())->toBeTrue()
        ->and(HomepagePromoType::PromoStrip->supportsAction())->toBeTrue()
        ->and(HomepagePromoType::BusinessCta->supportsAction())->toBeTrue()
        ->and(HomepagePromoType::CustomManufacturing->supportsAction())->toBeTrue();
});

test('non-button promo types hide action fields', function () {
    expect(HomepagePromoType::Industry->supportsAction())->toBeFalse()
        ->and(HomepagePromoType::Stat->supportsAction())->toBeFalse()
        ->and(HomepagePromoType::AboutHighlight->supportsAction())->toBeFalse()
        ->and(HomepagePromoType::WhyUsHighlight->supportsAction())->toBeFalse();
});
