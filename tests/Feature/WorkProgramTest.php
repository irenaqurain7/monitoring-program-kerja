<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_head_can_create_a_program_in_their_assigned_unit(): void
    {
        [$campus, $unit, $head, $responsible] = $this->makeUnitTeam();

        $this->actingAs($head)->post(route('work-programs.store'), $this->validProgramData($campus, $unit, $responsible))
            ->assertRedirect();

        $program = WorkProgram::firstOrFail();
        $this->assertSame($unit->id, $program->unit_id);
        $this->assertSame($campus->id, $program->campus_id);
        $this->assertSame($head->id, $program->created_by);
        $this->assertSame(WorkProgram::STATUS_DRAFT, $program->status);
    }

    public function test_unit_head_cannot_create_a_program_for_another_unit(): void
    {
        [$campus, $unit, $head, $responsible] = $this->makeUnitTeam();
        $otherUnit = $campus->units()->create(['name' => 'Unit Lain']);

        $this->actingAs($head)->post(route('work-programs.store'), array_merge(
            $this->validProgramData($campus, $unit, $responsible),
            ['unit_id' => (string) $otherUnit->id]
        ))->assertSessionHasErrors('unit_id');

        $this->assertDatabaseCount('work_programs', 0);
    }

    public function test_campus_head_only_sees_programs_from_assigned_campus(): void
    {
        $this->seed(OrganizationSeeder::class);
        $campuses = Campus::query()->orderBy('id')->take(2)->get();
        $units = $campuses->map(fn (Campus $campus) => $campus->units()->firstOrFail());
        $head = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $campuses[0]->id,
        ]);
        $inside = $this->createProgram($campuses[0], $units[0]);
        $outside = $this->createProgram($campuses[1], $units[1]);

        $this->actingAs($head)
            ->get(route('work-programs.index'))
            ->assertOk()
            ->assertSee($inside->name)
            ->assertDontSee($outside->name);
        $this->actingAs($head)->get(route('work-programs.show', $outside))->assertNotFound();
    }

    public function test_unit_user_cannot_view_or_change_program_outside_assigned_unit(): void
    {
        $campus = Campus::create(['name' => 'Kampus Cakupan Program']);
        $unit = $campus->units()->create(['name' => 'Unit Tugas Program']);
        $otherUnit = $campus->units()->create(['name' => 'Unit Lain Program']);
        $head = User::factory()->create([
            'role' => User::ROLE_UNIT_HEAD,
            'unit_id' => $unit->id,
        ]);
        $program = $this->createProgram($campus, $otherUnit);

        $this->actingAs($head)->get(route('work-programs.show', $program))->assertNotFound();
        $this->actingAs($head)->put(route('work-programs.update', $program), [])->assertForbidden();
    }

    public function test_unit_member_can_view_but_cannot_edit_program(): void
    {
        [$campus, $unit, , $responsible] = $this->makeUnitTeam();
        $member = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $unit->id,
        ]);
        $program = $this->createProgram($campus, $unit, $responsible);

        $this->actingAs($member)->get(route('work-programs.show', $program))->assertOk();
        $this->actingAs($member)->get(route('work-programs.edit', $program))->assertForbidden();
        $this->actingAs($member)->put(route('work-programs.update', $program), [])->assertForbidden();
        $this->actingAs($member)->patch(route('work-programs.status', $program), [
            'status' => WorkProgram::STATUS_ACTIVE,
        ])->assertForbidden();
    }

    public function test_unit_head_can_update_program_details_without_changing_its_placement(): void
    {
        [$campus, $unit, $head, $responsible] = $this->makeUnitTeam();
        $program = $this->createProgram($campus, $unit, $responsible);
        $data = $this->validProgramData($campus, $unit, $responsible);
        $data['name'] = 'Program Diperbarui';
        unset($data['campus_id'], $data['unit_id']);

        $this->actingAs($head)->put(route('work-programs.update', $program), $data)
            ->assertRedirect(route('work-programs.show', $program));

        $this->assertSame('Program Diperbarui', $program->fresh()->name);
        $this->assertSame($unit->id, $program->fresh()->unit_id);
        $this->assertSame($campus->id, $program->fresh()->campus_id);
    }

    public function test_unit_head_can_change_status_only_through_allowed_transitions(): void
    {
        [$campus, $unit, $head, $responsible] = $this->makeUnitTeam();
        $program = $this->createProgram($campus, $unit, $responsible);

        $this->actingAs($head)->patch(route('work-programs.status', $program), [
            'status' => WorkProgram::STATUS_ACTIVE,
        ])->assertRedirect(route('work-programs.show', $program));
        $this->assertSame(WorkProgram::STATUS_ACTIVE, $program->fresh()->status);

        $this->patch(route('work-programs.status', $program), [
            'status' => WorkProgram::STATUS_DRAFT,
        ])->assertSessionHasErrors('status');
        $this->assertSame(WorkProgram::STATUS_ACTIVE, $program->fresh()->status);
    }

    public function test_system_admin_is_denied_from_all_work_program_endpoints(): void
    {
        [$campus, $unit, , $responsible] = $this->makeUnitTeam();
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $program = $this->createProgram($campus, $unit, $responsible);

        $this->actingAs($admin)->get(route('work-programs.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('work-programs.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('work-programs.store'), [])->assertForbidden();
        $this->actingAs($admin)->get(route('work-programs.show', $program))->assertForbidden();
        $this->actingAs($admin)->get(route('work-programs.edit', $program))->assertForbidden();
        $this->actingAs($admin)->put(route('work-programs.update', $program), [])->assertForbidden();
    }

    public function test_program_validation_rejects_invalid_dates_target_and_responsible_user(): void
    {
        [$campus, $unit, $head] = $this->makeUnitTeam();
        $otherUnit = $campus->units()->create(['name' => 'Unit Penanggung Jawab Lain']);
        $wrongResponsible = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $otherUnit->id,
        ]);
        $data = $this->validProgramData($campus, $unit, $wrongResponsible);
        $data['target'] = '-1';
        $data['start_date'] = '2026-12-31';
        $data['due_date'] = '2026-01-01';

        $this->actingAs($head)->post(route('work-programs.store'), $data)
            ->assertSessionHasErrors(['target', 'due_date', 'responsible_user_id']);

        $this->assertDatabaseCount('work_programs', 0);
    }

    public function test_search_filters_status_and_pagination_are_applied(): void
    {
        [$campus, $unit, $head, $responsible] = $this->makeUnitTeam();

        for ($index = 1; $index <= 17; $index++) {
            $this->createProgram($campus, $unit, $responsible, [
                'name' => $index === 1 ? 'Program Pencarian Khusus' : 'Program Uji '.$index,
                'year' => 2025,
            ]);
        }
        $this->createProgram($campus, $unit, $responsible, [
            'name' => 'Program Berstatus Aktif',
            'year' => 2025,
            'status' => WorkProgram::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($head)->get(route('work-programs.index', [
            'search' => 'Pencarian Khusus',
            'year' => 2025,
            'status' => WorkProgram::STATUS_DRAFT,
            'unit_id' => $unit->id,
        ]));
        $response->assertOk()
            ->assertSee('Program Pencarian Khusus')
            ->assertDontSee('Program Berstatus Aktif');

        $response = $this->actingAs($head)->get(route('work-programs.index'));
        $response->assertOk()
            ->assertSee('Program Uji 1')
            ->assertSee('page=2');
        $this->assertSame(15, $response->viewData('programs')->count());
    }

    private function makeUnitTeam(): array
    {
        $campus = Campus::create(['name' => 'Kampus Program Uji']);
        $unit = $campus->units()->create(['name' => 'Unit Program Uji']);
        $head = User::factory()->create([
            'role' => User::ROLE_UNIT_HEAD,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);
        $responsible = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        return [$campus, $unit, $head, $responsible];
    }

    private function validProgramData(Campus $campus, Unit $unit, User $responsible): array
    {
        return [
            'name' => 'Program Uji Tahunan',
            'description' => 'Deskripsi uji program.',
            'objective' => 'Tujuan uji program.',
            'success_indicator' => 'Indikator uji tercapai.',
            'target' => '12',
            'target_unit' => 'kegiatan',
            'year' => '2026',
            'start_date' => '2026-01-01',
            'due_date' => '2026-12-31',
            'campus_id' => (string) $campus->id,
            'unit_id' => (string) $unit->id,
            'responsible_user_id' => (string) $responsible->id,
            'notes' => 'Catatan uji.',
        ];
    }

    private function createProgram(Campus $campus, Unit $unit, ?User $responsible = null, array $attributes = []): WorkProgram
    {
        $responsible ??= User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $unit->id,
            'is_active' => true,
        ]);

        return WorkProgram::create(array_merge([
            'name' => 'Program '.uniqid(),
            'description' => null,
            'objective' => null,
            'success_indicator' => null,
            'target' => 5,
            'target_unit' => 'kegiatan',
            'year' => 2026,
            'start_date' => '2026-01-01',
            'due_date' => '2026-12-31',
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
            'responsible_user_id' => $responsible->id,
            'status' => WorkProgram::STATUS_DRAFT,
        ], $attributes));
    }
}
