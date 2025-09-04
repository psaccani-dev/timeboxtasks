<?php

namespace App\Http\Requests;

use App\Enums\TimeBoxType;
use App\Models\TimeBox;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTimeBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solução 1: Tentar pegar o timeBox de várias formas
        $timeBox = $this->route('time_box')
            ?? $this->route('timeBox')
            ?? $this->route('time-box');

        // Solução 2: Se ainda for null, pegar o ID diretamente e buscar o TimeBox
        if (!$timeBox) {
            // Pega o ID do segmento da URL (time-boxes/{id})
            $timeBoxId = $this->segment(2); // O ID está no segundo segmento

            if ($timeBoxId) {
                $timeBox = TimeBox::find($timeBoxId);
            }
        }

        if (!$timeBox) {
            return false;
        }

        return $this->user()->id === $timeBox->user_id;
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
