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
            $unitPrograms = collect();
            $myTopicsCount = 0;
            if ($user->unit_id) {
                $unitPrograms = WorkProgram::query()
                    ->where('unit_id', $user->unit_id)
                    ->with('latestProgress')
                    ->get();
                $myTopicsCount = \App\Models\CommunicationTopic::query()
                    ->where('creator_id', $user->id)
                    ->count();
            }

            return view($view, compact('user', 'assignedCampus', 'unitPrograms', 'myTopicsCount'));
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

        $pendingTopics = \App\Models\CommunicationTopic::query()
            ->where('campus_id', $assignedCampus->id)
            ->whereIn('status', [
                \App\Models\CommunicationTopic::STATUS_NEW,
                \App\Models\CommunicationTopic::STATUS_UNDER_REVIEW,
                \App\Models\CommunicationTopic::STATUS_IN_PROGRESS,
            ])
            ->count();

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
            'pending_topics' => $pendingTopics,
        ];

        // 12-Month Aggregated Analytics for Target vs Realisasi
        $monthlyTargetRecords = \App\Models\WorkProgramMonthlyTarget::query()
            ->whereIn('work_program_id', $programs->pluck('id'))
            ->selectRaw('month, SUM(target) as total_target, SUM(actual) as total_actual')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $chartMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartTargets = [];
        $chartActuals = [];

        for ($m = 1; $m <= 12; $m++) {
            $record = $monthlyTargetRecords->get($m);
            $chartTargets[] = $record ? (float) $record->total_target : 0.0;
            $chartActuals[] = $record ? (float) $record->total_actual : 0.0;
        }

        // Priority attention items (overdue or lacking recent updates)
        $attentionPrograms = $programs->filter(fn (WorkProgram $program) =>
            $program->status === WorkProgram::STATUS_ACTIVE && (
                $program->due_date->lt(now()->startOfDay()) || ! $program->latestProgress
            )
        )->values();

        $recentTopics = \App\Models\CommunicationTopic::query()
            ->where('campus_id', $assignedCampus->id)
            ->with(['unit', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        return view($view, compact(
            'user',
            'assignedCampus',
            'units',
            'programs',
            'summary',
            'chartMonths',
            'chartTargets',
            'chartActuals',
            'attentionPrograms',
            'recentTopics'
        ));
    }
}
