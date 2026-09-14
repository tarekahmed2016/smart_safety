<?php

namespace App\Http\Controllers;

use App\Services\VisitorStatsService;
use Illuminate\Http\JsonResponse;

class VisitorStatsController extends Controller
{
    public function __construct(public VisitorStatsService $visitorStatsService) {}

    public function __invoke(): JsonResponse
    {
        return response()->json($this->visitorStatsService->dashboardPayload());
    }
}
