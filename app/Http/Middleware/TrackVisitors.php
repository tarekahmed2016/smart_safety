<?php

namespace App\Http\Middleware;

use App\Services\VisitorIdentityService;
use App\Services\VisitorTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    public function __construct(
        public VisitorTrackingService $visitorTrackingService,
        public VisitorIdentityService $visitorIdentityService,
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $identity = null;

        if ($this->shouldTrack($request)) {
            $identity = $this->visitorTrackingService->track($request);
        }

        $response = $next($request);

        if ($identity === null) {
            return $response;
        }

        $response->headers->setCookie(
            $this->visitorIdentityService->visitorCookie($request, $identity->visitorCookieId)
        );
        $response->headers->setCookie(
            $this->visitorIdentityService->sessionCookie($request, $identity->sessionCookieId)
        );

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return false;
        }

        if ($request->is('api', 'api/*')) {
            return false;
        }

        if ($request->headers->has('X-Inertia-Partial-Data')) {
            return false;
        }

        $purpose = strtolower((string) $request->headers->get('Purpose', $request->headers->get('Sec-Purpose', '')));
        if (str_contains($purpose, 'prefetch')) {
            return false;
        }

        if ($this->isExcludedPath($request)) {
            return false;
        }

        if ($this->isStaticAsset($request)) {
            return false;
        }

        if ($this->isBot($request->userAgent())) {
            return false;
        }

        return true;
    }

    private function isExcludedPath(Request $request): bool
    {
        foreach (config('visitor-tracking.excluded_path_prefixes', []) as $prefix) {
            if ($request->is($prefix) || $request->is($prefix.'/*')) {
                return true;
            }
        }

        return false;
    }

    private function isStaticAsset(Request $request): bool
    {
        $path = strtolower($request->path());
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            return false;
        }

        return in_array($extension, config('visitor-tracking.excluded_extensions', []), true);
    }

    private function isBot(?string $userAgent): bool
    {
        if ($userAgent === null || trim($userAgent) === '') {
            return true;
        }

        $haystack = strtolower($userAgent);

        foreach (config('visitor-tracking.bot_user_agents', []) as $needle) {
            if (str_contains($haystack, strtolower((string) $needle))) {
                return true;
            }
        }

        return false;
    }
}
