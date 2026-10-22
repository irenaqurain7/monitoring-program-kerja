@extends('layouts.app')

@section('title', 'Laporan Kinerja Program Kerja | Monitoring Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Laporan Manajemen</div>
            <h1 class="h2 text-navy fw-medium mb-1">Laporan Kinerja Program Kerja</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">Rekapitulasi target dan realisasi bulanan serta capaian kumulatif tahunan per unit.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="eyebrow-luxury">Parameter Evaluasi</div>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="year">Tahun Periode</label>
                    <input type="number" class="form-control form-control-editorial" id="year" name="year" min="2000" max="2100" value="{{ $year }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="month">Bulan Evaluasi</label>
                    <select class="form-select form-control-editorial" id="month" name="month">
                        @foreach ($monthNames as $mNum => $mName)
                            <option value="{{ $mNum }}" @selected($selectedMonth === $mNum)>{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>
                @if (auth()->user()->role === \App\Models\User::ROLE_CAMPUS_HEAD || auth()->user()->role === \App\Models\User::ROLE_SYSTEM_ADMIN)
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="unit_id">Unit Bagian</label>
                        <select class="form-select form-control-editorial" id="unit_id" name="unit_id">
                            <option value="">Semua Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected($selectedUnitId === $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-6 col-md-2 d-grid">
                    <button class="btn btn-outline-navy" type="submit">Tampilkan Laporan</button>
                </div>
            </form>
        </div>
    </section>

    @php
        $totalPrograms = $programs->count();
        $achievedCount = 0;
        $inProgressCount = 0;
        $attentionCount = 0;

        foreach ($programs as $p) {
            $monthlyTarget = $p->monthlyTargets->firstWhere('month', $selectedMonth);
            if ($monthlyTarget) {
                $effStatus = $monthlyTarget->effectiveStatus();
                if ($effStatus === \App\Models\WorkProgramMonthlyTarget::STATUS_ACHIEVED) {
                    $achievedCount++;
                } elseif ($effStatus === \App\Models\WorkProgramMonthlyTarget::STATUS_IN_PROGRESS) {
                    $inProgressCount++;
                } elseif ($effStatus === \App\Models\WorkProgramMonthlyTarget::STATUS_NEEDS_ATTENTION) {
                    $attentionCount++;
                }
            }
        }
    @endphp

    <!-- Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-editorial">
                <div class="stat-label">Total Program Kerja</div>
                <div class="stat-number">{{ $totalPrograms }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-editorial">
                <div class="stat-label">Target Tercapai ({{ $monthNames[$selectedMonth] }})</div>
                <div class="stat-number" style="color: var(--success);">{{ $achievedCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-editorial">
                <div class="stat-label">Sedang Berjalan</div>
                <div class="stat-number" style="color: var(--corporate-blue);">{{ $inProgressCount }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-editorial">
                <div class="stat-label">Perlu Perhatian</div>
                <div class="stat-number {{ $attentionCount > 0 ? 'accent' : '' }}">{{ $attentionCount }}</div>
            </div>
        </div>
    </div>

    <!-- Report Table Card -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
            <div>
                <div class="eyebrow-luxury">Rekapitulasi Kinerja</div>
                <h2 class="h5 text-navy mb-0">Matriks Capaian Program Kerja: {{ $monthNames[$selectedMonth] }} {{ $year }}</h2>
                <div class="text-secondary small">Perbandingan target dan realisasi bulanan serta akumulasi tahun berjalan.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Program Kerja</th>
                        <th>Unit</th>
                        <th>Target Tahunan</th>
                        <th>Target ({{ $monthNames[$selectedMonth] }})</th>
                        <th>Realisasi ({{ $monthNames[$selectedMonth] }})</th>
                        <th>Capaian Bulan (%)</th>
                        <th>Akumulasi Realisasi</th>
                        <th>Capaian Tahun (%)</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programs as $prog)
                        @php
                            $mTarget = $prog->monthlyTargets->firstWhere('month', $selectedMonth);
                            $mTargetVal = $mTarget ? (float) $mTarget->target : 0;
                            $mActualVal = $mTarget && $mTarget->actual !== null ? (float) $mTarget->actual : null;
                            $mPct = ($mTargetVal > 0 && $mActualVal !== null) ? round(($mActualVal / $mTargetVal) * 100, 1) : null;

                            $totalAnnualActual = (float) $prog->monthlyTargets->whereNotNull('actual')->sum('actual');
                            $annualTarget = (float) $prog->target;
                            $annualPct = $annualTarget > 0 ? min(100, round(($totalAnnualActual / $annualTarget) * 100, 1)) : 0;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('work-programs.show', $prog) }}" class="fw-semibold text-navy text-decoration-none">
                                    {{ $prog->name }}
                                </a>
                                <div>
                                    <span class="badge" style="background: rgba(100, 116, 139, 0.08); color: var(--text-muted); font-size: 0.68rem;">
                                        {{ $prog->statusLabel() }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ $prog->unit->name }}</td>
                            <td class="fw-semibold text-navy">{{ $prog->target }} {{ $prog->target_unit }}</td>
                            <td>{{ $mTargetVal }} {{ $prog->target_unit }}</td>
                            <td>{{ $mActualVal !== null ? $mActualVal.' '.$prog->target_unit : 'Belum diisi' }}</td>
                            <td>
                                @if ($mPct === null)
                                    <span class="small text-secondary">-</span>
                                @else
                                    <span class="fw-semibold {{ $mPct >= 100 ? 'text-success' : ($mPct < 50 ? 'text-danger' : 'text-navy') }}">
                                        {{ $mPct }}%
                                    </span>
                                @endif
                            </td>
                            <td>{{ $totalAnnualActual }} {{ $prog->target_unit }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px; background: var(--border-gray);">
                                        <div class="progress-bar" style="width: {{ $annualPct }}%; background: var(--corporate-blue);"></div>
                                    </div>
                                    <span class="small fw-semibold text-navy">{{ $annualPct }}%</span>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.show', $prog) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-5">
                                Belum ada program kerja yang tercatat pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
@endsection
