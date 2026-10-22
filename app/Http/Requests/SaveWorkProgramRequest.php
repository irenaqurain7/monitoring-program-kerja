<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWorkProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        return $program instanceof WorkProgram
            ? $this->user()->can('update', $program)
            : $this->user()->can('create', WorkProgram::class);
    }

    public function rules(): array
    {
        $program = $this->route('program');
        $unitId = $program instanceof WorkProgram
            ? (string) $program->unit_id
            : (string) $this->input('unit_id', '');
        $campusId = $program instanceof WorkProgram
            ? (string) $program->campus_id
            : (string) $this->input('campus_id', '');
        $allowedUnitId = (string) $this->user()->unit_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'objective' => ['nullable', 'string', 'max:10000'],
            'success_indicator' => ['nullable', 'string', 'max:10000'],
            'target' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'target_unit' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'campus_id' => [
                $program instanceof WorkProgram ? 'prohibited' : 'required',
                'integer',
                Rule::exists('campuses', 'id'),
            ],
            'unit_id' => [
                $program instanceof WorkProgram ? 'prohibited' : 'required',
                'integer',
                Rule::exists('units', 'id')->where(
                    fn ($query) => $query->where('campus_id', ctype_digit($campusId) ? (int) $campusId : 0)
                ),
                Rule::in([$allowedUnitId]),
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'team_id' => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id')->where(fn ($query) => $query->where('unit_id', ctype_digit($unitId) ? (int) $unitId : 0)
                    ->where('is_active', true)),
            ],
            'responsible_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($unitId) {
                    $query->where('unit_id', ctype_digit($unitId) ? (int) $unitId : 0)
                        ->where('is_active', true);
                }),
            ],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama program wajib diisi.',
            'name.max' => 'Nama program tidak boleh lebih dari 255 karakter.',
            'target.required' => 'Target program wajib diisi.',
            'target.numeric' => 'Target harus berupa angka.',
            'target.min' => 'Target tidak boleh bernilai negatif.',
            'target_unit.required' => 'Satuan target wajib diisi.',
            'year.required' => 'Tahun program wajib diisi.',
            'year.between' => 'Tahun program harus antara 2000 dan 2100.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'due_date.required' => 'Tenggat waktu wajib diisi.',
            'due_date.after_or_equal' => 'Tenggat waktu tidak boleh sebelum tanggal mulai.',
            'campus_id.exists' => 'Kampus yang dipilih tidak tersedia.',
            'unit_id.exists' => 'Unit tidak aktif atau tidak sesuai dengan kampus yang dipilih.',
            'unit_id.in' => 'Anda hanya dapat membuat program pada unit penugasan sendiri.',
            'team_id.exists' => 'Tim harus aktif dan berasal dari unit program kerja.',
            'responsible_user_id.required' => 'Penanggung jawab wajib dipilih.',
            'responsible_user_id.exists' => 'Penanggung jawab harus pengguna aktif dari unit terkait.',
            'status.prohibited' => 'Status program hanya dapat diubah melalui tindakan status.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama program',
            'description' => 'deskripsi',
            'objective' => 'tujuan',
            'success_indicator' => 'indikator keberhasilan',
            'target' => 'target',
            'target_unit' => 'satuan target',
            'year' => 'tahun program',
            'start_date' => 'tanggal mulai',
            'due_date' => 'tenggat waktu',
            'campus_id' => 'kampus',
            'unit_id' => 'unit',
            'responsible_user_id' => 'penanggung jawab',
            'notes' => 'catatan',
        ];
    }

}
