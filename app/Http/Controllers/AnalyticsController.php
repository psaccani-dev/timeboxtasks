<?php
// app/Http/Controllers/AnalyticsController.php

namespace App\Http\Controllers;

use App\Models\{Task, TimeBox};
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');

        $endDate = Carbon::now();
        $startDate = match ($period) {
            '7d' => $endDate->copy()->subDays(7),
            '30d' => $endDate->copy()->subDays(30),
            '90d' => $endDate->copy()->subDays(90),
            '1y' => $endDate->copy()->subYear(),
            default => $endDate->copy()->subDays(30)
        };

        $periodDays = $startDate->diffInDays($endDate);
        $prevStartDate = $startDate->copy()->subDays($periodDays);
        $prevEndDate = $startDate->copy();

        $metrics = $this->analyticsService->calculateMetrics(
            $user->id,
            $startDate,
            $endDate,
            $prevStartDate,
            $prevEndDate
        );

        $chartData = $this->analyticsService->getChartData(
            $user->id,
            $startDate,
            $endDate,
            $period
        );

        $insights = $this->analyticsService->generateInsights(
            $user->id,
            $metrics['tasksCompleted']['value'],
            $metrics['focusHours']['value'],
            $metrics['completionRate']['value']
        );

        return Inertia::render('Analytics/Index', [
            'metrics' => $metrics,
            'chartData' => $chartData,
            'insights' => $insights,
            'currentPeriod' => $period
        ]);
    }
}
