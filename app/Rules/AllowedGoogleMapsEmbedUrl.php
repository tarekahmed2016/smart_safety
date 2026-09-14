<?php

namespace App\Rules;

use App\Support\GoogleMapsEmbedUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedGoogleMapsEmbedUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || GoogleMapsEmbedUrl::sanitize($value) === null) {
            $fail('The Google Maps embed URL must be a Google Maps iframe src link, not full HTML.');
        }
    }
}
