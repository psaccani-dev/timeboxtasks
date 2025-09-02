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
