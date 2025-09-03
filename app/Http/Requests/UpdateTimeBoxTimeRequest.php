<?php
// app/Http/Requests/UpdateTimeBoxTimeRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeBoxTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('timeBox')->user_id;
    }

    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_at.after' => 'End time must be after start time.',
        ];
    }
}
