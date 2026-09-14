<?php

namespace App\Services;

use App\Enums\VisitorEventType;
use App\Models\VisitorEvent;
use App\Support\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorTrackingService
{
    public function __construct(
        public VisitorIdentityService $identityService,
        public VisitorStatsService $statsService,
    ) {}

    public function track(Request $request): VisitorIdentity
    {
        $identity = $this->identityService->resolve($request);

        $recorded = DB::transaction(fn () => $this->recordEvents($request, $identity));

        $this->statsService->incrementToday(
            uniqueVisitors: $recorded['unique_visitor'] ? 1 : 0,
            visits: $recorded['visit'] ? 1 : 0,
            pageViews: 1,
        );

        return $identity;
    }

    /**
     * @return array{unique_visitor: bool, visit: bool}
     */
    private function recordEvents(Request $request, VisitorIdentity $identity): array
    {
        $now = now();
        $visitedOn = $now->toDateString();
        $path = $this->identityService->abbreviatePath($request);

        $uniqueVisitor = $this->recordUniqueVisitor($identity, $visitedOn, $now);
        [$visit, $sessionHash] = $this->recordVisit($identity, $visitedOn, $now);

        VisitorEvent::query()->create([
            'visitor_hash' => $identity->visitorHash,
            'session_hash' => $sessionHash,
            'visited_on' => $visitedOn,
            'last_seen_at' => $now,
            'path' => $path,
            'event_type' => VisitorEventType::PageView,
        ]);

        VisitorEvent::query()
            ->where('visitor_hash', $identity->visitorHash)
            ->where('visited_on', $visitedOn)
            ->where('event_type', VisitorEventType::Visitor)
            ->update(['last_seen_at' => $now]);

        return [
            'unique_visitor' => $uniqueVisitor,
            'visit' => $visit,
        ];
    }

    private function recordUniqueVisitor(VisitorIdentity $identity, string $visitedOn, Carbon $now): bool
    {
        $exists = VisitorEvent::query()
            ->where('visitor_hash', $identity->visitorHash)
            ->where('visited_on', $visitedOn)
            ->where('event_type', VisitorEventType::Visitor)
            ->exists();

        if ($exists) {
            return false;
        }

        VisitorEvent::query()->create([
            'visitor_hash' => $identity->visitorHash,
            'session_hash' => $identity->sessionHash,
            'visited_on' => $visitedOn,
            'last_seen_at' => $now,
            'path' => null,
            'event_type' => VisitorEventType::Visitor,
        ]);

        return true;
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function recordVisit(VisitorIdentity $identity, string $visitedOn, Carbon $now): array
    {
        if (! $identity->isNewSession) {
            VisitorEvent::query()
                ->where('session_hash', $identity->sessionHash)
                ->where('event_type', VisitorEventType::Visit)
                ->update(['last_seen_at' => $now]);

            return [false, $identity->sessionHash];
        }

        $recentVisit = VisitorEvent::query()
            ->where('visitor_hash', $identity->visitorHash)
            ->where('event_type', VisitorEventType::Visit)
            ->where('last_seen_at', '>=', $now->copy()->subMinutes(
                (int) config('visitor-tracking.cookie.session_minutes', 30)
            ))
            ->latest('last_seen_at')
            ->first();

        if ($recentVisit) {
            $recentVisit->update(['last_seen_at' => $now]);

            return [false, $recentVisit->session_hash];
        }

        VisitorEvent::query()->create([
            'visitor_hash' => $identity->visitorHash,
            'session_hash' => $identity->sessionHash,
            'visited_on' => $visitedOn,
            'last_seen_at' => $now,
            'path' => null,
            'event_type' => VisitorEventType::Visit,
        ]);

        return [true, $identity->sessionHash];
    }
}
