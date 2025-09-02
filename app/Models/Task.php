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
