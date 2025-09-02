<?php
// app/Http/Controllers/TimeBoxController.php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreTimeBoxRequest, UpdateTimeBoxRequest};
use App\Models\{TimeBox, Task, User};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class TimeBoxController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = $user->timeBoxes()->with('tasks');

        // Apply filters
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('start_at', $date);
        } else if ($request->filled('week')) {
            $weekStart = Carbon::parse($request->week)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $query->whereBetween('start_at', [$weekStart, $weekEnd]);
        } else {
            // Default: show current week
            $query->whereBetween('start_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $timeBoxes = $query
            ->orderBy('start_at', 'asc')
            ->get()
            ->map(function ($timeBox) {
                return [
                    'id' => $timeBox->id,
                    'title' => $timeBox->title,
                    'type' => $timeBox->type,
                    'start_at' => $timeBox->start_at->toISOString(),
                    'end_at' => $timeBox->end_at->toISOString(),
                    'allow_overlap' => $timeBox->allow_overlap,
                    'notes' => $timeBox->notes,
                    'tasks' => $timeBox->tasks,
                    'duration_minutes' => $timeBox->start_at->diffInMinutes($timeBox->end_at),
                    'is_active' => $timeBox->start_at <= now() && $timeBox->end_at >= now(),
                    'is_past' => $timeBox->end_at < now(),
                ];
            });

        // Get user's tasks for assignment
        $availableTasks = $user->tasks()
            ->where('status', '!=', 'done')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get(['id', 'title', 'priority', 'estimated_minutes', 'status']);

        return Inertia::render('TimeBoxes/Index', [
            'timeBoxes' => $timeBoxes,
            'availableTasks' => $availableTasks,
            'filters' => $request->only(['date', 'week', 'type']),
            'currentWeek' => [
                'start' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                'end' => Carbon::now()->endOfWeek()->format('Y-m-d'),
            ]
        ]);
    }

    public function store(StoreTimeBoxRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validated();

        // Check for overlaps if not allowed
        if (!($data['allow_overlap'] ?? false)) {
            $overlapping = $user->timeBoxes()
                ->between($data['start_at'], $data['end_at'])
                ->exists();

            if ($overlapping) {
                return back()->withErrors([
                    'start_at' => 'This time slot overlaps with an existing time box.'
                ]);
            }
        }

        $timeBox = $user->timeBoxes()->create($data);

        // Attach tasks if provided
        if (!empty($data['task_ids'])) {
            $timeBox->tasks()->sync($data['task_ids']);
        }

        return redirect()
            ->route('time-boxes.index')
            ->with('message', 'Time box created successfully!');
    }

    public function update(UpdateTimeBoxRequest $request, TimeBox $timeBox)
    {
        $this->authorize('update', $timeBox);

        $data = $request->validated();

        // Check for overlaps if changing time and not allowing overlap
        if (isset($data['start_at']) || isset($data['end_at'])) {
            $start = $data['start_at'] ?? $timeBox->start_at;
            $end = $data['end_at'] ?? $timeBox->end_at;
            $allowOverlap = $data['allow_overlap'] ?? $timeBox->allow_overlap;

            if (!$allowOverlap) {
                $overlapping = $timeBox->user->timeBoxes()
                    ->where('id', '!=', $timeBox->id)
                    ->between($start, $end)
                    ->exists();

                if ($overlapping) {
                    return back()->withErrors([
                        'start_at' => 'This time slot overlaps with an existing time box.'
                    ]);
                }
            }
        }

        $timeBox->update($data);

        // Update tasks if provided
        if (isset($data['task_ids'])) {
            $timeBox->tasks()->sync($data['task_ids']);
        }

        return back()->with('message', 'Time box updated successfully!');
    }

    public function destroy(TimeBox $timeBox)
    {
        $this->authorize('delete', $timeBox);

        $timeBox->delete();

        return back()->with('message', 'Time box deleted successfully!');
    }

    /**
     * Quick update for drag & drop time changes
     */
    public function updateTime(Request $request, TimeBox $timeBox)
    {
        $this->authorize('update', $timeBox);

        $request->validate([
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);

        // Check overlaps
        if (!$timeBox->allow_overlap) {
            $overlapping = $timeBox->user->timeBoxes()
                ->where('id', '!=', $timeBox->id)
                ->between($request->start_at, $request->end_at)
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'message' => 'Time slot overlaps with another time box'
                ], 422);
            }
        }

        $timeBox->update([
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        return response()->json(['message' => 'Time updated successfully']);
    }
}
