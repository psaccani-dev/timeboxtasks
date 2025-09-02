<?php
// app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreTaskRequest, UpdateTaskRequest};
use App\Models\{Task, TimeBox, User};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', Task::class);

        /** @var User $user */
        $user = Auth::user();
        $tasks = $user->tasks()
            ->with('timeBoxes')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks
        ]);
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
        $task = $user->tasks()->create(
            $request->validated()
        );

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
            ->route('tasks.index', $task)
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

    // Método adicional para update rápido de status
    public function updateStatus(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => $request->validated('status')
        ]);

        return back()->with('message', 'Task status updated!');
    }

    // Método para ver time boxes de uma task
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
