<?php

namespace App\Support;

final readonly class VisitorIdentity
{
    public function __construct(
        public string $visitorHash,
        public string $sessionHash,
        public string $visitorCookieId,
        public string $sessionCookieId,
        public bool $isNewVisitorCookie,
        public bool $isNewSession,
    ) {}
}
