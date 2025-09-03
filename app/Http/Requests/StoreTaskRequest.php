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
            'due_date' => ['nullable', 'date'],
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
