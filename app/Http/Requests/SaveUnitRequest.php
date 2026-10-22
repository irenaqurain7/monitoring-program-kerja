<?php

namespace App\Http\Requests;

use App\Models\Campus;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $unit = $this->route('unit');

        return $unit instanceof Unit
            ? $this->user()->can('manage', $unit)
            : $this->user()->can('create', Unit::class);
    }

    public function rules(): array
    {
        $unit = $this->route('unit');
        $campusId = $this->user()->assignedCampusId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')
                    ->where(fn ($query) => $query->where('campus_id', $campusId))
                    ->ignore($unit?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'campus_id' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama unit wajib diisi.',
            'name.unique' => 'Nama unit sudah digunakan pada kampus ini.',
            'description.max' => 'Informasi unit tidak boleh lebih dari 5.000 karakter.',
            'is_active.boolean' => 'Status unit tidak valid.',
            'campus_id.prohibited' => 'Kampus unit ditetapkan sesuai penugasan Kepala Kampus.',
        ];
    }
}
