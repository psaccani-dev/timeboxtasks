Contexto do projeto : 
Task/Timeboxing APP

Tecnologias : Laravel 12 + Inertia + Vue 3 (script setup), STARTER KIT LARAVEL, VITE e shadcn-vue (Tailwind).

DB POSTGRESL , redis 


Eloquent com relationships explícitas, enums em PHP 8.1+ para status/priority, SoftDeletes quando útil.





Tables/Migrations : 

Tasks: 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title', 160);
            $table->text('description')->nullable();

            // enums (armazenados como string, cast no Model)
            $table->string('type', 32)->index();     // App\Enums\TaskType
            $table->string('status', 32)->index();   // App\Enums\TaskStatus
            $table->string('priority', 16)->index(); // App\Enums\TaskPriority

            $table->dateTime('due_date')->nullable();                 // UTC
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->unsignedSmallInteger('actual_minutes')->nullable();

            $table->json('labels')->nullable();       // tags livres
            $table->integer('order_index')->default(0);
            $table->dateTime('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};


Timebox: 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_boxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title', 160);
            $table->string('type', 32)->index(); // App\Enums\TimeBoxType
            $table->dateTime('start_at');        // UTC
            $table->dateTime('end_at');          // UTC

            $table->boolean('allow_overlap')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'start_at']);
            $table->index(['user_id', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_boxes');
    }
};



Task time box :

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_time_box', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_box_id')->constrained()->cascadeOnDelete();
            $table->unique(['task_id', 'time_box_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_time_box');
    }
};


User settings : 

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 500)->nullable();
            $table->string('theme', 20)->default('dark');
            $table->string('language', 10)->default('en');
            $table->string('date_format', 20)->default('MM/DD/YYYY');
            $table->string('time_format', 10)->default('12h');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'theme', 'language', 'date_format', 'time_format']);
        });
    }
};



ENUMS : 

<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';
}


<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case DONE = 'done';
}

<?php

namespace App\Enums;

enum TaskType: string
{
    case STUDY = 'study';
    case WORK = 'work';
    case QUESTION = 'question';
    case QUICK_NOTE = 'quick_note';
    case REMINDER = 'reminder';
    case HOUSE = 'house';
    case RANDOM = 'random';
}

<?php

namespace App\Enums;



namespace App\Enums;

enum TimeBoxType: string
{
    case FOCUS = 'focus';
    case MEETING = 'meeting';
    case BREAK = 'break';
    case STUDY = 'study';
    case CUSTOM = 'custom';
    case WORK = 'work';
    case HOUSE = 'house';
    case RANDOM = 'random';
}



MODELS : 


<?php

namespace App\Models;

use App\Enums\{TaskPriority, TaskStatus, TaskType};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'due_date',
        'estimated_minutes',
        'actual_minutes',
        'labels',
        'order_index',
        'completed_at',
    ];

    protected $casts = [
        'type'          => TaskType::class,
        'status'        => TaskStatus::class,
        'priority'      => TaskPriority::class,
        'labels'        => 'array',
        'due_date'      => 'datetime',
        'completed_at'  => 'datetime',
    ];

    /** Relations */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeBoxes(): BelongsToMany
    {
        return $this->belongsToMany(TimeBox::class, 'task_time_box')->withTimestamps();
    }

    /** Scopes */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, TaskStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeByType($query, TaskType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeByPriority($query, TaskPriority $priority)
    {
        return $query->where('priority', $priority->value);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', '!=', TaskStatus::DONE->value);
    }

    public function scopeDueUntil($query, $date)
    {
        return $query->where('due_date', '<=', $date);
    }
}



<?php

namespace App\Models;

use App\Enums\TimeBoxType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeBox extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'start_at',
        'end_at',
        'allow_overlap',
        'notes',
    ];

    protected $casts = [
        'type'          => TimeBoxType::class,
        'start_at'      => 'datetime',
        'end_at'        => 'datetime',
        'allow_overlap' => 'boolean',
    ];

    /** Relations */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_time_box')->withTimestamps();
    }

    /** Scopes */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Overlapping-friendly interval: pega blocos que tocam o intervalo [from, to)
     */
    public function scopeBetween($query, $from, $to)
    {
        return $query
            ->where('start_at', '<', $to)
            ->where('end_at', '>', $from);
    }

    public function scopeOfType($query, TimeBoxType $type)
    {
        return $query->where('type', $type->value);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'theme',
        'language',
        'date_format',
        'time_format',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the tasks for the user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the time boxes for the user.
     */
    public function timeBoxes(): HasMany
    {
        return $this->hasMany(TimeBox::class);
    }
}


