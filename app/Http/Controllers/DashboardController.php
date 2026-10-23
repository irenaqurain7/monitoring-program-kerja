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

        $selectedPeriod = $request->query('periode', 'tahunan');
        if (! in_array($selectedPeriod, ['tahunan', 'bulanan'], true)) {
            $selectedPeriod = 'tahunan';
        }

        $selectedMonth = (int) $request->query('bulan', now()->month);
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) now()->month;
        }

        $selectedYear = (int) $request->query('tahun', now()->year);
        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = (int) now()->year;
        }

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Specific standard order for Universitas BSI campus units
        $unitOrder = [
            'Admin & SO' => 1,
            'Marketing Communication' => 2,
            'Marcomm' => 2,
            'BSI Career Center' => 3,
            'BSI Entrepreneur Center' => 4,
            'Kemahasiswaan' => 5,
            'Technical Support' => 6,
        ];

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
            ->with(['workPrograms.latestProgress', 'workPrograms.monthlyTargets'])
            ->get()
            ->sortBy(fn (Unit $u) => $unitOrder[$u->name] ?? 99)
            ->values();

        $programs = WorkProgram::query()
            ->where('campus_id', $assignedCampus->id)
            ->with(['latestProgress', 'monthlyTargets', 'unit'])
            ->get();

        // Calculate metrics per unit for the selected period (Tahunan vs Bulanan)
        $unitStats = [];
        $allUnitsCapaian = [];
        $needsAttentionCount = 0;
        $totalPeriodTarget = 0;
        $totalPeriodActual = 0;

        foreach ($units as $unit) {
            $uPrograms = $unit->workPrograms->where('year', $selectedYear);
            $uProgCount = $uPrograms->count();

            if ($selectedPeriod === 'bulanan') {
                $progPercentages = [];
                $uTarget = 0;
                $uActual = 0;

                foreach ($uPrograms as $p) {
                    $mt = $p->monthlyTargets->firstWhere('month', $selectedMonth);
                    $t = $mt ? (float) $mt->target : 0;
                    $a = $mt && $mt->actual !== null ? (float) $mt->actual : null;
                    $uTarget += $t;
                    if ($a !== null) {
                        $uActual += $a;
                        $progPercentages[] = $t > 0 ? min(100, round(($a / $t) * 100, 1)) : 100;
                    } else {
                        $progPercentages[] = 0;
                    }
                }

                $uCapaian = count($progPercentages) > 0 ? round(array_sum($progPercentages) / count($progPercentages), 1) : 0;
                $periodTarget = $uTarget;
                $periodActual = $uActual;
            } else {
                // Annual evaluation
                $progPercentages = [];
                $uTarget = 0;
                $uActual = 0;

                foreach ($uPrograms as $p) {
                    $t = (float) $p->target;
                    $a = (float) $p->totalMonthlyActual();
                    if ($a <= 0 && $p->latestProgress) {
                        $a = (float) $p->latestProgress->actual;
                    }
                    $uTarget += $t;
                    $uActual += $a;
                    if ($t > 0) {
                        $progPercentages[] = min(100, round(($a / $t) * 100, 1));
                    }
                }

                $uCapaian = count($progPercentages) > 0 ? round(array_sum($progPercentages) / count($progPercentages), 1) : 0;
                $periodTarget = $uTarget;
                $periodActual = $uActual;
            }

            $allUnitsCapaian[] = $uCapaian;
            $totalPeriodTarget += $periodTarget;
            $totalPeriodActual += $periodActual;

            if ($uCapaian >= 75) {
                $statusLabel = 'Baik';
                $statusColor = 'success';
                $barHex = '#1B7340';
                $badgeBg = '#DCFCE7';
                $badgeText = '#15803D';
            } elseif ($uCapaian >= 60) {
                $statusLabel = 'Berjalan';
                $statusColor = 'primary';
                $barHex = '#C27803';
                $badgeBg = '#DBEAFE';
                $badgeText = '#1D4ED8';
            } else {
                $statusLabel = 'Perhatian';
                $statusColor = 'danger';
                $barHex = '#DF0B2B';
                $badgeBg = '#FEE2E2';
                $badgeText = '#DC2626';
                $needsAttentionCount++;
            }

            $unitStats[] = [
                'unit' => $unit,
                'program_count' => $uProgCount,
                'target' => $periodTarget,
                'actual' => $periodActual,
                'capaian' => $uCapaian,
                'status' => $statusLabel,
                'status_color' => $statusColor,
                'bar_hex' => $barHex,
                'badge_bg' => $badgeBg,
                'badge_text' => $badgeText,
            ];
        }

        $averageCapaian = count($allUnitsCapaian) > 0 ? (int) round(array_sum($allUnitsCapaian) / count($allUnitsCapaian)) : 0;

        $executiveSummary = [
            'total_units' => $units->count(),
            'target_program' => $programs->count(),
            'average_capaian' => $averageCapaian,
            'needs_attention' => $needsAttentionCount,
            'total_target' => $totalPeriodTarget,
            'total_actual' => $totalPeriodActual,
        ];

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

        // Annual Line Chart Datasets per Unit Section
        $unitLineLabels = [];
        $unitLineCapaian = [];
        $unitLinePointColors = [];
        $unitLineTargets = [];
        $unitLineThresholds = [];

        foreach ($units as $unit) {
            $uPrograms = $unit->workPrograms->where('year', $selectedYear);
            $progPercentages = [];

            foreach ($uPrograms as $p) {
                $t = (float) $p->target;
                $a = (float) $p->totalMonthlyActual();
                if ($a <= 0 && $p->latestProgress) {
                    $a = (float) $p->latestProgress->actual;
                }
                if ($t > 0) {
                    $progPercentages[] = min(100, round(($a / $t) * 100, 1));
                }
            }

            $capaian = count($progPercentages) > 0 
                ? round(array_sum($progPercentages) / count($progPercentages), 1) 
                : 0;

            $unitLineLabels[] = $unit->name;
            $unitLineCapaian[] = $capaian;
            $unitLineTargets[] = 100;
            $unitLineThresholds[] = 75;

            if ($capaian >= 75) {
                $unitLinePointColors[] = '#15803D';
            } elseif ($capaian >= 60) {
                $unitLinePointColors[] = '#2563EB';
            } else {
                $unitLinePointColors[] = '#DF0B2B';
            }
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
            'unitStats',
            'executiveSummary',
            'selectedPeriod',
            'selectedMonth',
            'selectedYear',
            'monthNames',
            'unitLineLabels',
            'unitLineCapaian',
            'unitLinePointColors',
            'unitLineTargets',
            'unitLineThresholds',
            'attentionPrograms',
            'recentTopics'
        ));
    }
}
