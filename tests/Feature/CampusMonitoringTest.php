<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Team;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_campus_head_only_sees_own_campus_units_and_programs(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        [$otherCampus, $otherUnit] = $this->makeCampusUnit('Karawang');
        $head = $this->makeCampusHead($campus);
        $inside = $this->makeProgram($campus, $unit, 'Program Cikampek');
        $outside = $this->makeProgram($otherCampus, $otherUnit, 'Program Karawang');

        $this->actingAs($head)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dasbor Pemantauan Kampus')
            ->assertSee('Program kerja');
        $this->actingAs($head)->get(route('work-programs.index'))
            ->assertOk()
            ->assertSee('Program Cikampek')
            ->assertDontSee('Program Karawang');
        $this->actingAs($head)->get(route('units.index'))
            ->assertOk()
            ->assertSee($unit->name)
            ->assertDontSee($otherUnit->name);
        $this->actingAs($head)->get(route('work-programs.show', $outside))->assertNotFound();
        $this->actingAs($head)->get(route('units.show', $otherUnit))->assertForbidden();
        $this->actingAs($head)->get(route('work-programs.show', $inside))->assertOk();
    }

    public function test_campus_head_can_create_and_update_unit_in_own_campus(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        $head = $this->makeCampusHead($campus);

        $this->actingAs($head)->post(route('units.store'), [
            'name' => 'Unit Baru',
            'description' => 'Informasi awal',
        ])->assertRedirect(route('units.index'));

        $created = Unit::where('campus_id', $campus->id)->where('name', 'Unit Baru')->firstOrFail();
        $this->actingAs($head)->put(route('units.update', $created), [
            'name' => 'Unit Diperbarui',
            'description' => 'Informasi baru',
        ])->assertRedirect(route('units.show', $created));

        $this->assertSame('Unit Diperbarui', $created->fresh()->name);
        $this->assertSame($campus->id, $created->fresh()->campus_id);
        $this->assertSame('Unit Cikampek', $unit->name);
    }

    public function test_campus_head_cannot_create_duplicate_or_manage_other_campus_unit(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        [$otherCampus, $otherUnit] = $this->makeCampusUnit('Karawang');
        $head = $this->makeCampusHead($campus);

        $this->actingAs($head)->post(route('units.store'), [
            'name' => $unit->name,
        ])->assertSessionHasErrors('name');

        $this->actingAs($head)->put(route('units.update', $otherUnit), [
            'name' => 'Tidak Diizinkan',
        ])->assertForbidden();
        $this->actingAs($head)->patch(route('units.status', $otherUnit), [
            'is_active' => '0',
        ])->assertForbidden();
        $this->assertSame($otherUnit->name, $otherUnit->fresh()->name);
        $this->assertNotSame($campus->id, $otherCampus->id);
    }

    public function test_campus_head_can_create_and_update_team_in_own_unit(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        $head = $this->makeCampusHead($campus);

        $this->actingAs($head)->post(route('units.teams.store', $unit), [
            'name' => 'Tim Kegiatan',
            'description' => 'Tim unit',
        ])->assertRedirect(route('units.show', $unit));

        $team = Team::where('unit_id', $unit->id)->where('name', 'Tim Kegiatan')->firstOrFail();
        $this->actingAs($head)->put(route('units.teams.update', [$unit, $team]), [
            'name' => 'Tim Kegiatan Baru',
            'description' => 'Tim diperbarui',
        ])->assertRedirect(route('units.show', $unit));

        $this->assertSame('Tim Kegiatan Baru', $team->fresh()->name);
        $this->assertSame($unit->id, $team->fresh()->unit_id);
    }

    public function test_team_cannot_be_assigned_across_campuses_and_other_campus_routes_are_denied(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        [$otherCampus, $otherUnit] = $this->makeCampusUnit('Karawang');
        $head = $this->makeCampusHead($campus);
        $team = $unit->teams()->create(['name' => 'Tim Asli']);

        $this->actingAs($head)->post(route('units.teams.store', $unit), [
            'name' => 'Tim Palsu',
            'unit_id' => $otherUnit->id,
        ])->assertSessionHasErrors('unit_id');

        $this->actingAs($head)->put(route('units.teams.update', [$otherUnit, $team]), [
            'name' => 'Tim Pindah',
        ])->assertNotFound();
        $this->actingAs($head)->get(route('units.show', $otherUnit))->assertForbidden();
        $this->assertSame($unit->id, $team->fresh()->unit_id);
        $this->assertNotSame($campus->id, $otherCampus->id);
    }

    public function test_dashboard_uses_actual_counts_progress_and_does_not_sum_different_target_units(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        $head = $this->makeCampusHead($campus);
        $unit->teams()->create(['name' => 'Tim Aktif']);
        $unit->teams()->create(['name' => 'Tim Nonaktif', 'is_active' => false]);
        $program = $this->makeProgram($campus, $unit, 'Program berjalan', [
            'target' => 10,
            'target_unit' => 'kegiatan',
            'status' => WorkProgram::STATUS_ACTIVE,
        ]);
        $program->progressUpdates()->create([
            'actual' => 4,
            'reported_by' => $head->id,
            'notes' => 'Pembaruan awal',
        ]);
        $this->makeProgram($campus, $unit, 'Program selesai', [
            'target' => 2,
            'target_unit' => 'dokumen',
            'status' => WorkProgram::STATUS_COMPLETED,
        ]);
        $this->makeProgram($campus, $unit, 'Program terlambat', [
            'target' => 5,
            'target_unit' => 'peserta',
            'status' => WorkProgram::STATUS_ACTIVE,
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($head)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unit aktif')
            ->assertSee('Tim aktif')
            ->assertSee('Program selesai')
            ->assertSee('Program terlambat')
            ->assertSee('40%')
            ->assertSee('Target memiliki satuan yang berbeda')
            ->assertSee('Program berjalan tanpa pembaruan progres');
    }

    public function test_admin_system_cannot_access_unit_team_program_or_progress_data(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $team = $unit->teams()->create(['name' => 'Tim Admin Test']);
        $program = $this->makeProgram($campus, $unit, 'Program Privat');

        $this->actingAs($admin)->get(route('units.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('units.show', $unit))->assertForbidden();
        $this->actingAs($admin)->post(route('units.teams.store', $unit), ['name' => 'Ditolak'])->assertForbidden();
        $this->actingAs($admin)->get(route('work-programs.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('work-programs.show', $program))->assertForbidden();
        $this->assertSame('Tim Admin Test', $team->fresh()->name);
    }

    public function test_deactivated_units_and_teams_keep_program_history(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        $head = $this->makeCampusHead($campus);
        $team = $unit->teams()->create(['name' => 'Tim Bersejarah']);
        $program = $this->makeProgram($campus, $unit, 'Program Bersejarah', ['team_id' => $team->id]);

        $this->actingAs($head)->patch(route('units.teams.status', [$unit, $team]), [
            'is_active' => '0',
        ])->assertRedirect(route('units.show', $unit));
        $this->actingAs($head)->patch(route('units.status', $unit), [
            'is_active' => '0',
        ])->assertRedirect(route('units.index'));

        $this->assertFalse($unit->fresh()->is_active);
        $this->assertFalse($team->fresh()->is_active);
        $this->assertDatabaseHas('work_programs', [
            'id' => $program->id,
            'unit_id' => $unit->id,
            'team_id' => $team->id,
        ]);
        $this->actingAs($head)->get(route('work-programs.show', $program))->assertOk();
    }

    public function test_program_progress_is_cumulative_and_unavailable_to_admin_or_campus_head(): void
    {
        [$campus, $unit] = $this->makeCampusUnit('Cikampek');
        $head = $this->makeCampusHead($campus);
        $member = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $unit->id,
        ]);
        $program = $this->makeProgram($campus, $unit, 'Program Capaian', [
            'target' => 20,
            'target_unit' => 'kegiatan',
        ]);

        $this->actingAs($member)->post(route('work-programs.progress', $program), [
            'actual' => 7,
            'notes' => 'Capaian berjalan',
        ])->assertRedirect(route('work-programs.show', $program));

        $this->actingAs($head)->get(route('work-programs.show', $program))
            ->assertOk()
            ->assertSee('7.00 kegiatan')
            ->assertSee('35%')
            ->assertDontSee('Catat Capaian');

        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $this->actingAs($admin)->post(route('work-programs.progress', $program), ['actual' => 8])->assertForbidden();
        $this->assertSame(1, $program->progressUpdates()->count());
    }

    private function makeCampusUnit(string $campusName): array
    {
        $campus = Campus::create(['name' => $campusName]);
        $unit = $campus->units()->create(['name' => 'Unit '.$campusName]);

        return [$campus, $unit];
    }

    private function makeCampusHead(Campus $campus): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $campus->id,
        ]);
    }

    private function makeProgram(Campus $campus, Unit $unit, string $name, array $attributes = []): WorkProgram
    {
        $responsible = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        return WorkProgram::create(array_merge([
            'name' => $name,
            'target' => 5,
            'target_unit' => 'kegiatan',
            'year' => now()->year,
            'start_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
            'responsible_user_id' => $responsible->id,
            'status' => WorkProgram::STATUS_ACTIVE,
        ], $attributes));
    }
}