CONTROLLERS : 

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

        // Apply filters
        $filters = $request->only(['status', 'priority', 'due_filter', 'search']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Due date filter
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

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
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



        // Add user_id explicitly
        $data['user_id'] = $user->id;

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

        // Extract task_ids before creating
        $taskIds = $data['task_ids'] ?? [];
        unset($data['task_ids']);

        $timeBox = TimeBox::create($data);

        // Attach tasks if provided
        if (!empty($taskIds)) {
            // Verify tasks belong to user
            $validTaskIds = $user->tasks()->whereIn('id', $taskIds)->pluck('id')->toArray();
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
     * Calendar view - monthly display
     */
    public function calendar(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Get date range for the month
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        // Get time boxes for the month - convert to array
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
                    'tasks' => $timeBox->tasks->toArray(), // Convert tasks to array too
                    'duration_minutes' => $timeBox->start_at->diffInMinutes($timeBox->end_at),
                    'is_active' => $timeBox->start_at <= now() && $timeBox->end_at >= now(),
                    'is_past' => $timeBox->end_at < now(),
                ];
            })
            ->values(); // Reset array keys


        // Get tasks with due dates in the month - convert to array
        $tasks = $user->tasks()
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get(['id', 'title', 'priority', 'status', 'due_date', 'estimated_minutes']);
        // Convert to array

        // Get all tasks for selection - convert to array
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
                return back()->withErrors([
                    'message' => 'Time slot overlaps with another time box'
                ]);
            }
        }

        $timeBox->update([
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        // Simply redirect back and let the index method handle the filtering
        return redirect()->back();
    }
}



<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\TimeBox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get user settings from database or use defaults
        $settings = [
            'theme' => $user->theme ?? 'dark',
            'language' => $user->language ?? 'en',
            'dateFormat' => $user->date_format ?? 'MM/DD/YYYY',
            'timeFormat' => $user->time_format ?? '12h',
        ];

        return Inertia::render('Settings/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio ?? '',
            ],
            'settings' => $settings
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'bio' => 'nullable|string|max:500'
        ]);

        $userId = Auth::id();

        // Update using DB Facade
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'name' => $request->name,
                'email' => $request->email,
                'bio' => $request->bio,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'theme' => 'nullable|in:light,dark,system',
            'language' => 'nullable|in:en,pt,es,fr,it,de',
            'dateFormat' => 'nullable|in:MM/DD/YYYY,DD/MM/YYYY,YYYY-MM-DD',
            'timeFormat' => 'nullable|in:12h,24h'
        ]);

        $userId = Auth::id();
        $updateData = [];

        // Build update array only with provided fields
        if ($request->has('theme')) {
            $updateData['theme'] = $request->theme;
        }
        if ($request->has('language')) {
            $updateData['language'] = $request->language;
            session(['locale' => $request->language]);
            app()->setLocale($request->language);
        }
        if ($request->has('dateFormat')) {
            $updateData['date_format'] = $request->dateFormat;
        }
        if ($request->has('timeFormat')) {
            $updateData['time_format'] = $request->timeFormat;
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = now();

            DB::table('users')
                ->where('id', $userId)
                ->update($updateData);
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    public function changeLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,pt,es,fr,it,de'
        ]);

        $userId = Auth::id();

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'language' => $request->language,
                'updated_at' => now()
            ]);

        session(['locale' => $request->language]);
        app()->setLocale($request->language);

        // Retornar resposta Inertia ao invés de JSON
        return redirect()->back()->with('success', 'Language updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors([
                'current_password' => 'The current password is incorrect.'
            ]);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function exportData()
    {
        $userId = Auth::id();
        $user = User::find($userId);

        // Collect user basic data
        $userData = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio ?? '',
                'created_at' => $user->created_at
            ]
        ];

        // Get tasks using Eloquent relationship
        $tasks = $user->tasks()->get()->map(function ($task) {
            return [
                'title' => $task->title ?? '',
                'description' => $task->description ?? '',
                'status' => $task->status ?? 'pending',
                'priority' => $task->priority ?? 'medium',
                'due_date' => $task->due_date ?? null,
                'created_at' => $task->created_at,
                'completed_at' => $task->completed_at ?? null
            ];
        });
        $userData['tasks'] = $tasks;

        // Get time boxes using Eloquent relationship
        $timeBoxes = $user->timeBoxes()->get()->map(function ($timeBox) {
            return [
                'title' => $timeBox->title ?? '',
                'start_time' => $timeBox->start_time ?? null,
                'end_time' => $timeBox->end_time ?? null,
                'duration' => $timeBox->duration ?? 0,
                'created_at' => $timeBox->created_at
            ];
        });
        $userData['timeBoxes'] = $timeBoxes;

        $userData['exported_at'] = now()->toISOString();

        $json = json_encode($userData, JSON_PRETTY_PRINT);
        $filename = 'taskflow-export-' . now()->format('Y-m-d') . '.json';

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function deleteAccount(Request $request)
    {
        // Optional: Add confirmation validation
        if ($request->has('confirmation')) {
            $request->validate([
                'confirmation' => 'required|in:DELETE'
            ]);
        }

        $userId = Auth::id();

        // Delete related data using DB Facades
        DB::table('tasks')->where('user_id', $userId)->delete();
        DB::table('time_boxes')->where('user_id', $userId)->delete();

        // You can add more tables here as needed
        // For example: comments, notifications, etc.

        // Logout user
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Delete user account
        DB::table('users')->where('id', $userId)->delete();

        return redirect('/')->with('message', 'Account deleted successfully');
    }
}

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




