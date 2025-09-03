<?php

// app/Http/Controllers/AnalyticsController.php - VERSIONE COMPLETA CON DATI REALI

namespace App\Http\Controllers;

use App\Models\{Task, TimeBox};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');

        // Calculate date range
        $endDate = Carbon::now();
        $startDate = match ($period) {
            '7d' => $endDate->copy()->subDays(7),
            '30d' => $endDate->copy()->subDays(30),
            '90d' => $endDate->copy()->subDays(90),
            '1y' => $endDate->copy()->subYear(),
            default => $endDate->copy()->subDays(30)
        };

        // Previous period for comparison
        $periodDays = $startDate->diffInDays($endDate);
        $prevStartDate = $startDate->copy()->subDays($periodDays);
        $prevEndDate = $startDate->copy();

        // REAL METRICS
        $metrics = $this->calculateMetrics($user->id, $startDate, $endDate, $prevStartDate, $prevEndDate);

        // REAL CHART DATA
        $chartData = [
            'taskTrend' => $this->getTaskTrend($user->id, $startDate, $endDate, $period),
            'labels' => $this->getChartLabels($period, $startDate, $endDate),
            'timeDistribution' => $this->getTimeDistribution($user->id, $startDate, $endDate),
            'priorityDistribution' => $this->getPriorityDistribution($user->id, $startDate, $endDate),
            'productiveHours' => $this->getProductiveHours($user->id, $startDate, $endDate),
            'taskTypes' => $this->getTaskTypes($user->id, $startDate, $endDate)
        ];

        // REAL INSIGHTS
        $insights = $this->generateInsights(
            $user->id,
            $metrics['tasksCompleted']['value'],
            $metrics['focusHours']['value'],
            $metrics['completionRate']['value']
        );

        return Inertia::render('Analytics/Index', [
            'metrics' => $metrics,
            'chartData' => $chartData,
            'insights' => $insights
        ]);
    }

    private function calculateMetrics($userId, $startDate, $endDate, $prevStartDate, $prevEndDate)
    {
        // Current period
        $tasksCompleted = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $totalTasks = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $timeBoxes = TimeBox::where('user_id', $userId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->get();

        $focusHours = $timeBoxes
            ->where('type', 'focus')
            ->sum(function ($tb) {
                return $tb->start_at->diffInMinutes($tb->end_at) / 60;
            });

        // Previous period
        $prevTasksCompleted = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$prevStartDate, $prevEndDate])
            ->count();

        $prevFocusHours = TimeBox::where('user_id', $userId)
            ->where('type', 'focus')
            ->whereBetween('start_at', [$prevStartDate, $prevEndDate])
            ->get()
            ->sum(function ($tb) {
                return $tb->start_at->diffInMinutes($tb->end_at) / 60;
            });

        // Calculate changes
        $tasksChange = $prevTasksCompleted > 0
            ? round((($tasksCompleted - $prevTasksCompleted) / $prevTasksCompleted) * 100)
            : 0;

        $focusChange = $prevFocusHours > 0
            ? round((($focusHours - $prevFocusHours) / $prevFocusHours) * 100)
            : 0;

        $completionRate = $totalTasks > 0
            ? round(($tasksCompleted / $totalTasks) * 100)
            : 0;

        $streak = $this->calculateStreak($userId);

        $productivityScore = min(100, round(
            ($completionRate * 0.4) +
                (min($focusHours / 4, 100) * 0.3) +
                (min($streak * 5, 100) * 0.3)
        ));

        return [
            'tasksCompleted' => [
                'value' => $tasksCompleted,
                'change' => $tasksChange
            ],
            'focusHours' => [
                'value' => round($focusHours, 1),
                'change' => $focusChange
            ],
            'completionRate' => [
                'value' => $completionRate,
                'change' => 0
            ],
            'productivity' => [
                'value' => $productivityScore,
                'change' => 0
            ],
            'streak' => [
                'value' => $streak,
                'change' => 0
            ]
        ];
    }

    private function getTaskTrend($userId, $startDate, $endDate, $period)
    {
        $data = [];
        $days = $period === '7d' ? 7 : ($period === '30d' ? 30 : 90);
        $interval = $days <= 7 ? 1 : ($days <= 30 ? 7 : 30);

        for ($i = 0; $i < min($days, 7); $i++) {
            $date = $startDate->copy()->addDays($i * $interval);
            $endInterval = $date->copy()->addDays($interval);

            $completed = Task::where('user_id', $userId)
                ->where('status', 'done')
                ->whereBetween('updated_at', [$date, $endInterval])
                ->count();

            $data[] = ['date' => $date->format('Y-m-d'), 'value' => $completed];
        }

        return $data;
    }

    private function getChartLabels($period, $startDate, $endDate)
    {
        if ($period === '7d') {
            $labels = [];
            for ($i = 0; $i < 7; $i++) {
                $labels[] = $startDate->copy()->addDays($i)->format('D');
            }
            return $labels;
        } else if ($period === '30d') {
            return ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        }
        return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    }

    private function getTimeDistribution($userId, $startDate, $endDate)
    {
        $timeBoxes = TimeBox::where('user_id', $userId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->get();

        $distribution = [
            'focus' => 0,
            'meeting' => 0,
            'break' => 0,
            'other' => 0
        ];

        foreach ($timeBoxes as $tb) {
            $hours = $tb->start_at->diffInMinutes($tb->end_at) / 60;
            $type = in_array($tb->type, ['focus', 'meeting', 'break']) ? $tb->type : 'other';
            $distribution[$type] += $hours;
        }

        return [
            ['type' => 'focus', 'label' => 'Focus', 'hours' => round($distribution['focus'], 1), 'color' => 'rgb(96, 165, 250)'],
            ['type' => 'meeting', 'label' => 'Meetings', 'hours' => round($distribution['meeting'], 1), 'color' => 'rgb(168, 85, 247)'],
            ['type' => 'break', 'label' => 'Breaks', 'hours' => round($distribution['break'], 1), 'color' => 'rgb(52, 211, 153)'],
            ['type' => 'other', 'label' => 'Other', 'hours' => round($distribution['other'], 1), 'color' => 'rgb(251, 146, 60)']
        ];
    }

    private function getPriorityDistribution($userId, $startDate, $endDate)
    {
        $tasks = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $total = $tasks->count();
        if ($total === 0) {
            return [];
        }

        $priorities = [
            'urgent' => $tasks->where('priority', 'urgent')->count(),
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count()
        ];

        return [
            ['name' => 'Urgent', 'count' => $priorities['urgent'], 'percentage' => round(($priorities['urgent'] / $total) * 100), 'colorClass' => 'bg-red-400'],
            ['name' => 'High', 'count' => $priorities['high'], 'percentage' => round(($priorities['high'] / $total) * 100), 'colorClass' => 'bg-orange-400'],
            ['name' => 'Medium', 'count' => $priorities['medium'], 'percentage' => round(($priorities['medium'] / $total) * 100), 'colorClass' => 'bg-yellow-400'],
            ['name' => 'Low', 'count' => $priorities['low'], 'percentage' => round(($priorities['low'] / $total) * 100), 'colorClass' => 'bg-slate-400']
        ];
    }

    private function getProductiveHours($userId, $startDate, $endDate)
    {
        // Versione PostgreSQL
        $hourlyTasks = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('EXTRACT(HOUR FROM updated_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        if ($hourlyTasks->isEmpty()) {
            return [];
        }

        $maxCount = $hourlyTasks->first()->count ?? 1;

        return $hourlyTasks->map(function ($item) use ($maxCount) {
            $hour = intval($item->hour);
            $label = sprintf(
                '%d:00 - %d:00 %s',
                $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour),
                ($hour + 1) > 12 ? ($hour + 1) - 12 : (($hour + 1) == 0 ? 12 : ($hour + 1)),
                $hour >= 12 ? 'PM' : 'AM'
            );

            return [
                'hour' => sprintf('%02d:00', $hour),
                'label' => $label,
                'tasks' => $item->count,
                'percentage' => round(($item->count / $maxCount) * 100)
            ];
        })->toArray();
    }

    private function getTaskTypes($userId, $startDate, $endDate)
    {
        $tasks = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Raggruppa per tipo e conta
        $tasksByType = [];
        foreach ($tasks as $task) {
            // Ottieni il valore stringa dall'enum
            $typeValue = $task->type instanceof \App\Enums\TaskType
                ? $task->type->value
                : $task->type;

            if (!isset($tasksByType[$typeValue])) {
                $tasksByType[$typeValue] = 0;
            }
            $tasksByType[$typeValue]++;
        }

        $icons = [
            'study' => 'BookOpen',
            'work' => 'Briefcase',
            'question' => 'HelpCircle',
            'quick_note' => 'StickyNote',
            'reminder' => 'Bell',
            'house' => 'Home',
            'random' => 'Shuffle'
        ];

        $colors = [
            'study' => 'text-indigo-400',
            'work' => 'text-orange-400',
            'question' => 'text-blue-400',
            'quick_note' => 'text-pink-400',
            'reminder' => 'text-purple-400',
            'house' => 'text-yellow-400',
            'random' => 'text-slate-400'
        ];

        $result = [];
        foreach ($tasksByType as $type => $count) {
            $result[] = [
                'name' => ucfirst(str_replace('_', ' ', $type)),
                'count' => $count,
                'icon' => $icons[$type] ?? 'Circle',
                'colorClass' => $colors[$type] ?? 'text-slate-400'
            ];
        }

        return $result;
    }

    private function calculateStreak($userId)
    {
        $streak = 0;
        $date = Carbon::now()->startOfDay();

        for ($i = 0; $i < 365; $i++) {
            $hasTask = Task::where('user_id', $userId)
                ->where('status', 'done')
                ->whereDate('updated_at', $date)
                ->exists();

            if (!$hasTask) {
                // Se non è oggi, lo streak è finito
                if ($i > 0) break;
                // Se è oggi e non ci sono task, controlla ieri
                return 0;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }

    private function generateInsights($userId, $tasksCompleted, $focusHours, $completionRate)
    {
        $insights = [];

        // Positive insight
        if ($tasksCompleted > 10) {
            $insights[] = [
                'id' => 1,
                'type' => 'positive',
                'icon' => 'TrendingUp',
                'title' => 'Great Progress!',
                'message' => "You've completed {$tasksCompleted} tasks this period. Keep up the momentum!"
            ];
        }

        // Warning for low completion
        if ($completionRate < 50 && $completionRate > 0) {
            $insights[] = [
                'id' => 2,
                'type' => 'warning',
                'icon' => 'AlertCircle',
                'title' => 'Completion Rate Alert',
                'message' => "Your completion rate is {$completionRate}%. Consider breaking tasks into smaller pieces."
            ];
        }

        // Focus time insight
        if ($focusHours > 10) {
            $insights[] = [
                'id' => 3,
                'type' => 'info',
                'icon' => 'Lightbulb',
                'title' => 'Focus Champion',
                'message' => sprintf("You've logged %.1f hours of focused work. Great dedication!", $focusHours)
            ];
        } elseif ($focusHours < 5) {
            $insights[] = [
                'id' => 4,
                'type' => 'warning',
                'icon' => 'Clock',
                'title' => 'Schedule More Focus Time',
                'message' => 'Try to schedule more dedicated focus sessions to boost productivity.'
            ];
        }

        // Check for overdue tasks
        $overdueTasks = Task::where('user_id', $userId)
            ->where('status', '!=', 'done')
            ->where('due_date', '<', Carbon::now())
            ->count();

        if ($overdueTasks > 0) {
            $insights[] = [
                'id' => 5,
                'type' => 'warning',
                'icon' => 'AlertCircle',
                'title' => 'Overdue Tasks',
                'message' => "You have {$overdueTasks} overdue task(s) that need attention."
            ];
        }

        return $insights;
    }
}
