<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\CommunicationMessage;
use App\Models\CommunicationTopic;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use App\Models\WorkProgramMonthlyTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrdFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function createSetup(): array
    {
        $campus = Campus::create(['name' => 'Kampus Karawang']);
        $unit = $campus->units()->create(['name' => 'Unit Kemahasiswaan']);
        $campusHead = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $campus->id,
        ]);
        $unitHead = User::factory()->create([
            'role' => User::ROLE_UNIT_HEAD,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);

        return [$campus, $unit, $campusHead, $unitHead];
    }

    public function test_program_ensures_12_monthly_targets_and_can_update_them(): void
    {
        [$campus, $unit, $campusHead, $unitHead] = $this->createSetup();

        $program = WorkProgram::create([
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
            'created_by' => $unitHead->id,
            'responsible_user_id' => $unitHead->id,
            'name' => 'Pelatihan Kepemimpinan Mahasiswa',
            'year' => 2026,
            'target' => 120,
            'target_unit' => 'peserta',
            'status' => WorkProgram::STATUS_ACTIVE,
            'start_date' => '2026-01-01',
            'due_date' => '2026-12-31',
        ]);

        // Monthly targets auto-generated
        $program->ensureMonthlyTargetsExist();
        $this->assertCount(12, $program->monthlyTargets);

        // Update targets via controller
        $targetsData = [];
        for ($m = 1; $m <= 12; $m++) {
            $targetsData[$m] = [
                'target' => 10,
                'actual' => $m <= 3 ? 10 : 0,
            ];
        }

        $this->actingAs($unitHead)
            ->put(route('work-programs.monthly-targets', $program), [
                'targets' => $targetsData,
            ])
            ->assertRedirect();

        $this->assertEquals(120, $program->totalMonthlyTarget());
        $this->assertEquals(30, $program->totalMonthlyActual());
    }

    public function test_two_way_communication_lifecycle(): void
    {
        [$campus, $unit, $campusHead, $unitHead] = $this->createSetup();

        // 1. Unit head creates communication topic (ticket/inquiry)
        $response = $this->actingAs($unitHead)->post(route('communication.store'), [
            'category' => CommunicationTopic::CATEGORY_KENDALA,
            'priority' => CommunicationTopic::PRIORITY_HIGH,
            'privacy' => CommunicationTopic::PRIVACY_PUBLIC_UNIT,
            'title' => 'Keterlambatan vendor impor sensor IoT',
            'description' => 'Vendor mengalami kendala pengiriman internasional, memerlukan arahan kepala kampus.',
        ]);

        $response->assertRedirect();
        $topic = CommunicationTopic::firstOrFail();
        $this->assertSame('Keterlambatan vendor impor sensor IoT', $topic->title);
        $this->assertSame(CommunicationTopic::STATUS_NEW, $topic->status);

        // 2. Campus head views and replies to the topic
        $this->actingAs($campusHead)
            ->get(route('communication.show', $topic))
            ->assertOk()
            ->assertSee('Keterlambatan vendor impor sensor IoT');

        $this->actingAs($campusHead)->post(route('communication.message', $topic), [
            'message' => 'Silakan lakukan eskalasi ke vendor cadangan lokal di Karawang.',
        ])->assertRedirect();

        $this->assertCount(1, $topic->fresh()->messages);

        // 3. Campus head updates status to resolved
        $this->actingAs($campusHead)->patch(route('communication.status', $topic), [
            'status' => CommunicationTopic::STATUS_RESOLVED,
            'action_note' => 'Disetujui penggantian vendor lokal.',
        ])->assertRedirect();

        $this->assertSame(CommunicationTopic::STATUS_RESOLVED, $topic->fresh()->status);
        $this->assertCount(2, $topic->statusLogs); // 1 init + 1 update
    }

    public function test_two_way_communication_campus_isolation(): void
    {
        [$campus1, $unit1, $campusHead1, $unitHead1] = $this->createSetup();

        $campus2 = Campus::create(['name' => 'Kampus Bandung']);
        $unit2 = $campus2->units()->create(['name' => 'Unit Logistik']);
        $campusHead2 = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $campus2->id,
        ]);

        $topic = CommunicationTopic::create([
            'ticket_number' => 'TCK-202610-0001',
            'campus_id' => $campus1->id,
            'unit_id' => $unit1->id,
            'creator_id' => $unitHead1->id,
            'category' => CommunicationTopic::CATEGORY_KOORDINASI,
            'priority' => CommunicationTopic::PRIORITY_MEDIUM,
            'privacy' => CommunicationTopic::PRIVACY_PUBLIC_UNIT,
            'title' => 'Topik Rahasia Internal Karawang',
            'description' => 'Isi topik rahasia',
            'status' => CommunicationTopic::STATUS_NEW,
        ]);

        // Campus Head of Campus 2 cannot view or access topic of Campus 1
        $this->actingAs($campusHead2)
            ->get(route('communication.show', $topic))
            ->assertForbidden();

        // System Admin cannot access communication topic
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $this->actingAs($admin)
            ->get(route('communication.index'))
            ->assertForbidden();
    }

    public function test_reports_access_and_rendering(): void
    {
        [$campus, $unit, $campusHead, $unitHead] = $this->createSetup();

        // Campus Head can access reports
        $this->actingAs($campusHead)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Laporan Kinerja Program Kerja')
            ->assertSee('Tahun Periode')
            ->assertSee('Bulan Evaluasi');

        // Unit Head can access reports
        $this->actingAs($unitHead)
            ->get(route('reports.index'))
            ->assertOk();

        // System Admin cannot access reports
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertForbidden();
    }
}
