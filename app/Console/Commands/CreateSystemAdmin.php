<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateSystemAdmin extends Command
{
    protected $signature = 'app:create-system-admin';

    protected $description = 'Membuat akun Admin Sistem pertama secara interaktif';

    public function handle(): int
    {
        if (User::where('role', User::ROLE_SYSTEM_ADMIN)->exists()) {
            $this->error('Admin Sistem sudah tersedia. Perintah ini hanya dapat membuat Admin Sistem pertama.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama Admin Sistem'));
        $email = strtolower(trim((string) $this->ask('Alamat email')));
        $username = strtolower(trim((string) $this->ask('Nama pengguna')));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'username' => $username],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'username' => ['required', 'string', 'regex:/^[a-z0-9._-]+$/', 'max:50', 'unique:users,username'],
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Alamat email wajib diisi.',
                'email.email' => 'Masukkan alamat email yang valid.',
                'email.unique' => 'Alamat email tersebut sudah digunakan.',
                'username.required' => 'Nama pengguna wajib diisi.',
                'username.regex' => 'Nama pengguna hanya boleh berisi huruf kecil, angka, titik, garis bawah, dan tanda hubung.',
                'username.max' => 'Nama pengguna tidak boleh lebih dari 50 karakter.',
                'username.unique' => 'Nama pengguna tersebut sudah digunakan.',
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (minimal 12 karakter)');
        $confirmation = $this->secret('Ulangi kata sandi');

        $passwordValidator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', 'min:12', 'max:255']],
            ['password.min' => 'Kata sandi harus memiliki minimal 12 karakter.'],
        );

        if ($passwordValidator->fails() || $password !== $confirmation) {
            foreach ($passwordValidator->errors()->all() as $message) {
                $this->error($message);
            }

            if ($password !== $confirmation) {
                $this->error('Konfirmasi kata sandi tidak sesuai.');
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => User::ROLE_SYSTEM_ADMIN,
        ]);

        $this->info('Akun Admin Sistem berhasil dibuat.');

        return self::SUCCESS;
    }
}
