<?php

namespace App\Services;

use App\Support\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie;

class VisitorIdentityService
{
    public function resolve(Request $request): VisitorIdentity
    {
        $visitorCookieName = (string) config('visitor-tracking.cookie.name');
        $sessionCookieName = (string) config('visitor-tracking.cookie.session_name');

        $incomingVisitorId = (string) $request->cookies->get($visitorCookieName, '');
        $hasValidVisitorCookie = $this->isValidId($incomingVisitorId);
        $visitorCookieId = $hasValidVisitorCookie
            ? $incomingVisitorId
            : Uuid::uuid5(Uuid::NAMESPACE_OID, $this->fallbackVisitorHash($request))->toString();

        $visitorHash = $this->hashValue('visitor', $visitorCookieId);

        $incomingSessionId = (string) $request->cookies->get($sessionCookieName, '');
        $hasValidSessionCookie = $this->isValidId($incomingSessionId);
        $sessionCookieId = $hasValidSessionCookie ? $incomingSessionId : (string) Str::uuid();

        return new VisitorIdentity(
            visitorHash: $visitorHash,
            sessionHash: $this->hashValue('session', $sessionCookieId),
            visitorCookieId: $visitorCookieId,
            sessionCookieId: $sessionCookieId,
            isNewVisitorCookie: ! $hasValidVisitorCookie,
            isNewSession: ! $hasValidSessionCookie,
        );
    }

    public function visitorCookie(Request $request, string $visitorId): Cookie
    {
        return $this->makeCookie(
            request: $request,
            name: (string) config('visitor-tracking.cookie.name'),
            value: $visitorId,
            minutes: (int) config('visitor-tracking.cookie.minutes'),
        );
    }

    public function sessionCookie(Request $request, string $sessionId): Cookie
    {
        return $this->makeCookie(
            request: $request,
            name: (string) config('visitor-tracking.cookie.session_name'),
            value: $sessionId,
            minutes: (int) config('visitor-tracking.cookie.session_minutes'),
        );
    }

    public function hashValue(string $namespace, string $value): string
    {
        return hash_hmac('sha256', $namespace.'|'.$value, $this->secret());
    }

    public function fallbackVisitorHash(Request $request): string
    {
        $date = now()->toDateString();
        $dailySalt = hash_hmac('sha256', 'daily-salt|'.$date, $this->secret());
        $ipDigest = hash('sha256', (string) $request->ip());
        $userAgentDigest = hash('sha256', $this->userAgentFingerprint($request->userAgent()));

        return hash_hmac('sha256', implode('|', [
            'fallback',
            $ipDigest,
            $userAgentDigest,
            $dailySalt,
        ]), $this->secret());
    }

    public function abbreviatePath(Request $request): string
    {
        $path = '/'.ltrim($request->getPathInfo(), '/');
        $maxLength = (int) config('visitor-tracking.path_max_length', 80);

        if (strlen($path) <= $maxLength) {
            return $path;
        }

        return substr($path, 0, $maxLength);
    }

    private function makeCookie(Request $request, string $name, string $value, int $minutes): Cookie
    {
        return cookie(
            name: $name,
            value: $value,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: (bool) config('visitor-tracking.cookie.http_only', true),
            raw: false,
            sameSite: (string) config('visitor-tracking.cookie.same_site', 'lax'),
        );
    }

    private function isValidId(string $value): bool
    {
        return Str::isUuid($value);
    }

    private function userAgentFingerprint(?string $userAgent): string
    {
        $normalized = Str::lower(trim((string) $userAgent));

        if ($normalized === '') {
            return 'unknown';
        }

        return substr($normalized, 0, 80);
    }

    private function secret(): string
    {
        return (string) config('app.key');
    }
}
