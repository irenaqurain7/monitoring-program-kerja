<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTeamRequest;
use App\Models\Team;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamManagementController extends Controller
{
    public function store(SaveTeamRequest $request, Unit $unit): RedirectResponse
    {
        Gate::authorize('manage', $unit);

        if (! $unit->is_active) {
            return back()->withErrors(['unit' => 'Tim tidak dapat ditambahkan ke unit nonaktif.']);
        }

        $unit->teams()->create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        return redirect()->route('units.show', $unit)->with('status', 'Tim berhasil ditambahkan.');
    }

    public function update(SaveTeamRequest $request, Unit $unit, Team $team): RedirectResponse
    {
        $this->authorizeTeamInUnit($unit, $team);
        Gate::authorize('manage', $team);

        $team->fill($request->validated())->save();

        return redirect()->route('units.show', $unit)->with('status', 'Informasi tim berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Unit $unit, Team $team): RedirectResponse
    {
        $this->authorizeTeamInUnit($unit, $team);
        Gate::authorize('manage', $team);

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ], [
            'is_active.required' => 'Status tim wajib diisi.',
            'is_active.boolean' => 'Status tim tidak valid.',
        ]);

        $team->is_active = (bool) $data['is_active'];
        $team->save();

        return redirect()->route('units.show', $unit)->with(
            'status',
            $team->is_active ? 'Tim berhasil diaktifkan.' : 'Tim dinonaktifkan; riwayat program tetap tersimpan.'
        );
    }

    private function authorizeTeamInUnit(Unit $unit, Team $team): void
    {
        abort_unless((int) $team->unit_id === (int) $unit->id, 404);
    }
}
