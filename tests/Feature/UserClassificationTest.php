<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserClassificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_form_offers_staff_and_intern_and_unit_section_options(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Status kepegawaian')
            ->assertSee('Staff')
            ->assertSee('Magang')
            ->assertSee('Peran / Unit Bagian')
            ->assertSee('BSI Career Center (BCC)')
            ->assertSee('Kepala Kampus')
            ->assertSee('Pilihan lainnya menjadi Anggota Unit');
    }

    public function test_unit_section_sets_access_role_and_organization_unit(): void
    {
        $this->seed(OrganizationSeeder::class);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $campus = Campus::where('name', 'Karawang')->firstOrFail();
        $unit = $campus->units()->where('name', 'Marcomm')->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Anggota Magang Marcomm',
            'username' => 'magang-marcomm',
            'email' => 'magang.marcomm@example.test',
            'password' => 'KataSandiMagang123!',
            'password_confirmation' => 'KataSandiMagang123!',
            'employment_type' => 'intern',
            'unit_section' => 'marcomm',
            'campus_id' => (string) $campus->id,
        ])->assertRedirect(route('users.index'));

        $created = User::where('username', 'magang-marcomm')->firstOrFail();
        $this->assertSame('intern', $created->employment_type);
        $this->assertSame('marcomm', $created->unit_section);
        $this->assertSame(User::ROLE_UNIT_MEMBER, $created->role);
        $this->assertSame($unit->id, $created->unit_id);

        $this->actingAs($admin)
            ->get(route('users.index', ['employment_type' => 'intern', 'unit_section' => 'marcomm']))
            ->assertOk()
            ->assertSee('magang-marcomm')
            ->assertSee('Magang')
            ->assertSee('Marcom');
    }

    public function test_campus_head_unit_section_creates_campus_head_role_without_a_unit(): void
    {
        $this->seed(OrganizationSeeder::class);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $campus = Campus::where('name', 'Bekasi')->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Kepala Kampus Bekasi',
            'username' => 'kepala-bekasi',
            'email' => 'kepala.bekasi@example.test',
            'password' => 'KataSandiKepala123!',
            'password_confirmation' => 'KataSandiKepala123!',
            'employment_type' => 'staff',
            'unit_section' => 'campus_head',
            'campus_id' => (string) $campus->id,
        ])->assertRedirect(route('users.index'));

        $created = User::where('username', 'kepala-bekasi')->firstOrFail();
        $this->assertSame(User::ROLE_CAMPUS_HEAD, $created->role);
        $this->assertSame($campus->id, $created->campus_id);
        $this->assertNull($created->unit_id);
    }

    public function test_request_cannot_override_role_or_promote_campus_head(): void
    {
        $this->seed(OrganizationSeeder::class);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $campus = Campus::where('name', 'Cikampek')->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Percobaan Mengubah Peran',
            'username' => 'ubah-peran',
            'email' => 'ubah.peran@example.test',
            'password' => 'KataSandiUbah123!',
            'password_confirmation' => 'KataSandiUbah123!',
            'role' => User::ROLE_SYSTEM_ADMIN,
            'employment_type' => 'staff',
            'unit_section' => 'marcomm',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['username' => 'ubah-peran']);

        $campusHead = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $campus->id,
        ]);
        $this->actingAs($campusHead)->post(route('users.store'), [
            'name' => 'Percobaan Kepala Kampus',
            'username' => 'kepala-tidak-diizinkan',
            'email' => 'kepala.tidak.diizinkan@example.test',
            'password' => 'KataSandiUbah123!',
            'password_confirmation' => 'KataSandiUbah123!',
            'employment_type' => 'staff',
            'unit_section' => 'campus_head',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('unit_section');
    }

    public function test_invalid_staff_type_or_unit_section_is_rejected(): void
    {
        $this->seed(OrganizationSeeder::class);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $campus = Campus::firstOrFail();
        $unit = $campus->units()->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Kategori Tidak Valid',
            'username' => 'kategori-tidak-valid',
            'email' => 'kategori.tidak.valid@example.test',
            'password' => 'KataSandiValid123!',
            'password_confirmation' => 'KataSandiValid123!',
            'employment_type' => 'manager',
            'unit_section' => 'unknown',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors(['employment_type', 'unit_section']);

        $this->assertDatabaseMissing('users', ['username' => 'kategori-tidak-valid']);
    }
}
