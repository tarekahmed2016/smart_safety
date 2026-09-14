<?php

namespace App\Services;

use App\Enums\VisitorEventType;
use App\Models\VisitorEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VisitorStatsService
{
    public function incrementToday(int $uniqueVisitors, int $visits, int $pageViews): void
    {
        $date = now()->toDateString();

        Cache::lock($this->lockKey($date), 5)->block(5, function () use ($date, $uniqueVisitors, $visits, $pageViews) {
            $this->bumpCounter($date, 'unique_visitors', $uniqueVisitors);
            $this->bumpCounter($date, 'visits', $visits);
            $this->bumpCounter($date, 'page_views', $pageViews);
        });
    }

    public function todayUniqueVisitors(): int
    {
        return $this->todayCounts()['unique_visitors'];
    }

    /**
     * @return array{unique_visitors: int, visits: int, page_views: int}
     */
    public function todayCounts(): array
    {
        $date = now()->toDateString();

        return [
            'unique_visitors' => $this->counterValue($date, 'unique_visitors'),
            'visits' => $this->counterValue($date, 'visits'),
            'page_views' => $this->counterValue($date, 'page_views'),
        ];
    }

    /**
     * @return array{
     *     today: array{unique_visitors: int, visits: int, page_views: int},
     *     last_7_days: list<array{date: string, unique_visitors: int, visits: int, page_views: int}>,
     *     this_month: array{unique_visitors: int, visits: int, page_views: int}
     * }
     */
    public function dashboardPayload(): array
    {
        $today = now()->toDateString();
        $ttl = (int) config('visitor-tracking.cache_ttl_seconds', 300);

        return [
            'today' => $this->todayCounts(),
            'last_7_days' => Cache::remember(
                'visitor-stats:last7:'.$today,
                $ttl,
                fn () => $this->lastSevenDaysFromDatabase(),
            ),
            'this_month' => Cache::remember(
                'visitor-stats:month:'.$today,
                $ttl,
                fn () => $this->thisMonthFromDatabase(),
            ),
        ];
    }

    /**
     * @return list<array{date: string, unique_visitors: int, visits: int, page_views: int}>
     */
    private function lastSevenDaysFromDatabase(): array
    {
        $dates = collect(range(6, 0))
            ->map(fn (int $daysAgo) => now()->subDays($daysAgo)->toDateString())
            ->values();

        $counts = $this->countsGroupedByDate($dates->all());

        return $dates
            ->map(fn (string $date) => [
                'date' => $date,
                'unique_visitors' => $counts[$date]['unique_visitors'] ?? 0,
                'visits' => $counts[$date]['visits'] ?? 0,
                'page_views' => $counts[$date]['page_views'] ?? 0,
            ])
            ->all();
    }

    /**
     * @return array{unique_visitors: int, visits: int, page_views: int}
     */
    private function thisMonthFromDatabase(): array
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->toDateString();

        $rows = VisitorEvent::query()
            ->select('event_type', DB::raw('count(*) as aggregate'))
            ->whereBetween('visited_on', [$start, $end])
            ->groupBy('event_type')
            ->pluck('aggregate', 'event_type');

        return $this->mapAggregates($rows->all());
    }

    /**
     * @param  list<string>  $dates
     * @return array<string, array{unique_visitors: int, visits: int, page_views: int}>
     */
    private function countsGroupedByDate(array $dates): array
    {
        $rows = VisitorEvent::query()
            ->select('visited_on', 'event_type', DB::raw('count(*) as aggregate'))
            ->whereIn('visited_on', $dates)
            ->groupBy('visited_on', 'event_type')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $date = substr((string) $row->visited_on, 0, 10);
            $type = $row->event_type instanceof VisitorEventType
                ? $row->event_type->value
                : (string) $row->event_type;

            $result[$date] ??= ['unique_visitors' => 0, 'visits' => 0, 'page_views' => 0];
            $result[$date][$this->metricForEventType($type)] = (int) $row->aggregate;
        }

        return $result;
    }

    private function bumpCounter(string $date, string $metric, int $delta): void
    {
        if ($delta < 1) {
            return;
        }

        $key = $this->counterKey($date, $metric);

        if (Cache::has($key)) {
            Cache::increment($key, $delta);

            return;
        }

        Cache::forever($key, $this->databaseCount($date, $metric));
    }

    private function counterValue(string $date, string $metric): int
    {
        $key = $this->counterKey($date, $metric);
        $cached = Cache::get($key);

        if ($cached !== null) {
            return (int) $cached;
        }

        $count = $this->databaseCount($date, $metric);
        Cache::forever($key, $count);

        return $count;
    }

    private function databaseCount(string $date, string $metric): int
    {
        return VisitorEvent::query()
            ->where('visited_on', $date)
            ->where('event_type', $this->eventTypeForMetric($metric))
            ->count();
    }

    /**
     * @param  array<string, mixed>  $rows
     * @return array{unique_visitors: int, visits: int, page_views: int}
     */
    private function mapAggregates(array $rows): array
    {
        $counts = ['unique_visitors' => 0, 'visits' => 0, 'page_views' => 0];

        foreach ($rows as $type => $aggregate) {
            $metric = $this->metricForEventType((string) $type);
            $counts[$metric] = (int) $aggregate;
        }

        return $counts;
    }

    private function eventTypeForMetric(string $metric): VisitorEventType
    {
        return match ($metric) {
            'unique_visitors' => VisitorEventType::Visitor,
            'visits' => VisitorEventType::Visit,
            default => VisitorEventType::PageView,
        };
    }

    private function metricForEventType(string $eventType): string
    {
        return match ($eventType) {
            VisitorEventType::Visitor->value => 'unique_visitors',
            VisitorEventType::Visit->value => 'visits',
            default => 'page_views',
        };
    }

    private function counterKey(string $date, string $metric): string
    {
        return "visitor-stats:{$date}:{$metric}";
    }

    private function lockKey(string $date): string
    {
        return "visitor-stats:lock:{$date}";
    }
}
