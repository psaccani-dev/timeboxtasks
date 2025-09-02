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
