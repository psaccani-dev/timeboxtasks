<?php
// app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreTaskRequest, UpdateTaskRequest};
use App\Models\{Task, TimeBox, User};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        /** @var User $user */
        $user = Auth::user();

        $query = $user->tasks()->with('timeBoxes');

        $filters = $request->only(['status', 'priority', 'due_filter', 'search']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('due_filter')) {
            $now = now();
            switch ($request->due_filter) {
                case 'overdue':
                    $query->where('due_date', '<', $now)
                        ->where('status', '!=', 'done');
                    break;
                case 'today':
                    $query->whereDate('due_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('due_date', [
                        $now->startOfDay(),
                        $now->copy()->addDays(7)->endOfDay()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('due_date', [
                        $now->startOfDay(),
                        $now->copy()->addMonth()->endOfDay()
                    ]);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        if (in_array($sortBy, ['created_at', 'due_date', 'priority', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $tasks = $query->paginate(30)->withQueryString();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'stats' => $this->getTaskStats($user)
        ]);
    }

    private function getTaskStats(User $user)
    {
        $now = now();

        return [
            'total' => $user->tasks()->count(),
            'in_progress' => $user->tasks()->where('status', 'in_progress')->count(),
            'completed' => $user->tasks()->where('status', 'done')->count(),
            'due_today' => $user->tasks()
                ->whereDate('due_date', $now->toDateString())
                ->where('status', '!=', 'done')
                ->count(),
        ];
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load('timeBoxes');

        return Inertia::render('Tasks/Show', [
            'task' => $task
        ]);
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        return Inertia::render('Tasks/Create');
    }

    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        /** @var User $user */
        $user = Auth::user();

        $task = $user->tasks()->create($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('message', 'Task created successfully!');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return Inertia::render('Tasks/Edit', [
            'task' => $task
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('message', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('message', 'Task deleted successfully!');
    }

    public function updateStatus(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => $request->validated('status')
        ]);

        return back()->with('message', 'Task status updated!');
    }

    public function timeBoxes(Task $task)
    {
        $this->authorize('view', $task);

        $timeBoxes = $task->timeBoxes()
            ->orderBy('start_at')
            ->get();

        return Inertia::render('Tasks/TimeBoxes', [
            'task' => $task,
            'timeBoxes' => $timeBoxes
        ]);
    }
}
