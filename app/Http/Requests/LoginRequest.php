<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'regex:/^[a-z0-9._-]+$/', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
            'campus_id' => [
                'required',
                'string',
                'max:20',
                Rule::in(array_merge(
                    ['system_admin'],
                    \App\Models\Campus::query()->pluck('id')->map(fn ($id) => (string) $id)->all(),
                )),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Nama pengguna wajib diisi.',
            'username.regex' => 'Nama pengguna hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.',
            'username.max' => 'Nama pengguna tidak boleh lebih dari 50 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.max' => 'Kata sandi tidak boleh lebih dari 255 karakter.',
            'campus_id.required' => 'Pilihan kampus wajib diisi.',
            'campus_id.in' => 'Pilih kampus yang tersedia atau akses Admin Sistem.',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'nama pengguna',
            'password' => 'kata sandi',
            'campus_id' => 'kampus',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('username'))) {
            $this->merge(['username' => strtolower(trim($this->input('username')))]);
        }
    }
}
