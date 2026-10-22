<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $view = match ($request->user()->role) {
            User::ROLE_SYSTEM_ADMIN => 'dashboards.system-admin',
            User::ROLE_CAMPUS_HEAD => 'dashboards.campus-head',
            User::ROLE_UNIT_HEAD => 'dashboards.unit-head',
            User::ROLE_UNIT_MEMBER => 'dashboards.unit-member',
            default => abort(403, 'Peran akun belum diatur.'),
        };

        $user = $request->user();

        if ($user->isSystemAdmin()) {
            return view($view, ['user' => $user, 'assignedCampus' => null]);
        }

        $user->load(['campus', 'unit.campus']);
        $assignedCampus = $user->role === User::ROLE_CAMPUS_HEAD
            ? $user->campus
            : $user->unit?->campus;

        if ($user->role !== User::ROLE_CAMPUS_HEAD) {
            return view($view, compact('user', 'assignedCampus'));
        }

        Gate::authorize('viewAny', Unit::class);

        abort_if(! $assignedCampus, 403, 'Akun belum memiliki penugasan kampus yang valid.');

        $units = Unit::query()
            ->where('campus_id', $assignedCampus->id)
            ->withCount([
                'teams',
                'teams as active_teams_count' => fn ($query) => $query->where('is_active', true),
                'workPrograms',
                'workPrograms as completed_programs_count' => fn ($query) => $query->where('status', WorkProgram::STATUS_COMPLETED),
                'workPrograms as active_programs_count' => fn ($query) => $query->where('status', WorkProgram::STATUS_ACTIVE),
                'workPrograms as overdue_programs_count' => fn ($query) => $query
                    ->where('status', WorkProgram::STATUS_ACTIVE)
                    ->whereDate('due_date', '<', now()->toDateString()),
                'workPrograms as programs_without_update_count' => fn ($query) => $query
                    ->where('status', WorkProgram::STATUS_ACTIVE)
                    ->whereDoesntHave('progressUpdates'),
            ])
            ->with(['workPrograms.latestProgress'])
            ->orderBy('name')
            ->get();

        $programs = WorkProgram::query()
            ->where('campus_id', $assignedCampus->id)
            ->with('latestProgress')
            ->get();
        $summary = [
            'active_units' => $units->where('is_active', true)->count(),
            'active_teams' => $units->sum('active_teams_count'),
            'programs' => $programs->count(),
            'completed' => $programs->where('status', WorkProgram::STATUS_COMPLETED)->count(),
            'running' => $programs->where('status', WorkProgram::STATUS_ACTIVE)->count(),
            'overdue' => $programs->filter(fn (WorkProgram $program) =>
                $program->status === WorkProgram::STATUS_ACTIVE
                && $program->due_date->lt(now()->startOfDay()))->count(),
            'without_update' => $programs->filter(fn (WorkProgram $program) =>
                $program->status === WorkProgram::STATUS_ACTIVE
                && ! $program->latestProgress)->count(),
        ];

        return view($view, compact('user', 'assignedCampus', 'units', 'programs', 'summary'));
    }
}
