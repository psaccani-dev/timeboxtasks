<?php

namespace App\Http\Controllers;

use App\Http\Requests\{StoreTimeBoxRequest, UpdateTimeBoxRequest};
use App\Models\{Task, TimeBox, User};
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TimeBoxController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', TimeBox::class);

        /** @var User $user */
        $user = Auth::user();
        $timeBoxes = $user->timeBoxes()
            ->with('tasks')
            ->orderBy('start_at', 'desc')
            ->paginate(15);

        return Inertia::render('TimeBoxes/Index', [
            'timeBoxes' => $timeBoxes
        ]);
    }

    public function show(TimeBox $timeBox)
    {
        $this->authorize('view', $timeBox);

        $timeBox->load('tasks');

        return Inertia::render('TimeBoxes/Show', [
            'timeBox' => $timeBox
        ]);
    }

    public function create()
    {
        $this->authorize('create', TimeBox::class);

        /** @var User $user */
        $user = Auth::user();
        // Buscar tasks do usuário para associar
        $tasks = $user->tasks()
            ->where('status', '!=', 'done')
            ->select('id', 'title', 'type', 'priority')
            ->get();

        return Inertia::render('TimeBoxes/Create', [
            'tasks' => $tasks
        ]);
    }

    public function store(StoreTimeBoxRequest $request)
    {
        $this->authorize('create', TimeBox::class);

        $validated = $request->validated();
        $taskIds = $validated['task_ids'] ?? [];
        unset($validated['task_ids']);

        /** @var User $user */
        $user = Auth::user();
        $timeBox = $user->timeBoxes()->create($validated);

        // Associar tasks se fornecidas
        if (!empty($taskIds)) {
            $timeBox->tasks()->attach($taskIds);
        }

        return redirect()
            ->route('time-boxes.show', $timeBox)
            ->with('message', 'Time box created successfully!');
    }

    public function edit(TimeBox $timeBox)
    {
        $this->authorize('update', $timeBox);

        $timeBox->load('tasks');

        /** @var User $user */
        $user = Auth::user();
        $availableTasks = $user->tasks()
            ->where('status', '!=', 'done')
            ->select('id', 'title', 'type', 'priority')
            ->get();

        return Inertia::render('TimeBoxes/Edit', [
            'timeBox' => $timeBox,
            'availableTasks' => $availableTasks
        ]);
    }

    public function update(UpdateTimeBoxRequest $request, TimeBox $timeBox)
    {
        $this->authorize('update', $timeBox);

        $validated = $request->validated();
        $taskIds = $validated['task_ids'] ?? [];
        unset($validated['task_ids']);

        $timeBox->update($validated);

        // Sincronizar tasks se fornecidas
        if (isset($request->validated()['task_ids'])) {
            $timeBox->tasks()->sync($taskIds);
        }

        return redirect()
            ->route('time-boxes.show', $timeBox)
            ->with('message', 'Time box updated successfully!');
    }

    public function destroy(TimeBox $timeBox)
    {
        $this->authorize('delete', $timeBox);

        $timeBox->delete();

        return redirect()
            ->route('time-boxes.index')
            ->with('message', 'Time box deleted successfully!');
    }
}
