<?php
// app/Http/Controllers/TimeBoxController
namespace App\Http\Controllers;

use App\Http\Requests\{StoreTimeBoxRequest, UpdateTimeBoxRequest, UpdateTimeBoxTimeRequest};
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

        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('start_at', $date);
        } else if ($request->filled('week')) {
            $weekStart = Carbon::parse($request->week)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();
            $query->whereBetween('start_at', [$weekStart, $weekEnd]);
        } else {
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
        $data['user_id'] = $user->id;

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

        $taskIds = $data['task_ids'] ?? [];
        unset($data['task_ids']);

        $timeBox = TimeBox::create($data);

        if (!empty($taskIds)) {
            $validTaskIds = $user->tasks()->whereIn('id', $taskIds)->pluck('id');
            $timeBox->tasks()->sync($validTaskIds);
        }

        return redirect()
            ->route('time-boxes.index')
            ->with('message', 'Time box created successfully!');
    }

    public function update(UpdateTimeBoxRequest $request, TimeBox $timeBox)
    {
        $this->authorize('update', $timeBox);

        $data = $request->validated();

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

    public function calendar(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        $timeBoxes = $user->timeBoxes()
            ->with('tasks')
            ->whereBetween('start_at', [$startDate, $endDate])
            ->orderBy('start_at')
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

        $tasks = $user->tasks()
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get(['id', 'title', 'priority', 'status', 'due_date', 'estimated_minutes']);

        $availableTasks = $user->tasks()
            ->where('status', '!=', 'done')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get(['id', 'title', 'priority', 'estimated_minutes', 'status']);

        return Inertia::render('Calendar/Index', [
            'timeBoxes' => $timeBoxes,
            'tasks' => $tasks,
            'availableTasks' => $availableTasks,
            'currentMonth' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ]
        ]);
    }

    public function updateTime(UpdateTimeBoxTimeRequest $request, TimeBox $timeBox)
    {
        $this->authorize('update', $timeBox);

        if (!$timeBox->allow_overlap) {
            $overlapping = $timeBox->user->timeBoxes()
                ->where('id', '!=', $timeBox->id)
                ->between($request->start_at, $request->end_at)
                ->exists();

            if ($overlapping) {
                return back()->withErrors([
                    'start_at' => 'Time slot overlaps with another time box'
                ]);
            }
        }

        $timeBox->update([
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        return redirect()->back();
    }
}
