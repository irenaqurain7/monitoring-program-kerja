<?php

namespace App\Http\Requests;

use App\Models\WorkProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeWorkProgramStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        return $program instanceof WorkProgram
            && $this->user()->can('changeStatus', $program);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in($this->allowedTransitions()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status program wajib dipilih.',
            'status.in' => 'Perubahan status program tersebut tidak diizinkan.',
        ];
    }

    private function allowedTransitions(): array
    {
        return match ($this->route('program')->status) {
            WorkProgram::STATUS_DRAFT => [
                WorkProgram::STATUS_ACTIVE,
                WorkProgram::STATUS_POSTPONED,
            ],
            WorkProgram::STATUS_ACTIVE => [
                WorkProgram::STATUS_COMPLETED,
                WorkProgram::STATUS_POSTPONED,
            ],
            WorkProgram::STATUS_POSTPONED => [
                WorkProgram::STATUS_ACTIVE,
                WorkProgram::STATUS_COMPLETED,
            ],
            default => [],
        };
    }
}
