<?php
// app/Services/AnalyticsService.php

namespace App\Services;

use App\Models\{Task, TimeBox};
use App\Enums\{TaskType, TaskPriority, TaskStatus};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected ProductivityService $productivityService;

    public function __construct(ProductivityService $productivityService)
    {
        $this->productivityService = $productivityService;
    }

    /**
     * Calculate all metrics for the analytics dashboard
     */
    public function calculateMetrics($userId, $startDate, $endDate, $prevStartDate, $prevEndDate): array
    {
        // Use single query for current period task stats
        $currentStats = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed
            ', ['done'])
            ->first();

        $tasksCompleted = $currentStats->completed ?? 0;
        $totalTasks = $currentStats->total ?? 0;

        // Previous period stats
        $prevStats = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed
            ', ['done'])
            ->first();

        $prevTasksCompleted = $prevStats->completed ?? 0;

        // Focus hours calculation
        $timeBoxes = TimeBox::where('user_id', $userId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->get();

        $focusHours = $timeBoxes
            ->where('type', 'focus')
            ->sum(function ($tb) {
                return $tb->start_at->diffInMinutes($tb->end_at) / 60;
            });

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

        $streak = $this->productivityService->calculateStreak($userId);
        $productivityScore = $this->productivityService->calculateScore(
            $completionRate,
            $focusHours,
            $streak
        );

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

    /**
     * Get all chart data for analytics dashboard
     */
    public function getChartData($userId, $startDate, $endDate, $period): array
    {
        return [
            'taskTrend' => $this->getTaskTrend($userId, $startDate, $endDate, $period),
            'labels' => $this->getChartLabels($period, $startDate, $endDate),
            'timeDistribution' => $this->getTimeDistribution($userId, $startDate, $endDate),
            'priorityDistribution' => $this->getPriorityDistribution($userId, $startDate, $endDate),
            'productiveHours' => $this->getProductiveHours($userId, $startDate, $endDate),
            'taskTypes' => $this->getTaskTypes($userId, $startDate, $endDate)
        ];
    }

    /**
     * Get task completion trend over time
     */
    public function getTaskTrend($userId, $startDate, $endDate, $period): array
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

    /**
     * Get chart labels based on period
     */
    public function getChartLabels($period, $startDate, $endDate): array
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

    /**
     * Get time distribution by type
     */
    public function getTimeDistribution($userId, $startDate, $endDate): array
    {
        $timeBoxes = TimeBox::where('user_id', $userId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->get();

        $distribution = [
            'focus' => 0,
            'meeting' => 0,
            'break' => 0,
            'study' => 0,
            'work' => 0,
            'other' => 0
        ];

        foreach ($timeBoxes as $tb) {
            $hours = $tb->start_at->diffInMinutes($tb->end_at) / 60;
            $typeValue = $tb->type instanceof \App\Enums\TimeBoxType
                ? $tb->type->value
                : $tb->type;

            $category = match ($typeValue) {
                'focus', 'meeting', 'break', 'study', 'work' => $typeValue,
                default => 'other'
            };

            $distribution[$category] += $hours;
        }

        return [
            ['type' => 'focus', 'label' => 'Focus', 'hours' => round($distribution['focus'], 1), 'color' => 'rgb(96, 165, 250)'],
            ['type' => 'meeting', 'label' => 'Meetings', 'hours' => round($distribution['meeting'], 1), 'color' => 'rgb(168, 85, 247)'],
            ['type' => 'break', 'label' => 'Breaks', 'hours' => round($distribution['break'], 1), 'color' => 'rgb(52, 211, 153)'],
            ['type' => 'study', 'label' => 'Study', 'hours' => round($distribution['study'], 1), 'color' => 'rgb(59, 130, 246)'],
            ['type' => 'work', 'label' => 'Work', 'hours' => round($distribution['work'], 1), 'color' => 'rgb(239, 68, 68)'],
            ['type' => 'other', 'label' => 'Other', 'hours' => round($distribution['other'], 1), 'color' => 'rgb(251, 146, 60)']
        ];
    }

    /**
     * Get priority distribution
     */
    public function getPriorityDistribution($userId, $startDate, $endDate): array
    {
        $tasks = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $total = $tasks->count();
        if ($total === 0) {
            return [];
        }

        $priorities = [
            'urgent' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        foreach ($tasks as $task) {
            $priorityValue = $task->priority instanceof \App\Enums\TaskPriority
                ? $task->priority->value
                : $task->priority;

            if (isset($priorities[$priorityValue])) {
                $priorities[$priorityValue]++;
            }
        }

        return [
            ['name' => 'Urgent', 'count' => $priorities['urgent'], 'percentage' => round(($priorities['urgent'] / $total) * 100), 'colorClass' => 'bg-red-400'],
            ['name' => 'High', 'count' => $priorities['high'], 'percentage' => round(($priorities['high'] / $total) * 100), 'colorClass' => 'bg-orange-400'],
            ['name' => 'Medium', 'count' => $priorities['medium'], 'percentage' => round(($priorities['medium'] / $total) * 100), 'colorClass' => 'bg-yellow-400'],
            ['name' => 'Low', 'count' => $priorities['low'], 'percentage' => round(($priorities['low'] / $total) * 100), 'colorClass' => 'bg-slate-400']
        ];
    }

    /**
     * Get most productive hours
     */
    public function getProductiveHours($userId, $startDate, $endDate): array
    {
        // PostgreSQL version
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

    /**
     * Get task type distribution
     */
    public function getTaskTypes($userId, $startDate, $endDate): array
    {
        $tasks = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $tasksByType = [];
        foreach ($tasks as $task) {
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

        // Sort by count descending
        usort($result, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return $result;
    }

    /**
     * Generate insights based on analytics data
     */
    public function generateInsights($userId, $tasksCompleted, $focusHours, $completionRate): array
    {
        $insights = [];

        // Positive insight for high completion
        if ($tasksCompleted > 10) {
            $insights[] = [
                'id' => 1,
                'type' => 'positive',
                'icon' => 'TrendingUp',
                'title' => 'Great Progress!',
                'message' => "You've completed {$tasksCompleted} tasks this period. Keep up the momentum!"
            ];
        }

        // Warning for low completion rate
        if ($completionRate < 50 && $completionRate > 0) {
            $insights[] = [
                'id' => 2,
                'type' => 'warning',
                'icon' => 'AlertCircle',
                'title' => 'Completion Rate Alert',
                'message' => "Your completion rate is {$completionRate}%. Consider breaking tasks into smaller pieces."
            ];
        }

        // Focus time insights
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

        // Streak achievement
        $streak = $this->productivityService->calculateStreak($userId);
        if ($streak >= 7) {
            $insights[] = [
                'id' => 6,
                'type' => 'positive',
                'icon' => 'Award',
                'title' => 'Consistency Streak!',
                'message' => "Amazing! You've maintained a {$streak}-day streak of completing tasks."
            ];
        }

        // Productivity trend
        $trend = $this->productivityService->calculateTrend($userId);
        if ($trend > 20) {
            $insights[] = [
                'id' => 7,
                'type' => 'positive',
                'icon' => 'TrendingUp',
                'title' => 'Productivity Boost',
                'message' => "Your productivity is up {$trend}% compared to last week!"
            ];
        } elseif ($trend < -20) {
            $insights[] = [
                'id' => 8,
                'type' => 'info',
                'icon' => 'Activity',
                'title' => 'Productivity Dip',
                'message' => "Your productivity is down {$trend}% from last week. Sometimes we need rest!"
            ];
        }

        return $insights;
    }

    /**
     * Get detailed statistics for a specific date range
     */
    public function getDetailedStats($userId, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'tasks' => [
                'total' => Task::where('user_id', $userId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'completed' => Task::where('user_id', $userId)
                    ->where('status', 'done')
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count(),
                'in_progress' => Task::where('user_id', $userId)
                    ->where('status', 'in_progress')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'overdue' => Task::where('user_id', $userId)
                    ->where('status', '!=', 'done')
                    ->where('due_date', '<', Carbon::now())
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count()
            ],
            'timeBoxes' => [
                'total' => TimeBox::where('user_id', $userId)
                    ->whereBetween('start_at', [$startDate, $endDate])
                    ->count(),
                'total_hours' => TimeBox::where('user_id', $userId)
                    ->whereBetween('start_at', [$startDate, $endDate])
                    ->get()
                    ->sum(function ($tb) {
                        return $tb->start_at->diffInMinutes($tb->end_at) / 60;
                    })
            ],
            'averages' => [
                'tasks_per_day' => $this->getAverageTasksPerDay($userId, $startDate, $endDate),
                'completion_time' => $this->productivityService->getAverageCompletionTime($userId),
                'focus_hours_per_day' => $this->getAverageFocusHoursPerDay($userId, $startDate, $endDate)
            ]
        ];
    }

    /**
     * Calculate average tasks completed per day
     */
    private function getAverageTasksPerDay($userId, Carbon $startDate, Carbon $endDate): float
    {
        $days = $startDate->diffInDays($endDate) ?: 1;
        $completedTasks = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        return round($completedTasks / $days, 1);
    }

    /**
     * Calculate average focus hours per day
     */
    private function getAverageFocusHoursPerDay($userId, Carbon $startDate, Carbon $endDate): float
    {
        $days = $startDate->diffInDays($endDate) ?: 1;
        $focusHours = TimeBox::where('user_id', $userId)
            ->where('type', 'focus')
            ->whereBetween('start_at', [$startDate, $endDate])
            ->get()
            ->sum(function ($tb) {
                return $tb->start_at->diffInMinutes($tb->end_at) / 60;
            });

        return round($focusHours / $days, 1);
    }
}
