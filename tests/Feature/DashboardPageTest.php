<?php

use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard source keeps quick links above visitor stats and hides the details table', function () {
    $source = file_get_contents(resource_path('js/Pages/Dashboard/IndexPage.vue'));
    $template = Str::after($source, '<template>');

    expect($source)->toContain('const showVisitorDetailsTable = false')
        ->and($source)->toContain('v-if="showVisitorDetailsTable"')
        ->and($source)->toContain('VisitorStatsChart')
        ->and($source)->toContain('last7Days')
        ->and($template)->toContain('dashboard-quick-links')
        ->and($template)->toContain('dashboard-visitor-stats')
        ->and($template)->toContain('dashboard-visitor-chart')
        ->and($template)->toContain('dashboard-visitor-details-table')
        ->and(strpos($template, 'dashboard-quick-links'))
        ->toBeLessThan(strpos($template, 'dashboard-visitor-stats'))
        ->and(strpos($template, 'dashboard-visitor-chart'))
        ->toBeLessThan(strpos($template, 'dashboard-visitor-details-table'));
});

test('dashboard still receives last seven days data while the details table stays hidden in source', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/IndexPage', false)
            ->has('visitorStats.today')
            ->has('visitorStats.last_7_days', 7)
            ->has('visitorStats.this_month'));

    $source = file_get_contents(resource_path('js/Pages/Dashboard/IndexPage.vue'));

    expect($source)->toContain('const showVisitorDetailsTable = false');
});

test('visitor chart dates use unpadded day/month and chronological left-to-right order', function () {
    $chart = file_get_contents(resource_path('js/Components/Dashboard/VisitorStatsChart.vue'));
    $util = file_get_contents(resource_path('js/Utils/formatVisitorChartDate.js'));

    expect($util)->toContain('return `${day}/${month}`')
        ->and($util)->not->toContain("padStart")
        ->and($util)->toContain('localeCompare')
        ->and($chart)->toContain('dir="ltr"')
        ->and($chart)->toContain('formatVisitorChartDate')
        ->and($chart)->toContain('visitorChartDaysForPlot')
        ->and($chart)->not->toContain('isRtl.value ? 1 - ratio');

    $script = <<<'JS'
import { formatVisitorChartDate, visitorChartDaysForPlot } from './resources/js/Utils/formatVisitorChartDate.js';

const formatted = [
    formatVisitorChartDate('2026-09-08'),
    formatVisitorChartDate('2026-09-09'),
    formatVisitorChartDate('2026-09-10'),
    formatVisitorChartDate('2026-09-14'),
];

const plotted = visitorChartDaysForPlot([
    { date: '2026-09-14', unique_visitors: 4 },
    { date: '2026-09-08', unique_visitors: 1 },
    { date: '2026-09-13', unique_visitors: 3 },
    { date: '2026-09-09', unique_visitors: 2 },
]);

console.log(JSON.stringify({
    formatted,
    dates: plotted.map((day) => day.date),
    labels: plotted.map((day) => formatVisitorChartDate(day.date)),
    values: plotted.map((day) => day.unique_visitors),
}));
JS;

    $result = json_decode(
        Illuminate\Support\Facades\Process::path(base_path())->run(['node', '--input-type=module', '-e', $script])->throw()->output(),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($result['formatted'])->toBe(['8/9', '9/9', '10/9', '14/9'])
        ->and($result['formatted'])->not->toContain('08/09')
        ->and($result['formatted'])->not->toContain('14/09')
        ->and($result['dates'])->toBe(['2026-09-08', '2026-09-09', '2026-09-13', '2026-09-14'])
        ->and($result['labels'])->toBe(['8/9', '9/9', '13/9', '14/9'])
        ->and($result['values'])->toBe([1, 2, 3, 4]);
});
