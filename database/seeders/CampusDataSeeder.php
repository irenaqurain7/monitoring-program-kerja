<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\CommunicationMessage;
use App\Models\CommunicationStatusLog;
use App\Models\CommunicationTopic;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use App\Models\WorkProgramMonthlyTarget;
use App\Models\WorkProgramProgressUpdate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CampusDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Cikampek Campus exists
        $campus = Campus::firstOrCreate(['name' => 'Cikampek']);

        // 2. Standardize 6 Units
        $unitNames = [
            'Admin & SO',
            'Marketing Communication',
            'BSI Career Center',
            'BSI Entrepreneur Center',
            'Kemahasiswaan',
            'Technical Support',
        ];

        // Rename old 'Marcomm' if exists
        Unit::where('campus_id', $campus->id)->where('name', 'Marcomm')->update(['name' => 'Marketing Communication']);

        $units = [];
        foreach ($unitNames as $name) {
            $units[$name] = Unit::firstOrCreate(
                ['campus_id' => $campus->id, 'name' => $name],
                ['is_active' => true]
            );
            $units[$name]->update(['is_active' => true]);
        }

        // 3. Ensure Users exist with active status and password 'password'
        $password = Hash::make('password');

        $kepala = User::updateOrCreate(
            ['username' => 'kepala-kampus'],
            [
                'name' => 'Kepala Kampus Cikampek',
                'email' => 'kepala.cikampek@bsi.ac.id',
                'password' => $password,
                'role' => User::ROLE_CAMPUS_HEAD,
                'campus_id' => $campus->id,
                'unit_id' => null,
                'is_active' => true,
            ]
        );

        $unitUserData = [
            'Admin & SO' => [
                'username' => 'admin-so',
                'name' => 'Staf Admin & SO',
                'email' => 'admin.so.cikampek@bsi.ac.id',
            ],
            'Marketing Communication' => [
                'username' => 'marcom',
                'name' => 'Staf Marketing Communication',
                'email' => 'marcom.cikampek@bsi.ac.id',
            ],
            'BSI Career Center' => [
                'username' => 'bcc',
                'name' => 'Staf BSI Career Center',
                'email' => 'bcc.cikampek@bsi.ac.id',
            ],
            'BSI Entrepreneur Center' => [
                'username' => 'bec',
                'name' => 'Staf BSI Entrepreneur Center',
                'email' => 'bec.cikampek@bsi.ac.id',
            ],
            'Kemahasiswaan' => [
                'username' => 'kemahasiswaan',
                'name' => 'Staf Kemahasiswaan',
                'email' => 'kemahasiswaan.cikampek@bsi.ac.id',
            ],
            'Technical Support' => [
                'username' => 'technical-support',
                'name' => 'Staf Technical Support',
                'email' => 'ts.cikampek@bsi.ac.id',
            ],
        ];

        $unitUsers = [];
        foreach ($unitUserData as $unitName => $uData) {
            $unitUsers[$unitName] = User::updateOrCreate(
                ['username' => $uData['username']],
                [
                    'name' => $uData['name'],
                    'email' => $uData['email'],
                    'password' => $password,
                    'role' => User::ROLE_UNIT_HEAD,
                    'campus_id' => $campus->id,
                    'unit_id' => $units[$unitName]->id,
                    'is_active' => true,
                ]
            );
        }

        // 4. Program Kerja & Monthly Distribution matching user's illustration (85%, 72%, 65%, 55%, 78%, 45%)
        $programsConfig = [
            'Admin & SO' => [
                'name' => 'Digitalisasi Arsip Surat & Dokumen Akademik Kampus',
                'target' => 100,
                'target_unit' => 'dokumen',
                'monthly_targets' => [
                    1 => ['target' => 9, 'actual' => 9],
                    2 => ['target' => 9, 'actual' => 9],
                    3 => ['target' => 9, 'actual' => 9],
                    4 => ['target' => 8, 'actual' => 8],
                    5 => ['target' => 9, 'actual' => 9],
                    6 => ['target' => 9, 'actual' => 9],
                    7 => ['target' => 8, 'actual' => 8],
                    8 => ['target' => 9, 'actual' => 9],
                    9 => ['target' => 8, 'actual' => 8],
                    10 => ['target' => 8, 'actual' => 7],
                    11 => ['target' => 7, 'actual' => null],
                    12 => ['target' => 7, 'actual' => null],
                ],
                'notes' => 'Peralihan bertahap berkas fisik menuju e-dokumen terintegrasi.',
            ],
            'Marketing Communication' => [
                'name' => 'Sosialisasi PMB & Branding Kampus Wilayah Karawang-Purwakarta',
                'target' => 100,
                'target_unit' => 'kegiatan promosi',
                'monthly_targets' => [
                    1 => ['target' => 8, 'actual' => 7],
                    2 => ['target' => 8, 'actual' => 7],
                    3 => ['target' => 9, 'actual' => 8],
                    4 => ['target' => 8, 'actual' => 7],
                    5 => ['target' => 9, 'actual' => 8],
                    6 => ['target' => 9, 'actual' => 7],
                    7 => ['target' => 8, 'actual' => 7],
                    8 => ['target' => 9, 'actual' => 7],
                    9 => ['target' => 8, 'actual' => 7],
                    10 => ['target' => 8, 'actual' => 7],
                    11 => ['target' => 8, 'actual' => null],
                    12 => ['target' => 8, 'actual' => null],
                ],
                'notes' => 'Roadshow ke SMA/SMK mitra serta kampanye digital media sosial.',
            ],
            'BSI Career Center' => [
                'name' => 'Penyaluran Magang Industri & Workshop Kesiapan Karir Lulusan',
                'target' => 100,
                'target_unit' => 'mahasiswa',
                'monthly_targets' => [
                    1 => ['target' => 8, 'actual' => 6],
                    2 => ['target' => 8, 'actual' => 7],
                    3 => ['target' => 8, 'actual' => 7],
                    4 => ['target' => 8, 'actual' => 6],
                    5 => ['target' => 9, 'actual' => 7],
                    6 => ['target' => 8, 'actual' => 6],
                    7 => ['target' => 9, 'actual' => 7],
                    8 => ['target' => 8, 'actual' => 6],
                    9 => ['target' => 9, 'actual' => 7],
                    10 => ['target' => 8, 'actual' => 6],
                    11 => ['target' => 8, 'actual' => null],
                    12 => ['target' => 9, 'actual' => null],
                ],
                'notes' => 'Kerjasama mitra industri kawasan industri Karawang & Cikarang.',
            ],
            'BSI Entrepreneur Center' => [
                'name' => 'Inkubasi Wirausaha Mandiri & Pendampingan Bisnis Mahasiswa',
                'target' => 100,
                'target_unit' => 'kelompok usaha',
                'monthly_targets' => [
                    1 => ['target' => 8, 'actual' => 5],
                    2 => ['target' => 8, 'actual' => 6],
                    3 => ['target' => 8, 'actual' => 5],
                    4 => ['target' => 8, 'actual' => 6],
                    5 => ['target' => 9, 'actual' => 5],
                    6 => ['target' => 8, 'actual' => 6],
                    7 => ['target' => 9, 'actual' => 5],
                    8 => ['target' => 8, 'actual' => 6],
                    9 => ['target' => 9, 'actual' => 6],
                    10 => ['target' => 8, 'actual' => 5],
                    11 => ['target' => 8, 'actual' => null],
                    12 => ['target' => 9, 'actual' => null],
                ],
                'notes' => 'Perlu dorongan keikutsertaan mahasiswa dalam bazar kampus dan pendanaan P2MW.',
            ],
            'Kemahasiswaan' => [
                'name' => 'Pembinaan Ormawa, Prestasi Mahasiswa, & Kompetisi Nasional',
                'target' => 100,
                'target_unit' => 'kegiatan ormawa',
                'monthly_targets' => [
                    1 => ['target' => 8, 'actual' => 8],
                    2 => ['target' => 8, 'actual' => 8],
                    3 => ['target' => 9, 'actual' => 8],
                    4 => ['target' => 8, 'actual' => 8],
                    5 => ['target' => 9, 'actual' => 7],
                    6 => ['target' => 8, 'actual' => 8],
                    7 => ['target' => 9, 'actual' => 8],
                    8 => ['target' => 8, 'actual' => 8],
                    9 => ['target' => 9, 'actual' => 7],
                    10 => ['target' => 8, 'actual' => 8],
                    11 => ['target' => 8, 'actual' => null],
                    12 => ['target' => 8, 'actual' => null],
                ],
                'notes' => 'Fasilitasi delegasi lomba akademik dan non-akademik tingkat wilayah dan nasional.',
            ],
            'Technical Support' => [
                'name' => 'Pemeliharaan Lab Komputer, Bandwidth Jaringan, & Perangkat IT',
                'target' => 100,
                'target_unit' => 'unit perangkat',
                'monthly_targets' => [
                    1 => ['target' => 8, 'actual' => 5],
                    2 => ['target' => 8, 'actual' => 4],
                    3 => ['target' => 8, 'actual' => 5],
                    4 => ['target' => 8, 'actual' => 4],
                    5 => ['target' => 9, 'actual' => 5],
                    6 => ['target' => 8, 'actual' => 4],
                    7 => ['target' => 9, 'actual' => 5],
                    8 => ['target' => 8, 'actual' => 4],
                    9 => ['target' => 9, 'actual' => 5],
                    10 => ['target' => 8, 'actual' => 4],
                    11 => ['target' => 8, 'actual' => null],
                    12 => ['target' => 9, 'actual' => null],
                ],
                'notes' => 'Terdapat kendala spare part PC lab hardware yang menunggu persetujuan pengadaan.',
            ],
        ];

        foreach ($programsConfig as $unitName => $cfg) {
            $unit = $units[$unitName];
            $user = $unitUsers[$unitName];

            $program = WorkProgram::updateOrCreate(
                [
                    'campus_id' => $campus->id,
                    'unit_id' => $unit->id,
                    'name' => $cfg['name'],
                ],
                [
                    'year' => 2026,
                    'target' => $cfg['target'],
                    'target_unit' => $cfg['target_unit'],
                    'status' => WorkProgram::STATUS_ACTIVE,
                    'start_date' => '2026-01-01',
                    'due_date' => '2026-12-31',
                    'notes' => $cfg['notes'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'responsible_user_id' => $user->id,
                ]
            );

            // Populate monthly targets
            $totalActual = 0;
            foreach ($cfg['monthly_targets'] as $m => $val) {
                if ($val['actual'] !== null) {
                    $totalActual += $val['actual'];
                }

                $status = WorkProgramMonthlyTarget::STATUS_NOT_STARTED;
                if ($val['actual'] !== null) {
                    if ($val['actual'] >= $val['target']) {
                        $status = WorkProgramMonthlyTarget::STATUS_ACHIEVED;
                    } elseif ($val['actual'] > 0) {
                        $status = WorkProgramMonthlyTarget::STATUS_IN_PROGRESS;
                    }
                }

                WorkProgramMonthlyTarget::updateOrCreate(
                    ['work_program_id' => $program->id, 'month' => $m],
                    [
                        'target' => $val['target'],
                        'actual' => $val['actual'],
                        'status' => $status,
                        'notes' => 'Laporan capaian bulan ke-'.$m,
                    ]
                );
            }

            // Record latest progress update
            WorkProgramProgressUpdate::updateOrCreate(
                ['work_program_id' => $program->id],
                [
                    'reported_by' => $user->id,
                    'actual' => $totalActual,
                    'notes' => 'Akumulasi capaian terkini tahun 2026.',
                ]
            );
        }

        // 5. Add Two-Way Communication topic for context
        $tsUnit = $units['Technical Support'];
        $tsUser = $unitUsers['Technical Support'];

        $topic = CommunicationTopic::firstOrCreate(
            ['ticket_number' => 'TWC-202610-0001'],
            [
                'campus_id' => $campus->id,
                'creator_id' => $tsUser->id,
                'unit_id' => $tsUnit->id,
                'category' => CommunicationTopic::CATEGORY_FASILITAS,
                'priority' => CommunicationTopic::PRIORITY_HIGH,
                'privacy' => CommunicationTopic::PRIVACY_PUBLIC_UNIT,
                'status' => CommunicationTopic::STATUS_IN_PROGRESS,
                'title' => 'Kendala Pengadaan Spare Part RAM & SSD Lab Komputer 2',
                'description' => 'Target pemeliharaan lab komputer baru mencapai 45% karena keterlambatan pengiriman modul RAM cadangan dari distributor.',
            ]
        );

        CommunicationStatusLog::firstOrCreate(
            ['communication_topic_id' => $topic->id, 'to_status' => CommunicationTopic::STATUS_IN_PROGRESS],
            [
                'user_id' => $kepala->id,
                'from_status' => CommunicationTopic::STATUS_NEW,
                'action_note' => 'Kepala Kampus telah menginstruksikan percepatan PO logistik.',
            ]
        );

        CommunicationMessage::firstOrCreate(
            ['communication_topic_id' => $topic->id, 'user_id' => $kepala->id],
            [
                'message' => 'Sudah kami disposisikan ke bagian logistik kampus pusat agar spare part diprioritaskan minggu ini.',
            ]
        );
    }
}
