<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeWorkProgramStatusRequest;
use App\Http\Requests\SaveWorkProgramRequest;
use App\Http\Requests\StoreWorkProgramProgressRequest;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use App\Models\WorkProgramProgressUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkProgramController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', WorkProgram::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'status' => ['nullable', Rule::in(array_keys(WorkProgram::statusLabels()))],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
        ], [
            'search.max' => 'Pencarian tidak boleh lebih dari 100 karakter.',
            'year.integer' => 'Tahun harus berupa angka.',
            'year.between' => 'Tahun harus antara 2000 dan 2100.',
            'status.in' => 'Status program tidak valid.',
            'unit_id.exists' => 'Unit yang dipilih tidak tersedia.',
        ]);

        $query = $this->visiblePrograms($request->user())
            ->with(['campus:id,name', 'unit:id,campus_id,name', 'team:id,unit_id,name', 'responsible:id,name']);

        $filtered = (clone $query)
            ->when($filters['search'] ?? null, fn (Builder $programs, string $search) =>
                $programs->where('name', 'like', '%'.$search.'%'))
            ->when($filters['year'] ?? null, fn (Builder $programs, int $year) =>
                $programs->where('year', $year))
            ->when($filters['status'] ?? null, fn (Builder $programs, string $status) =>
                $programs->where('status', $status))
            ->when($filters['unit_id'] ?? null, fn (Builder $programs, int $unitId) =>
                $programs->where('unit_id', $unitId));

        $summary = (clone $filtered)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $programs = $filtered
            ->orderByDesc('year')
            ->orderBy('due_date')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('work-programs.index', [
            'programs' => $programs,
            'filters' => $filters,
            'units' => $this->availableUnits($request->user()),
            'statuses' => WorkProgram::statusLabels(),
            'summary' => $summary,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', WorkProgram::class);

        $unit = $request->user()->unit;

        return view('work-programs.form', [
            'program' => new WorkProgram([
                'year' => (int) now()->format('Y'),
                'start_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'unit_id' => $unit->id,
                'campus_id' => $unit->campus_id,
            ]),
            'unit' => $unit->load('campus'),
            'teams' => $unit->teams()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'responsibles' => $this->responsiblesForUnit($unit->id),
            'isEdit' => false,
        ]);
    }

    public function store(SaveWorkProgramRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $unit = $request->user()->unit;

        abort_if(! $unit, 403, 'Akun belum memiliki penempatan unit yang valid.');

        $data['unit_id'] = $unit->id;
        $data['campus_id'] = $unit->campus_id;
        $data['team_id'] = $data['team_id'] ?? null;
        $data['status'] = WorkProgram::STATUS_DRAFT;
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $program = WorkProgram::create($data);

        return redirect()->route('work-programs.show', $program)
            ->with('status', 'Program kerja berhasil dibuat sebagai rancangan.');
    }

    public function show(Request $request, WorkProgram $program): View
    {
        $workProgram = $this->findVisibleProgram($request->user(), $program->id);
        Gate::authorize('view', $workProgram);

        return view('work-programs.show', ['program' => $workProgram]);
    }

    public function edit(Request $request, WorkProgram $program): View
    {
        $workProgram = $this->findVisibleProgram($request->user(), $program->id);
        Gate::authorize('update', $workProgram);

        return view('work-programs.form', [
            'program' => $workProgram,
            'unit' => $workProgram->unit->load('campus'),
            'teams' => $workProgram->unit->teams()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'responsibles' => $this->responsiblesForUnit($workProgram->unit_id),
            'isEdit' => true,
        ]);
    }

    public function update(SaveWorkProgramRequest $request, WorkProgram $program): RedirectResponse
    {
        $workProgram = $this->findVisibleProgram($request->user(), $program->id);
        Gate::authorize('update', $workProgram);

        $workProgram->fill($request->validated());
        $workProgram->updated_by = $request->user()->id;
        $workProgram->save();

        return redirect()->route('work-programs.show', $workProgram)
            ->with('status', 'Program kerja berhasil diperbarui.');
    }

    public function reportProgress(StoreWorkProgramProgressRequest $request, WorkProgram $program): RedirectResponse
    {
        $workProgram = $this->findVisibleProgram($request->user(), $program->id);
        Gate::authorize('reportProgress', $workProgram);

        WorkProgramProgressUpdate::create([
            'work_program_id' => $workProgram->id,
            'reported_by' => $request->user()->id,
            'actual' => $request->validated('actual'),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()->route('work-programs.show', $workProgram)
            ->with('status', 'Pembaruan capaian berhasil dicatat.');
    }

    public function changeStatus(ChangeWorkProgramStatusRequest $request, WorkProgram $program): RedirectResponse
    {
        $workProgram = $this->findVisibleProgram($request->user(), $program->id);
        Gate::authorize('changeStatus', $workProgram);

        $workProgram->status = $request->validated('status');
        $workProgram->updated_by = $request->user()->id;
        $workProgram->save();

        return redirect()->route('work-programs.show', $workProgram)
            ->with('status', 'Status program kerja berhasil diperbarui.');
    }

    private function visiblePrograms(User $user): Builder
    {
        $query = WorkProgram::query();

        if ($user->role === User::ROLE_CAMPUS_HEAD) {
            $campusId = $user->assignedCampusId();

            return $campusId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('campus_id', $campusId);
        }

        if (in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
            && $user->unit_id !== null) {
            return $query->where('unit_id', $user->unit_id);
        }

        return $query->whereRaw('1 = 0');
    }

    private function findVisibleProgram(User $user, int $id): WorkProgram
    {
        return $this->visiblePrograms($user)
            ->with(['campus', 'unit.campus', 'team', 'responsible', 'creator', 'updater', 'progressUpdates.reporter'])
            ->whereKey($id)
            ->firstOrFail();
    }

    private function availableUnits(User $user)
    {
        $query = Unit::query()->with('campus:id,name')->orderBy('name');

        if ($user->role === User::ROLE_CAMPUS_HEAD) {
            $campusId = $user->assignedCampusId();

            return $campusId === null
                ? $query->whereRaw('1 = 0')->get()
                : $query->where('campus_id', $campusId)->get();
        }

        if (in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)) {
            return $user->unit_id === null
                ? $query->whereRaw('1 = 0')->get()
                : $query->whereKey($user->unit_id)->get();
        }

        return $query->whereRaw('1 = 0')->get();
    }

    private function responsiblesForUnit(int $unitId)
    {
        return User::query()
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }
}
