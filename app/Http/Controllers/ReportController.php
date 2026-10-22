<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use App\Models\WorkProgramMonthlyTarget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);

        $year = $filters['year'] ?? (int) now()->format('Y');
        $selectedUnitId = $filters['unit_id'] ?? null;
        $selectedMonth = $filters['month'] ?? (int) now()->format('n');

        $query = WorkProgram::query()
            ->with(['campus:id,name', 'unit:id,name', 'monthlyTargets' => fn ($q) => $q->orderBy('month')])
            ->where('year', $year);

        if ($user->role !== User::ROLE_SYSTEM_ADMIN) {
            $campusId = $user->assignedCampusId();
            $query->where('campus_id', $campusId);

            if (in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)) {
                $query->where('unit_id', $user->unit_id);
                $selectedUnitId = $user->unit_id;
            }
        }

        if ($selectedUnitId) {
            $query->where('unit_id', $selectedUnitId);
        }

        $programs = $query->orderBy('unit_id')->orderBy('name')->get();

        $unitsQuery = Unit::query();
        if ($user->role !== User::ROLE_SYSTEM_ADMIN && $user->assignedCampusId()) {
            $unitsQuery->where('campus_id', $user->assignedCampusId());
        }
        $units = $unitsQuery->orderBy('name')->get(['id', 'name']);

        return view('reports.index', [
            'programs' => $programs,
            'year' => $year,
            'selectedMonth' => $selectedMonth,
            'selectedUnitId' => $selectedUnitId,
            'units' => $units,
            'monthNames' => WorkProgramMonthlyTarget::monthNames(),
        ]);
    }
}
