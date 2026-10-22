<?php

namespace App\Http\Requests;

use App\Models\WorkProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkProgramProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        return $program instanceof WorkProgram
            && $this->user()->can('reportProgress', $program);
    }

    public function rules(): array
    {
        return [
            'actual' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'campus_id' => ['prohibited'],
            'unit_id' => ['prohibited'],
            'work_program_id' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'actual.required' => 'Nilai capaian wajib diisi.',
            'actual.numeric' => 'Nilai capaian harus berupa angka.',
            'actual.min' => 'Nilai capaian tidak boleh negatif.',
            'notes.max' => 'Catatan tidak boleh lebih dari 5.000 karakter.',
            'campus_id.prohibited' => 'Kampus tidak dapat diubah.',
            'unit_id.prohibited' => 'Unit tidak dapat diubah.',
            'work_program_id.prohibited' => 'Program kerja tidak valid.',
        ];
    }
}
