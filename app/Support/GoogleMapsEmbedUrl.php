<?php

namespace App\Support;

final class GoogleMapsEmbedUrl
{
    public const MAX_LENGTH = 4096;

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public static function isAllowed(mixed $value): bool
    {
        return self::sanitize($value) !== null;
    }

    public static function sanitize(mixed $value): ?string
    {
        $url = self::normalize($value);

        if ($url === null || strlen($url) > self::MAX_LENGTH) {
            return null;
        }

        $lower = strtolower($url);

        if (
            str_contains($lower, '<')
            || str_contains($lower, '>')
            || str_contains($lower, 'iframe')
            || str_contains($lower, 'javascript:')
            || str_contains($lower, 'data:')
            || preg_match('/\s/', $url)
        ) {
            return null;
        }

        if (parse_url($url, PHP_URL_USER) !== null) {
            return null;
        }

        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = (string) parse_url($url, PHP_URL_QUERY);

        if (! self::isAllowedHost($host) || ! self::isAllowedEmbedTarget($path, $query)) {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $url;
    }

    private static function isAllowedHost(string $host): bool
    {
        return (bool) preg_match(
            '/^(?:maps\.)?(?:www\.)?google\.(?:com|com\.[a-z]{2}|co\.[a-z]{2}|[a-z]{2})$/',
            $host,
        );
    }

    private static function isAllowedEmbedTarget(string $path, string $query): bool
    {
        $path = strtolower($path);

        if (! str_starts_with($path, '/maps')) {
            return false;
        }

        if (str_contains($path, '/embed')) {
            return true;
        }

        parse_str(strtolower($query), $params);

        return ($params['output'] ?? '') === 'embed' || array_key_exists('pb', $params);
    }
}
