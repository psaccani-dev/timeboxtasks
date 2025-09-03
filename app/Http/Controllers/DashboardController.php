<?php


// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeBox;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        // Today's stats - usando query builder direttamente
        $todayTasks = Task::where('user_id', $user->id)
            ->whereDate('due_date', $now->toDateString())
            ->get();

        $todayCompleted = $todayTasks->where('status', 'done')->count();
        $todayTotal = $todayTasks->count();
        $todayProgress = $todayTotal > 0 ? round(($todayCompleted / $todayTotal) * 100) : 0;

        // Active time box
        $activeTimeBox = TimeBox::where('user_id', $user->id)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->with('tasks')
            ->first();

        if ($activeTimeBox) {
            $duration = $activeTimeBox->start_at->diffInMinutes($activeTimeBox->end_at);
            $elapsed = $activeTimeBox->start_at->diffInMinutes($now);
            $progress = round(($elapsed / $duration) * 100);
            $remaining = $activeTimeBox->end_at->diffForHumans($now, true);

            $activeTimeBox = [
                'id' => $activeTimeBox->id,
                'title' => $activeTimeBox->title,
                'progress' => $progress,
                'remaining' => $remaining
            ];
        }

        // Week stats
        $weekTasks = Task::where('user_id', $user->id)
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->get();

        $weekTimeBoxes = TimeBox::where('user_id', $user->id)
            ->whereBetween('start_at', [$startOfWeek, $endOfWeek])
            ->get();

        $focusHours = $weekTimeBoxes
            ->where('type', 'focus')
            ->sum(function ($tb) {
                return $tb->start_at->diffInMinutes($tb->end_at);
            }) / 60;

        // Today's schedule
        $todaySchedule = TimeBox::where('user_id', $user->id)
            ->whereDate('start_at', $now->toDateString())
            ->orderBy('start_at')
            ->get()
            ->map(function ($tb) use ($now) {
                $isNow = $tb->start_at <= $now && $tb->end_at >= $now;
                return [
                    'id' => $tb->id,
                    'title' => $tb->title,
                    'type' => $tb->type,
                    'time' => $tb->start_at->format('H:i'),
                    'duration' => $tb->start_at->diffInMinutes($tb->end_at),
                    'isNow' => $isNow
                ];
            });

        // Upcoming tasks
        $upcomingTasks = Task::where('user_id', $user->id)
            ->where('status', '!=', 'done')
            ->where(function ($q) use ($now) {
                $q->whereNull('due_date')
                    ->orWhere('due_date', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get(['id', 'title', 'priority', 'status', 'due_date']);

        // Activity data for chart
        $activityData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayTasks = Task::where('user_id', $user->id)
                ->whereDate('updated_at', $date)
                ->count();
            $dayCompleted = Task::where('user_id', $user->id)
                ->whereDate('updated_at', $date)
                ->where('status', 'done')
                ->count();

            $dayTimeBoxes = TimeBox::where('user_id', $user->id)
                ->whereDate('start_at', $date)
                ->get();

            $dayHours = $dayTimeBoxes->sum(function ($tb) {
                return $tb->start_at->diffInMinutes($tb->end_at);
            }) / 60;

            $activityData[] = [
                'date' => $date->toDateString(),
                'label' => $date->format('D'),
                'total' => $dayTasks,
                'completed' => $dayCompleted,
                'hours' => round($dayHours, 1),
                'percentage' => min(100, $dayHours * 10) // Scale for display
            ];
        }

        return Inertia::render('Dashboard/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'stats' => [
                'todayCompleted' => $todayCompleted,
                'todayTotal' => $todayTotal,
                'todayProgress' => $todayProgress,
                'weekStreak' => 3, // Calculate actual streak
                'productivityScore' => 78,
                'productivityTrend' => 12
            ],
            'activeTimeBox' => $activeTimeBox,
            'todaySchedule' => $todaySchedule,
            'upcomingTasks' => $upcomingTasks,
            'weekStats' => [
                'tasksCompleted' => $weekTasks->where('status', 'done')->count(),
                'focusHours' => round($focusHours, 1),
                'timeBoxes' => $weekTimeBoxes->count(),
                'avgProductivity' => 82
            ],
            'activityData' => $activityData
        ]);
    }
}
