<?php

namespace App\Http\Controllers;

use App\Services\VisitorStatsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(public VisitorStatsService $visitorStatsService) {}

    public function __invoke(): Response
    {
        return Inertia::render('Dashboard/IndexPage', [
            'visitorStats' => $this->visitorStatsService->dashboardPayload(),
        ]);
    }
}
