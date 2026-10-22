<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\User;
use Database\Seeders\CikampekDemoUsersSeeder;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CikampekDemoUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_non_loginable_demo_accounts_for_cikampek_only(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(CikampekDemoUsersSeeder::class);

        $campus = Campus::where('name', 'Cikampek')->firstOrFail();
        $accounts = User::where('username', 'like', 'dummy-cikampek-%')->with('unit')->get();

        $this->assertCount(7, $accounts);
        $this->assertSame(1, $accounts->where('role', User::ROLE_CAMPUS_HEAD)->count());
        $this->assertSame(6, $accounts->where('role', User::ROLE_UNIT_MEMBER)->count());
        $this->assertTrue($accounts->every(fn (User $account) => ! $account->isActive()));
        $this->assertTrue($accounts->every(fn (User $account) => $account->assignedCampusId() === (int) $campus->id));
        $this->assertSame([
            'Admin & SO',
            'BSI Career Center (BCC)',
            'BSI Enterpreneur Center',
            'Kemahasiswaan',
            'Kepala Kampus',
            'Marcom',
            'Technical Support',
        ], $accounts->map(fn (User $account) => $account->unitSectionLabel())->sort()->values()->all());

        $this->seed(CikampekDemoUsersSeeder::class);
        $this->assertSame(7, User::where('username', 'like', 'dummy-cikampek-%')->count());
    }
}
