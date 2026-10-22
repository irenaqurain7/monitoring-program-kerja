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
use Illuminate\Database\Seeder;

class SampleProgramAndCommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $campus = Campus::where('name', 'Cikampek')->first();
        if (! $campus) {
            return;
        }

        $kepala = User::where('role', User::ROLE_CAMPUS_HEAD)->where('campus_id', $campus->id)->first();
        $adminSo = User::where('username', 'admin-so')->first();
        $marcom = User::where('username', 'marcom')->first();

        $marcommUnit = Unit::where('campus_id', $campus->id)->where('name', 'Marcomm')->first();
        $adminSoUnit = Unit::where('campus_id', $campus->id)->where('name', 'Admin & SO')->first();

        if (! $marcommUnit || ! $adminSoUnit || ! $kepala || ! $marcom || ! $adminSo) {
            return;
        }

        // 1. Program Kerja 1: Marcomm
        $p1 = WorkProgram::firstOrCreate(
            ['name' => 'Sosialisasi Penerimaan Mahasiswa Baru 2026/2027', 'campus_id' => $campus->id],
            [
                'unit_id' => $marcommUnit->id,
                'created_by' => $marcom->id,
                'responsible_user_id' => $marcom->id,
                'year' => 2026,
                'target' => 600,
                'target_unit' => 'siswa',
                'status' => WorkProgram::STATUS_ACTIVE,
                'start_date' => '2026-01-01',
                'due_date' => '2026-12-31',
                'notes' => 'Sosialisasi ke SMA/SMK sederajat di wilayah Karawang dan Purwakarta.',
            ]
        );

        // 12 Monthly Targets for P1
        $p1->ensureMonthlyTargetsExist();
        $monthlyTargetsP1 = [
            1 => ['target' => 40, 'actual' => 45],
            2 => ['target' => 50, 'actual' => 52],
            3 => ['target' => 60, 'actual' => 58],
            4 => ['target' => 50, 'actual' => 48],
            5 => ['target' => 60, 'actual' => 65],
            6 => ['target' => 70, 'actual' => 72],
            7 => ['target' => 60, 'actual' => 60],
            8 => ['target' => 50, 'actual' => 55],
            9 => ['target' => 50, 'actual' => 48],
            10 => ['target' => 50, 'actual' => 35],
            11 => ['target' => 30, 'actual' => 0],
            12 => ['target' => 30, 'actual' => 0],
        ];
        foreach ($monthlyTargetsP1 as $m => $val) {
            WorkProgramMonthlyTarget::updateOrCreate(
                ['work_program_id' => $p1->id, 'month' => $m],
                ['target' => $val['target'], 'actual' => $val['actual'], 'notes' => 'Laporan bulan '.$m]
            );
        }

        // 2. Program Kerja 2: Admin & SO
        $p2 = WorkProgram::firstOrCreate(
            ['name' => 'Digitalisasi Arsip Surat dan Dokumen Akademik', 'campus_id' => $campus->id],
            [
                'unit_id' => $adminSoUnit->id,
                'created_by' => $adminSo->id,
                'responsible_user_id' => $adminSo->id,
                'year' => 2026,
                'target' => 1200,
                'target_unit' => 'dokumen',
                'status' => WorkProgram::STATUS_ACTIVE,
                'start_date' => '2026-01-01',
                'due_date' => '2026-12-31',
                'notes' => 'Peralihan bertahap berkas fisik menuju e-dokumen terintegrasi.',
            ]
        );

        $p2->ensureMonthlyTargetsExist();
        for ($m = 1; $m <= 12; $m++) {
            WorkProgramMonthlyTarget::updateOrCreate(
                ['work_program_id' => $p2->id, 'month' => $m],
                ['target' => 100, 'actual' => $m <= 10 ? 100 : 0, 'notes' => 'Target 100 dokumen/bulan']
            );
        }

        // 3. Two-Way Communication Topics
        $ticket1 = CommunicationTopic::firstOrCreate(
            ['ticket_number' => 'TWC-202610-0001'],
            [
                'campus_id' => $campus->id,
                'creator_id' => $marcom->id,
                'unit_id' => $marcommUnit->id,
                'category' => CommunicationTopic::CATEGORY_KENDALA,
                'priority' => CommunicationTopic::PRIORITY_HIGH,
                'privacy' => CommunicationTopic::PRIVACY_PUBLIC_UNIT,
                'status' => CommunicationTopic::STATUS_IN_PROGRESS,
                'title' => 'Kendala Pengiriman Brosur Cetak untuk Roadshow Karawang',
                'description' => 'Vendor percetakan mengabarkan keterlambatan kertas khusus, estimasi mundur 4 hari kerja. Mohon arahan apakah menggunakan alternatif percetakan lokal.',
            ]
        );

        CommunicationStatusLog::firstOrCreate(
            ['communication_topic_id' => $ticket1->id, 'to_status' => CommunicationTopic::STATUS_NEW],
            [
                'user_id' => $marcom->id,
                'from_status' => 'init',
                'action_note' => 'Aspirasi / pengaduan baru berhasil dibuat.',
            ]
        );

        CommunicationStatusLog::firstOrCreate(
            ['communication_topic_id' => $ticket1->id, 'to_status' => CommunicationTopic::STATUS_IN_PROGRESS],
            [
                'user_id' => $kepala->id,
                'from_status' => CommunicationTopic::STATUS_NEW,
                'action_note' => 'Sedang dikoordinasikan dengan bagian pengadaan.',
            ]
        );

        CommunicationMessage::firstOrCreate(
            ['communication_topic_id' => $ticket1->id, 'user_id' => $kepala->id],
            [
                'message' => 'Silakan gunakan percetakan lokal di Cikampek untuk tahap pertama (200 eks) agar jadwal roadshow tidak terganggu. Invoice ditagihkan terpisah.',
            ]
        );

        $ticket2 = CommunicationTopic::firstOrCreate(
            ['ticket_number' => 'TWC-202610-0002'],
            [
                'campus_id' => $campus->id,
                'creator_id' => $adminSo->id,
                'unit_id' => $adminSoUnit->id,
                'category' => CommunicationTopic::CATEGORY_FASILITAS,
                'priority' => CommunicationTopic::PRIORITY_MEDIUM,
                'privacy' => CommunicationTopic::PRIVACY_PUBLIC_UNIT,
                'status' => CommunicationTopic::STATUS_NEW,
                'title' => 'Usulan Penambahan Scanner Kecepatan Tinggi untuk Ruang Arsip',
                'description' => 'Untuk mempercepat target digitalisasi berkas 1.200 dokumen, dibutuhkan unit scanner ADF otomatis tambahan di ruang pelayanan terpadu.',
            ]
        );

        CommunicationStatusLog::firstOrCreate(
            ['communication_topic_id' => $ticket2->id, 'to_status' => CommunicationTopic::STATUS_NEW],
            [
                'user_id' => $adminSo->id,
                'from_status' => 'init',
                'action_note' => 'Aspirasi / pengaduan baru berhasil dibuat.',
            ]
        );
    }
}
