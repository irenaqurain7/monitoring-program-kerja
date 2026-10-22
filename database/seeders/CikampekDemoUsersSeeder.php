<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CikampekDemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $campus = Campus::query()->where('name', 'Cikampek')->first();

        if (! $campus) {
            throw new RuntimeException('Kampus Cikampek belum tersedia. Seeder tidak membuat kampus baru.');
        }

        $accounts = [[
            'username' => 'dummy-cikampek-kepala',
            'email' => 'dummy.cikampek.kepala@example.test',
            'name' => 'Dummy Kepala Kampus Cikampek',
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $campus->id,
            'unit_id' => null,
        ]];

        foreach (User::unitSectionUnitNames() as $unitName) {
            $unit = Unit::query()
                ->where('campus_id', $campus->id)
                ->where('name', $unitName)
                ->first();

            if (! $unit) {
                throw new RuntimeException("Unit {$unitName} belum tersedia di Cikampek. Seeder tidak membuat unit baru.");
            }

            $slug = match ($unitName) {
                'Admin & SO' => 'admin-so',
                'BSI Career Center' => 'bcc',
                'BSI Entrepreneur Center' => 'bsi-entrepreneur',
                default => Str::slug($unitName),
            };

            $accounts[] = [
                'username' => 'dummy-cikampek-'.$slug,
                'email' => 'dummy.cikampek.'.$slug.'@example.test',
                'name' => 'Dummy '.$unitName.' Cikampek',
                'role' => User::ROLE_UNIT_MEMBER,
                'campus_id' => $campus->id,
                'unit_id' => $unit->id,
            ];
        }

        $toCreate = [];

        foreach ($accounts as $account) {
            $existing = User::query()
                ->where('username', $account['username'])
                ->orWhere('email', $account['email'])
                ->first();

            if (! $existing) {
                $toCreate[] = $account;
                continue;
            }

            if ($existing->username !== $account['username']
                || $existing->email !== $account['email']
                || $existing->role !== $account['role']
                || (int) $existing->campus_id !== (int) $account['campus_id']
                || (int) $existing->unit_id !== (int) $account['unit_id']) {
                throw new RuntimeException(
                    "Username atau email demo {$account['username']} sudah dipakai akun lain. Tidak ada akun yang dibuat."
                );
            }
        }

        DB::transaction(function () use ($toCreate): void {
            foreach ($toCreate as $account) {
                User::create($account + [
                    'password' => Str::random(64),
                    'is_active' => false,
                ]);
            }
        });

        $this->command?->info(count($toCreate).' akun dummy baru dibuat; '.(count($accounts) - count($toCreate)).' akun sudah tersedia.');
        $this->command?->line('Semua akun dummy berstatus nonaktif dan tidak dapat dipakai untuk login.');
        $this->command?->table(
            ['Username', 'Unit Bagian', 'Peran Akses', 'Status'],
            array_map(fn (array $account) => [
                $account['username'],
                $account['role'] === User::ROLE_CAMPUS_HEAD
                    ? 'Kepala Kampus'
                    : $account['name'],
                $account['role'] === User::ROLE_CAMPUS_HEAD ? 'Kepala Kampus' : 'Anggota Unit',
                'Nonaktif',
            ], $accounts),
        );
    }
}
