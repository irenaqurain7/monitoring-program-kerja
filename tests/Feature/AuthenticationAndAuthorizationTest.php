<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private static int $testIpCounter = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $address = '192.0.2.'.self::$testIpCounter++;
        $this->withServerVariables(['REMOTE_ADDR' => $address]);
    }

    public function test_login_page_can_be_opened(): void
    {
        $this->seed(OrganizationSeeder::class);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke aplikasi')
            ->assertSee('Nama Pengguna')
            ->assertSee('Kata sandi')
            ->assertSee('Kampus')
            ->assertSee('Cikampek')
            ->assertSee('Bekasi')
            ->assertSee('Admin Sistem (tanpa akses kampus)');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $campus = Campus::create(['name' => 'Kampus Login Salah']);

        $this->post('/login', [
            'username' => 'tidak-ada',
            'password' => 'kata-sandi-salah',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors([
            'username' => 'Nama pengguna, kata sandi, atau pilihan kampus tidak sesuai.',
        ])
            ->assertSessionHas('_old_input.username', 'tidak-ada')
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_successful_login_authenticates_and_regenerates_the_session(): void
    {
        $campus = Campus::create(['name' => 'Kampus Login Berhasil']);
        $unit = $campus->units()->create(['name' => 'Unit Login Berhasil']);
        $user = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);

        $this->get('/login');
        $sessionId = $this->app['session.store']->getId();

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
            'campus_id' => (string) $campus->id,
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, $this->app['session.store']->getId());
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_UNIT_MEMBER]);

        $this->actingAs($user)->withSession(['session_marker' => 'private'])
            ->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionMissing('session_marker');

        $this->assertGuest();
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_each_role_receives_its_own_dashboard(): void
    {
        $dashboards = [
            User::ROLE_SYSTEM_ADMIN => 'Administrasi Sistem',
            User::ROLE_CAMPUS_HEAD => 'Dashboard Kepala Kampus',
            User::ROLE_UNIT_HEAD => 'Dashboard Kepala Unit',
            User::ROLE_UNIT_MEMBER => 'Dashboard Anggota Unit',
        ];
        $campus = Campus::create(['name' => 'Kampus Dasbor Test']);

        foreach ($dashboards as $role => $heading) {
            $user = User::factory()->create([
                'role' => $role,
                'campus_id' => $role === User::ROLE_CAMPUS_HEAD ? $campus->id : null,
            ]);

            $this->actingAs($user)
                ->get('/dashboard')
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_system_admin_cannot_access_organization_data(): void
    {
        $campus = Campus::create(['name' => 'Kampus Admin Test']);
        $unit = $campus->units()->create(['name' => 'Unit Admin Test']);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);

        $this->actingAs($admin)->get(route('campus.show', $campus))->assertForbidden();
        $this->actingAs($admin)->get(route('unit.show', $unit))->assertForbidden();
        $this->actingAs($admin)->get(route('unit.manage', $unit))->assertForbidden();
    }

    public function test_system_admin_dashboard_does_not_display_organization_assignments(): void
    {
        $campus = Campus::create(['name' => 'Kampus Privat']);
        $unit = $campus->units()->create(['name' => 'Unit Privat']);
        $admin = User::factory()->create([
            'role' => User::ROLE_SYSTEM_ADMIN,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Kampus Privat')
            ->assertDontSee('Unit Privat');
    }

    public function test_campus_head_cannot_access_another_campus(): void
    {
        $assignedCampus = Campus::create(['name' => 'Kampus Tugas']);
        $otherCampus = Campus::create(['name' => 'Kampus Lain']);
        $head = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $assignedCampus->id,
        ]);

        $this->actingAs($head)->get(route('campus.show', $assignedCampus))->assertOk();
        $this->actingAs($head)->get(route('campus.show', $otherCampus))->assertForbidden();
    }

    public function test_unit_head_cannot_access_another_unit(): void
    {
        $campus = Campus::create(['name' => 'Kampus Unit Test']);
        $assignedUnit = $campus->units()->create(['name' => 'Unit Tugas']);
        $otherUnit = $campus->units()->create(['name' => 'Unit Lain']);
        $head = User::factory()->create([
            'role' => User::ROLE_UNIT_HEAD,
            'campus_id' => $campus->id,
            'unit_id' => $assignedUnit->id,
        ]);

        $this->actingAs($head)->get(route('unit.show', $assignedUnit))->assertOk();
        $this->actingAs($head)->get(route('unit.show', $otherUnit))->assertForbidden();
        $this->actingAs($head)->get(route('unit.manage', $otherUnit))->assertForbidden();
    }

    public function test_unit_member_cannot_access_unit_management(): void
    {
        $campus = Campus::create(['name' => 'Kampus Anggota Test']);
        $unit = $campus->units()->create(['name' => 'Unit Anggota Test']);
        $member = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);

        $this->actingAs($member)->get(route('unit.manage', $unit))->assertForbidden();
    }

    public function test_first_system_admin_is_created_with_a_hashed_password(): void
    {
        $password = 'Uji123';

        $this->artisan('app:create-system-admin')
            ->expectsQuestion('Nama Admin Sistem', 'Admin Uji')
            ->expectsQuestion('Alamat email', 'admin.uji@example.test')
            ->expectsQuestion('Nama pengguna', 'admin-uji')
            ->expectsQuestion('Kata sandi (minimal 6 karakter)', $password)
            ->expectsQuestion('Ulangi kata sandi', $password)
            ->expectsOutput('Akun Admin Sistem berhasil dibuat.')
            ->assertSuccessful();

        $admin = User::where('email', 'admin.uji@example.test')->firstOrFail();
        $this->assertSame(User::ROLE_SYSTEM_ADMIN, $admin->role);
        $this->assertSame('admin-uji', $admin->username);
        $this->assertTrue(Hash::check($password, $admin->password));

        $this->artisan('app:create-system-admin')->assertFailed();
    }

    public function test_organization_seeder_is_safe_to_run_more_than_once(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('campuses', 6);
        $this->assertDatabaseCount('units', 36);
    }

    public function test_campus_head_can_log_in_only_to_assigned_campus(): void
    {
        $assignedCampus = Campus::create(['name' => 'Kampus Login Kepala']);
        $otherCampus = Campus::create(['name' => 'Kampus Login Bukan Tugas']);
        $head = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $assignedCampus->id,
        ]);

        $this->post('/login', [
            'username' => $head->username,
            'password' => 'password',
            'campus_id' => (string) $otherCampus->id,
        ])->assertSessionHasErrors([
            'username' => 'Nama pengguna, kata sandi, atau pilihan kampus tidak sesuai.',
        ]);
        $this->assertGuest();

        $this->post('/login', [
            'username' => $head->username,
            'password' => 'password',
            'campus_id' => (string) $assignedCampus->id,
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($head);
    }

    public function test_unit_head_and_member_must_choose_the_campus_of_their_assigned_unit(): void
    {
        $assignedCampus = Campus::create(['name' => 'Kampus dari Unit']);
        $otherCampus = Campus::create(['name' => 'Kampus selain Unit']);
        $unit = $assignedCampus->units()->create(['name' => 'Unit Penugasan']);

        foreach ([User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'campus_id' => $otherCampus->id,
                'unit_id' => $unit->id,
            ]);

            $this->post('/login', [
                'username' => $user->username,
                'password' => 'password',
                'campus_id' => (string) $otherCampus->id,
            ])->assertSessionHasErrors([
                'username' => 'Nama pengguna, kata sandi, atau pilihan kampus tidak sesuai.',
            ]);
            $this->assertGuest();

            $this->post('/login', [
                'username' => $user->username,
                'password' => 'password',
                'campus_id' => (string) $assignedCampus->id,
            ])->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);
            auth()->logout();
        }
    }

    public function test_system_admin_must_use_the_dedicated_non_organization_login_option(): void
    {
        $campus = Campus::create(['name' => 'Kampus untuk Admin']);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);

        $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors([
            'username' => 'Nama pengguna, kata sandi, atau pilihan kampus tidak sesuai.',
        ]);
        $this->assertGuest();

        $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
            'campus_id' => 'system_admin',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('campus.show', $campus))->assertForbidden();
    }

    public function test_login_rejects_a_campus_that_is_not_in_the_database(): void
    {
        $this->post('/login', [
            'username' => 'akun-uji',
            'password' => 'kata-sandi-uji',
            'campus_id' => '999999',
        ])->assertSessionHasErrors('campus_id');

        $this->assertGuest();
    }

    public function test_login_is_case_insensitive_for_username(): void
    {
        $campus = Campus::create(['name' => 'Kampus Username']);
        $unit = $campus->units()->create(['name' => 'Unit Username']);
        $user = User::factory()->create([
            'username' => 'pengguna-uji',
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $unit->id,
        ]);

        $this->post('/login', [
            'username' => 'PENGGUNA-UJI',
            'password' => 'password',
            'campus_id' => (string) $campus->id,
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_repeated_login_attempts_are_rate_limited_with_an_indonesian_message(): void
    {
        $campus = Campus::create(['name' => 'Kampus Throttle']);
        $response = null;

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->post('/login', [
                'username' => 'percobaan-berulang',
                'password' => 'kata-sandi-salah',
                'campus_id' => (string) $campus->id,
            ]);
        }

        $response->assertSessionHasErrors([
            'username' => 'Terlalu banyak percobaan masuk. Coba lagi dalam satu menit.',
        ]);
        $this->assertGuest();
    }

    public function test_system_admin_can_create_and_manage_user_accounts_but_not_organization_routes(): void
    {
        $this->seed(OrganizationSeeder::class);
        $campus = Campus::where('name', 'Karawang')->firstOrFail();
        $unit = $campus->units()->where('name', 'Marcomm')->firstOrFail();
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengguna');
        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Pilih peran / unit bagian');

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Pengguna Baru',
            'username' => 'pengguna-baru',
            'email' => 'pengguna.baru@example.test',
            'password' => 'KataSandiBaru123!',
            'password_confirmation' => 'KataSandiBaru123!',
            'employment_type' => 'staff',
            'unit_section' => 'marcomm',
            'campus_id' => (string) $campus->id,
        ])->assertRedirect(route('users.index'));

        $created = User::where('username', 'pengguna-baru')->firstOrFail();
        $this->assertSame($unit->id, $created->unit_id);
        $this->assertTrue(Hash::check('KataSandiBaru123!', $created->password));
        $this->assertTrue($created->isActive());
        $this->actingAs($admin)->patch(route('users.status', $created), [
            'is_active' => '0',
        ])->assertRedirect(route('users.index'));
        $this->assertFalse($created->fresh()->isActive());
        $this->actingAs($admin)->get(route('campus.show', $campus))->assertForbidden();
    }

    public function test_campus_head_management_is_limited_to_assigned_campus(): void
    {
        $assignedCampus = Campus::create(['name' => 'Kampus Kepala Pengguna']);
        $otherCampus = Campus::create(['name' => 'Kampus Lain Pengguna']);
        $assignedUnit = $assignedCampus->units()->create(['name' => 'Unit Kampus Kepala']);
        $otherUnit = $otherCampus->units()->create(['name' => 'Unit Kampus Lain']);
        $head = User::factory()->create([
            'role' => User::ROLE_CAMPUS_HEAD,
            'campus_id' => $assignedCampus->id,
        ]);
        $inside = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $assignedCampus->id,
            'unit_id' => $assignedUnit->id,
        ]);
        $outside = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $otherCampus->id,
            'unit_id' => $otherUnit->id,
        ]);

        $this->actingAs($head)->get(route('users.index'))->assertOk()->assertSee($inside->username)->assertDontSee($outside->username);
        $this->actingAs($head)->get(route('users.create'))->assertOk()->assertSee('Pilih peran / unit bagian');
        $this->actingAs($head)->get(route('users.edit', $outside))->assertNotFound();
        $this->actingAs($head)->put(route('users.update', $outside), [
            'name' => 'Ubah lintas kampus',
            'username' => $outside->username,
            'email' => $outside->email,
            'unit_section' => 'marcomm',
            'campus_id' => (string) $assignedCampus->id,
        ])->assertForbidden();
    }

    public function test_unit_head_can_manage_members_only_in_assigned_unit(): void
    {
        $campus = Campus::create(['name' => 'Kampus Kepala Unit Pengguna']);
        $unit = $campus->units()->create(['name' => 'Marcomm']);
        $otherUnit = $campus->units()->create(['name' => 'Admin & SO']);
        $head = User::factory()->create([
            'role' => User::ROLE_UNIT_HEAD,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);
        $inside = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
        ]);
        $outside = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $campus->id,
            'unit_id' => $otherUnit->id,
        ]);

        $this->actingAs($head)->get(route('users.index'))->assertOk()->assertSee($inside->username)->assertDontSee($outside->username);
        $this->actingAs($head)->get(route('users.edit', $outside))->assertNotFound();

        $this->actingAs($head)->post(route('users.store'), [
            'name' => 'Anggota Unit Baru',
            'username' => 'anggota-baru',
            'email' => 'anggota-baru@example.test',
            'password' => 'KataSandiAnggota123!',
            'password_confirmation' => 'KataSandiAnggota123!',
            'employment_type' => 'intern',
            'unit_section' => 'marcomm',
            'campus_id' => (string) $campus->id,
        ])->assertRedirect(route('users.index'));

        $this->actingAs($head)->post(route('users.store'), [
            'name' => 'Anggota Unit Tidak Sesuai',
            'username' => 'anggota-tidak-sesuai',
            'email' => 'anggota.tidak.sesuai@example.test',
            'password' => 'KataSandiAnggota123!',
            'password_confirmation' => 'KataSandiAnggota123!',
            'employment_type' => 'intern',
            'unit_section' => 'admin_so',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('unit_section');
    }

    public function test_unit_member_cannot_access_user_management_routes(): void
    {
        $member = User::factory()->create(['role' => User::ROLE_UNIT_MEMBER]);

        $this->actingAs($member)->get(route('users.index'))->assertForbidden();
        $this->actingAs($member)->get(route('users.create'))->assertForbidden();
    }

    public function test_duplicate_username_and_email_are_rejected(): void
    {
        $this->seed(OrganizationSeeder::class);
        $existing = User::factory()->create([
            'username' => 'sudah-ada',
            'email' => 'sudah.ada@example.test',
        ]);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $campus = Campus::firstOrFail();
        $unit = $campus->units()->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Duplikat',
            'username' => $existing->username,
            'email' => 'email.baru@example.test',
            'password' => 'KataSandiBaru123!',
            'password_confirmation' => 'KataSandiBaru123!',
            'employment_type' => 'staff',
            'unit_section' => 'marcomm',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('username');

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Duplikat',
            'username' => 'username-baru',
            'email' => $existing->email,
            'password' => 'KataSandiBaru123!',
            'password_confirmation' => 'KataSandiBaru123!',
            'employment_type' => 'staff',
            'unit_section' => 'marcomm',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('email');
    }

    public function test_unit_assignment_is_derived_from_unit_section_not_request_unit_id(): void
    {
        $this->seed(OrganizationSeeder::class);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $campuses = Campus::orderBy('id')->take(2)->get();
        $unit = $campuses[1]->units()->firstOrFail();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Penempatan Salah',
            'username' => 'penempatan-salah',
            'email' => 'penempatan.salah@example.test',
            'password' => 'KataSandiBaru123!',
            'password_confirmation' => 'KataSandiBaru123!',
            'employment_type' => 'intern',
            'unit_section' => 'technical_support',
            'campus_id' => (string) $campuses[0]->id,
            'unit_id' => (string) $unit->id,
        ])->assertSessionHasErrors('unit_id');
    }

    public function test_inactive_user_cannot_log_in_and_active_session_is_terminated(): void
    {
        $campus = Campus::create(['name' => 'Kampus Akun Nonaktif']);
        $unit = $campus->units()->create(['name' => 'Unit Akun Nonaktif']);
        $user = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'unit_id' => $unit->id,
            'is_active' => false,
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('username');
        $this->assertGuest();

        $user->forceFill(['is_active' => true])->save();
        $this->actingAs($user);
        $user->forceFill(['is_active' => false])->save();

        $this->get('/dashboard')->assertRedirect(route('login'))->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_user_cannot_change_own_role_or_organization_assignment(): void
    {
        $this->seed(OrganizationSeeder::class);
        $campus = Campus::firstOrFail();
        $unit = $campus->units()->firstOrFail();
        $admin = User::factory()->create([
            'role' => User::ROLE_SYSTEM_ADMIN,
            'campus_id' => null,
            'unit_id' => null,
        ]);

        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => (string) $campus->id,
        ])->assertSessionHasErrors('role');

        $admin->refresh();
        $this->assertSame(User::ROLE_SYSTEM_ADMIN, $admin->role);
        $this->assertNull($admin->campus_id);
        $this->assertNull($admin->unit_id);
    }

    public function test_last_active_system_admin_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN, 'is_active' => true]);

        Gate::before(function ($user, string $ability) {
            return $ability === 'toggleStatus' ? true : null;
        });

        $this->actingAs($admin)
            ->patch(route('users.status', $admin), ['is_active' => '0'])
            ->assertSessionHasErrors('status');

        $this->assertTrue($admin->fresh()->isActive());
    }

    public function test_last_active_system_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN, 'is_active' => true]);

        $response = $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'role' => User::ROLE_UNIT_MEMBER,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame(User::ROLE_SYSTEM_ADMIN, $admin->fresh()->role);
    }

    public function test_user_can_be_updated_without_changing_password_or_failing_own_unique_values(): void
    {
        $campus = Campus::create(['name' => 'Kampus Edit Pengguna']);
        $unit = $campus->units()->create(['name' => 'Marcomm']);
        $admin = User::factory()->create(['role' => User::ROLE_SYSTEM_ADMIN]);
        $member = User::factory()->create([
            'role' => User::ROLE_UNIT_MEMBER,
            'campus_id' => $campus->id,
            'unit_id' => $unit->id,
            'password' => 'KataSandiAwal123!',
        ]);

        $this->actingAs($admin)->put(route('users.update', $member), [
            'name' => 'Nama Diperbarui',
            'username' => $member->username,
            'email' => $member->email,
            'campus_id' => (string) $campus->id,
            'unit_section' => 'marcomm',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('users.index'));

        $this->assertSame('Nama Diperbarui', $member->fresh()->name);
        $this->assertTrue(Hash::check('KataSandiAwal123!', $member->fresh()->password));

        $this->actingAs($admin)->put(route('users.update', $member), [
            'name' => 'Nama Diperbarui',
            'username' => $member->username,
            'email' => $member->email,
            'campus_id' => (string) $campus->id,
            'unit_section' => 'marcomm',
            'password' => 'KataSandiGanti123!',
            'password_confirmation' => 'KataSandiGanti123!',
        ])->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('KataSandiGanti123!', $member->fresh()->password));
    }
}
