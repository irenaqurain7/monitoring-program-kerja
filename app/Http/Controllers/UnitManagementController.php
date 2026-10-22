<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUnitRequest;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UnitManagementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Unit::class);

        $campusId = $request->user()->assignedCampusId();
        $units = Unit::query()
            ->where('campus_id', $campusId)
            ->withCount([
                'teams',
                'teams as active_teams_count' => fn ($query) => $query->where('is_active', true),
                'workPrograms',
                'workPrograms as completed_programs_count' => fn ($query) => $query->where('status', WorkProgram::STATUS_COMPLETED),
                'workPrograms as active_programs_count' => fn ($query) => $query->where('status', WorkProgram::STATUS_ACTIVE),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('units.index', [
            'units' => $units,
            'campus' => $request->user()->campus,
        ]);
    }

    public function store(SaveUnitRequest $request): RedirectResponse
    {
        Gate::authorize('create', Unit::class);

        Unit::create([
            ...$request->validated(),
            'campus_id' => $request->user()->assignedCampusId(),
            'is_active' => true,
        ]);

        return redirect()->route('units.index')->with('status', 'Unit berhasil ditambahkan.');
    }

    public function update(SaveUnitRequest $request, Unit $unit): RedirectResponse
    {
        Gate::authorize('manage', $unit);
        $unit->fill($request->validated())->save();

        return redirect()->route('units.show', $unit)->with('status', 'Informasi unit berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Unit $unit): RedirectResponse
    {
        Gate::authorize('manage', $unit);
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ], [
            'is_active.required' => 'Status unit wajib diisi.',
            'is_active.boolean' => 'Status unit tidak valid.',
        ]);

        $unit->is_active = (bool) $data['is_active'];
        $unit->save();

        return redirect()->route('units.index')->with(
            'status',
            $unit->is_active ? 'Unit berhasil diaktifkan.' : 'Unit dinonaktifkan; riwayat program tetap tersimpan.'
        );
    }

    public function show(Request $request, Unit $unit): View
    {
        Gate::authorize('view', $unit);
        $unit->load([
            'campus',
            'teams' => fn ($query) => $query->withCount('workPrograms')->orderBy('name'),
            'workPrograms.team',
            'workPrograms.responsible',
            'workPrograms.latestProgress',
        ]);

        return view('units.show', compact('unit'));
    }
}
