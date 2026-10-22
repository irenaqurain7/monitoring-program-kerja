<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', User::ROLE_CAMPUS_HEAD)
            ->whereNull('unit_section')
            ->update(['unit_section' => 'campus_head']);

        foreach (User::unitSectionUnitNames() as $section => $unitName) {
            $unitIds = DB::table('units')->where('name', $unitName)->pluck('id');

            DB::table('users')
                ->whereIn('role', [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER])
                ->whereNull('unit_section')
                ->whereIn('unit_id', $unitIds)
                ->update(['unit_section' => $section]);
        }
    }

    public function down(): void
    {
        // Keep the backfilled classifications; rolling back should not erase account data.
    }
};
