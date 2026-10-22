<?php

namespace App\Http\Requests;

use App\Models\Team;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $unit = $this->route('unit');
        $team = $this->route('team');

        if ($team instanceof Team) {
            return $this->user()->can('manage', $team);
        }

        return $unit instanceof Unit && $this->user()->can('manage', $unit);
    }

    public function rules(): array
    {
        $unit = $this->route('unit');
        $team = $this->route('team');
        $unitId = $team instanceof Team ? $team->unit_id : $unit->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')
                    ->where(fn ($query) => $query->where('unit_id', $unitId))
                    ->ignore($team?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'unit_id' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tim wajib diisi.',
            'name.unique' => 'Nama tim sudah digunakan pada unit ini.',
            'description.max' => 'Informasi tim tidak boleh lebih dari 5.000 karakter.',
            'is_active.boolean' => 'Status tim tidak valid.',
            'unit_id.prohibited' => 'Unit tim tidak dapat diganti melalui formulir ini.',
        ];
    }
}