ROTAS 


<?php

use App\Http\Controllers\{AnalyticsController, DashboardController, SettingsController, TaskController, TimeBoxController};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->middleware(['auth'])
    ->name('analytics.index');

// Rotas protegidas por auth
Route::middleware(['auth', 'verified'])->group(function () {

    // Tasks - Resource routes
    Route::resource('tasks', TaskController::class);

    // TimeBoxes - Resource routes  
    Route::resource('time-boxes', TimeBoxController::class);
    Route::patch('/time-boxes/{timeBox}/time', [TimeBoxController::class, 'updateTime'])->name('time-boxes.update-time');
    Route::get('/calendar', [TimeBoxController::class, 'calendar'])->name('calendar.index');
    // Rotas adicionais específicas (se necessário futuramente)
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::get('tasks/{task}/time-boxes', [TaskController::class, 'timeBoxes'])->name('tasks.time-boxes');

    Route::prefix('app-settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('app-settings.index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('app-settings.profile');
        Route::post('/update', [SettingsController::class, 'updateSettings'])->name('app-settings.update');
        Route::post('/language', [SettingsController::class, 'changeLanguage'])->name('app-settings.language');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('app-settings.password');
        Route::get('/export', [SettingsController::class, 'exportData'])->name('app-settings.export');
        Route::delete('/delete-account', [SettingsController::class, 'deleteAccount'])->name('app-settings.delete-account');
    });
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';





Policies, Requests, e Services / PAGES te mando a medida que forem possiveis, assim voce ja consegue dar uma olhada





Requests

<?php
// app/Http/Requests/StoreTaskRequest.php

namespace App\Http\Requests;

use App\Enums\{TaskPriority, TaskStatus, TaskType};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // MVP: sempre autorizado (user autenticado)
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'type' => ['required', new Enum(TaskType::class)],
            'status' => ['sometimes', new Enum(TaskStatus::class)],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date', 'after:now'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Define status padrão se não informado
        if (!$this->has('status')) {
            $this->merge(['status' => TaskStatus::TODO->value]);
        }
    }
}



<?php

namespace App\Http\Requests;

use App\Enums\TimeBoxType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTimeBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', new Enum(TimeBoxType::class)],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'allow_overlap' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
        ];
    }
}


<?php

namespace App\Http\Requests;

use App\Enums\{TaskPriority, TaskStatus, TaskType};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('task')->user_id;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', new Enum(TaskType::class)],
            'status' => ['sometimes', new Enum(TaskStatus::class)],
            'priority' => ['sometimes', new Enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'actual_minutes' => ['nullable', 'integer', 'min:1'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['string', 'max:50'],
        ];
    }
}


<?php

namespace App\Http\Requests;

use App\Enums\TimeBoxType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTimeBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('timeBox')->user_id;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:160'],
            'type' => ['sometimes', new Enum(TimeBoxType::class)],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after:start_at'],
            'allow_overlap' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
        ];
    }
}


Policies:

<?php

namespace App\Policies;

use App\Models\{Task, User};

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // MVP: user autenticado pode ver suas tasks
    }

    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}


TimeBoxPolicy:

<?php

namespace App\Policies;

use App\Models\{TimeBox, User};

class TimeBoxPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function delete(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function restore(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }

    public function forceDelete(User $user, TimeBox $timeBox): bool
    {
        return $user->id === $timeBox->user_id;
    }
}




