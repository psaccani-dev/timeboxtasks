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
