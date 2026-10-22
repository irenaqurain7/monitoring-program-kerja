<?php

namespace App\Http\Requests;

use App\Models\Campus;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');

        return $target instanceof User
            ? $this->user()->can('update', $target)
            : $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $target = $this->route('user');
        $isSelf = $target instanceof User && $target->is($this->user());
        $campusId = $this->input('campus_id');
        $allowedCampuses = $this->allowedCampusIds();
        $sectionOptions = $this->allowedUnitSectionKeys($target);
        $isSystemAdminTarget = $target instanceof User && $target->isSystemAdmin();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'regex:/^[a-z0-9._-]+$/',
                'max:50',
                Rule::unique('users', 'username')->ignore($target?->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($target?->id),
            ],
            'password' => [
                $target ? 'nullable' : 'required',
                'string',
                'min:12',
                'max:255',
                'confirmed',
            ],
            'role' => ['prohibited'],
            'employment_type' => [
                $target ? 'sometimes' : 'required',
                Rule::in(array_keys(User::employmentTypeLabels())),
            ],
            'unit_section' => [
                $isSystemAdminTarget ? 'prohibited' : 'required',
                Rule::in($sectionOptions),
            ],
            'campus_id' => [
                $isSystemAdminTarget ? 'prohibited' : ($isSelf ? 'sometimes' : 'required'),
                'integer',
                Rule::in($allowedCampuses),
            ],
            'unit_id' => ['prohibited'],
        ];

        if ($isSelf) {
            $rules['campus_id'][] = Rule::in([$target->campus_id]);
            $rules['unit_section'][] = Rule::in([$target->unit_section]);
        }

        if ($isSystemAdminTarget) {
            $rules['employment_type'] = ['sometimes', Rule::in(array_keys(User::employmentTypeLabels()))];
        }

        $section = $this->input('unit_section');
        if (! $isSystemAdminTarget && $section !== null) {
            if ($section === 'campus_head') {
                $rules['unit_section'][] = Rule::in(
                    $this->user()->role === User::ROLE_SYSTEM_ADMIN ? ['campus_head'] : []
                );
            } else {
                $rules['unit_section'][] = function (string $attribute, mixed $value, \Closure $fail) use ($campusId): void {
                    $unitName = is_string($value)
                        ? (User::unitSectionUnitNames()[$value] ?? null)
                        : null;
                    $validCampusId = is_scalar($campusId) && ctype_digit((string) $campusId)
                        ? (int) $campusId
                        : 0;
                    $isAllowedUnit = $unitName !== null
                        && Unit::query()
                            ->where('campus_id', $validCampusId)
                            ->where('name', $unitName)
                            ->exists();

                    if (! $isAllowedUnit) {
                        $fail('Unit bagian tersebut tidak tersedia pada kampus yang dipilih.');
                    }
                };
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh menggunakan huruf kecil, angka, titik, garis bawah, atau tanda hubung.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'role.prohibited' => 'Peran akses ditentukan berdasarkan unit bagian.',
            'employment_type.required' => 'Status kepegawaian wajib dipilih.',
            'employment_type.in' => 'Status kepegawaian yang dipilih tidak valid.',
            'unit_section.required' => 'Unit bagian wajib dipilih.',
            'unit_section.in' => 'Unit bagian yang dipilih tidak valid.',
            'campus_id.required' => 'Kampus wajib dipilih.',
            'campus_id.in' => 'Kampus yang dipilih tidak sesuai dengan kewenangan Anda.',
            'unit_section.exists' => 'Unit bagian tersebut tidak tersedia pada kampus yang dipilih.',
            'unit_section.prohibited' => 'Akun Admin Sistem tidak memiliki unit bagian organisasi.',
            'unit_id.prohibited' => 'Penempatan unit ditentukan dari unit bagian yang dipilih.',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'employment_type' => 'status kepegawaian',
            'unit_section' => 'unit bagian',
            'campus_id' => 'kampus',
            'unit_id' => 'unit',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('username'))) {
            $this->merge(['username' => strtolower(trim($this->input('username')))]);
        }

        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    private function allowedUnitSectionKeys(?User $target): array
    {
        if ($target?->isSystemAdmin()) {
            return [];
        }

        if ($target && $target->is($this->user())) {
            return $target->unit_section === null ? [] : [$target->unit_section];
        }

        if ($this->user()->role === User::ROLE_SYSTEM_ADMIN) {
            return array_keys(User::unitSectionLabels());
        }

        if ($this->user()->role === User::ROLE_CAMPUS_HEAD) {
            return array_keys(User::unitSectionUnitNames());
        }

        if ($this->user()->role === User::ROLE_UNIT_HEAD) {
            $section = array_search($this->user()->unit?->name, User::unitSectionUnitNames(), true);

            return $section === false ? [] : [$section];
        }

        return [];
    }

    private function allowedCampusIds(): array
    {
        if ($this->user()->role === User::ROLE_SYSTEM_ADMIN) {
            return Campus::query()->pluck('id')->all();
        }

        $campusId = $this->user()->assignedCampusId();

        return $campusId === null ? [] : [$campusId];
    }

}
