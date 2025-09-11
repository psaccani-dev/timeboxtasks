<?php
// app/Services/ProductivityService.php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;

class ProductivityService
{
    /**
     * Calculate productivity score based on multiple factors, completation Rate, focus Hours e streak 
     */
    public function calculateScore(float $completionRate, float $focusHours, int $streak): int
    {
        // Fórmula ponderada:
        // 40% baseado em taxa de conclusão
        // 30% baseado em horas de foco (normalizado para 100)
        // 30% baseado em consistência/streak (normalizado para 100)

        return min(100, round(
            ($completionRate * 0.3) +
                (min($focusHours / 4, 100) * 0.5) +  // 4 horas = 100%
                (min($streak * 5, 100) * 0.2)        // 20 dias = 100%
        ));
    }

    /**
     * Calculate current streak of consecutive days with completed tasks
     */
    public function calculateStreak(int $userId): int
    {
        $streak = 0;
        $date = Carbon::now()->startOfDay();

        // Verifica até 365 dias para trás
        for ($i = 0; $i < 365; $i++) {
            $hasCompletedTask = Task::where('user_id', $userId)
                ->where('status', 'done')
                ->whereDate('updated_at', $date)
                ->exists();

            if (!$hasCompletedTask) {
                // Se não é o primeiro dia (hoje), o streak acabou
                if ($i > 0) {
                    break;
                }
                // Se é hoje e não tem task completada, streak é 0
                return 0;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }

    /**
     * Calculate productivity trend comparing to previous period
     */
    public function calculateTrend(int $userId): int
    {
        $now = Carbon::now();
        $weekAgo = $now->copy()->subWeek();
        $twoWeeksAgo = $now->copy()->subWeeks(2);

        // Tasks completadas na última semana
        $currentWeekTasks = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$weekAgo, $now])
            ->count();

        // Tasks completadas na semana anterior
        $previousWeekTasks = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$twoWeeksAgo, $weekAgo])
            ->count();

        if ($previousWeekTasks === 0) {
            // Se não tinha tasks antes, qualquer task é 100% de melhora
            return $currentWeekTasks > 0 ? 100 : 0;
        }

        // Calcula percentual de mudança
        return round((($currentWeekTasks - $previousWeekTasks) / $previousWeekTasks) * 100);
    }

    /**
     * Calculate weekly average productivity
     */
    public function calculateWeeklyAverage(int $userId): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Total de tasks criadas na semana
        $totalTasks = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Tasks completadas na semana
        $completedTasks = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        if ($totalTasks === 0) {
            return 0;
        }

        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Get productivity insights based on user data
     */
    public function getProductivityInsights(int $userId): array
    {
        $insights = [];
        $now = Carbon::now();

        // Verifica padrão de horários mais produtivos
        $mostProductiveHour = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereDate('updated_at', '>=', $now->copy()->subDays(30))
            ->selectRaw('EXTRACT(HOUR FROM updated_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        if ($mostProductiveHour) {
            $hour = $mostProductiveHour->hour;
            $insights[] = [
                'type' => 'info',
                'message' => sprintf('You are most productive at %d:00', $hour)
            ];
        }

        // Verifica se há muitas tasks em atraso
        $overdueTasks = Task::where('user_id', $userId)
            ->where('status', '!=', 'done')
            ->where('due_date', '<', $now)
            ->count();

        if ($overdueTasks > 5) {
            $insights[] = [
                'type' => 'warning',
                'message' => sprintf('You have %d overdue tasks. Consider reviewing priorities.', $overdueTasks)
            ];
        }

        // Verifica streak
        $streak = $this->calculateStreak($userId);
        if ($streak > 7) {
            $insights[] = [
                'type' => 'success',
                'message' => sprintf('Great job! %d day streak!', $streak)
            ];
        }

        return $insights;
    }

    /**
     * Calculate estimated time to complete all pending tasks
     */
    public function estimateTimeToComplete(int $userId): array
    {
        $pendingTasks = Task::where('user_id', $userId)
            ->where('status', '!=', 'done')
            ->whereNotNull('estimated_minutes')
            ->sum('estimated_minutes');

        $hours = floor($pendingTasks / 60);
        $minutes = $pendingTasks % 60;

        return [
            'total_minutes' => $pendingTasks,
            'hours' => $hours,
            'minutes' => $minutes,
            'formatted' => sprintf('%dh %dm', $hours, $minutes)
        ];
    }

    /**
     * Get completion rate for different task priorities
     */
    public function getCompletionRateByPriority(int $userId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $priorities = ['urgent', 'high', 'medium', 'low'];
        $rates = [];

        foreach ($priorities as $priority) {
            $total = Task::where('user_id', $userId)
                ->where('priority', $priority)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $completed = Task::where('user_id', $userId)
                ->where('priority', $priority)
                ->where('status', 'done')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $rates[$priority] = [
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100) : 0
            ];
        }

        return $rates;
    }

    /**
     * Calculate average time from creation to completion
     */
    public function getAverageCompletionTime(int $userId): array
    {
        $completedTasks = Task::where('user_id', $userId)
            ->where('status', 'done')
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', Carbon::now()->subDays(30))
            ->get();

        if ($completedTasks->isEmpty()) {
            return [
                'days' => 0,
                'hours' => 0,
                'formatted' => 'No data'
            ];
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($completedTasks as $task) {
            $minutes = $task->created_at->diffInMinutes($task->completed_at);
            $totalMinutes += $minutes;
            $count++;
        }

        $averageMinutes = $totalMinutes / $count;
        $days = floor($averageMinutes / 1440); // 1440 minutes in a day
        $hours = floor(($averageMinutes % 1440) / 60);

        return [
            'days' => $days,
            'hours' => $hours,
            'total_minutes' => $averageMinutes,
            'formatted' => $days > 0 ? sprintf('%d days, %d hours', $days, $hours) : sprintf('%d hours', $hours)
        ];
    }
}
