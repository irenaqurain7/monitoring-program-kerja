@extends('layouts.app')

@section('title', $program->name.' | Monitoring Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="mb-3">
        <a href="{{ route('work-programs.index') }}" class="text-decoration-none small fw-semibold" style="color: var(--corporate-blue);">
            &larr; Kembali ke Daftar Program
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">{{ $program->campus->name }} &bull; {{ $program->unit->name }} &bull; Tahun {{ $program->year }}</div>
            <h1 class="h2 text-navy fw-medium mb-0" style="font-family: var(--font-serif);">{{ $program->name }}</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $statusBadgeStyle = match($program->status) {
                    \App\Models\WorkProgram::STATUS_COMPLETED => 'background: rgba(21, 128, 61, 0.1); color: var(--success); border-color: rgba(21, 128, 61, 0.2);',
                    \App\Models\WorkProgram::STATUS_ACTIVE => 'background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border-color: rgba(33, 74, 123, 0.2);',
                    \App\Models\WorkProgram::STATUS_APPROVED => 'background: rgba(2, 132, 199, 0.1); color: #0284c7; border-color: rgba(2, 132, 199, 0.2);',
                    \App\Models\WorkProgram::STATUS_SUBMITTED => 'background: rgba(183, 121, 31, 0.1); color: var(--warning); border-color: rgba(183, 121, 31, 0.2);',
                    default => 'background: rgba(100, 116, 139, 0.1); color: var(--text-muted); border-color: rgba(100, 116, 139, 0.2);',
                };
            @endphp
            <span class="badge px-3 py-2" style="{{ $statusBadgeStyle }} border: 1px solid; font-weight: 600; font-size: 0.82rem;">
                {{ $program->statusLabel() }}
            </span>
            @can('update', $program)
                <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.edit', $program) }}">Ubah Program</a>
            @endcan
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show small mb-4" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger small mb-4" role="alert">
            <div class="fw-semibold mb-1">Terdapat kesalahan:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Target & Meta Details -->
        <div class="col-12 col-lg-7">
            <section class="card card-app h-100">
                <div class="card-header bg-white border-bottom p-3 p-md-4">
                    <div class="eyebrow-luxury">Parameter Kinerja</div>
                    <h2 class="h5 text-navy mb-0">Informasi & Target Tahunan</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <dl class="row mb-0 gy-3">
                        <dt class="col-sm-4 text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Target Tahunan</dt>
                        <dd class="col-sm-8 fw-semibold text-navy fs-5 mb-0" style="font-family: var(--font-serif);">
                            {{ $program->target }} <span class="fs-6 fw-normal text-secondary">{{ $program->target_unit }}</span>
                        </dd>

                        <dt class="col-sm-4 text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Jadwal Periode</dt>
                        <dd class="col-sm-8 mb-0">{{ $program->start_date->format('d/m/Y') }} s.d. {{ $program->due_date->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4 text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Penanggung Jawab</dt>
                        <dd class="col-sm-8 mb-0">{{ $program->responsible->name }}</dd>

                        <dt class="col-sm-4 text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tim Pelaksana</dt>
                        <dd class="col-sm-8 mb-0">{{ $program->team?->name ?? 'Seluruh Unit' }}</dd>

                        @if ($program->objective)
                            <dt class="col-sm-4 text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tujuan</dt>
                            <dd class="col-sm-8 mb-0">{{ $program->objective }}</dd>
                        @endif

                        @if ($program->success_indicator)
                            <dt class="col-sm-4 text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Indikator</dt>
                            <dd class="col-sm-8 mb-0">{{ $program->success_indicator }}</dd>
                        @endif
                    </dl>
                </div>
            </section>
        </div>

        <!-- Progress Summary & Status Transitions -->
        <div class="col-12 col-lg-5">
            <section class="card card-app h-100">
                <div class="card-header bg-white border-bottom p-3 p-md-4">
                    <div class="eyebrow-luxury">Evaluasi Capaian</div>
                    <h2 class="h5 text-navy mb-0">Ringkasan Progres & Status</h2>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div>
                        @php
                            $totalTargetBulanan = $program->totalMonthlyTarget();
                            $totalRealisasiBulanan = $program->totalMonthlyActual();
                            $persenTahunan = (float) $program->target > 0 ? min(100, round(($totalRealisasiBulanan / (float) $program->target) * 100, 1)) : 0;
                        @endphp

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Akumulasi Realisasi Tahunan</span>
                                <span class="small fw-semibold text-navy">{{ $persenTahunan }}%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px; background: var(--border-gray);">
                                <div class="progress-bar" role="progressbar" style="width: {{ $persenTahunan }}%; background: var(--corporate-blue);" aria-valuenow="{{ $persenTahunan }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="p-3 rounded-2 border mb-3" style="background: #fafbfc; border-color: var(--border-subtle) !important;">
                            <div class="row text-center g-2">
                                <div class="col-6">
                                    <div class="small text-secondary fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Target 12 Bulan</div>
                                    <div class="fw-bold text-navy fs-5" style="font-family: var(--font-serif);">{{ $totalTargetBulanan }} <span class="fs-6 fw-normal text-secondary">{{ $program->target_unit }}</span></div>
                                </div>
                                <div class="col-6 border-start" style="border-color: var(--border-subtle) !important;">
                                    <div class="small text-secondary fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Realisasi</div>
                                    <div class="fw-bold fs-5" style="font-family: var(--font-serif); color: var(--corporate-blue);">{{ $totalRealisasiBulanan }} <span class="fs-6 fw-normal text-secondary">{{ $program->target_unit }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('changeStatus', $program)
                        @php
                            $user = auth()->user();
                            $isCampusHead = $user->role === \App\Models\User::ROLE_CAMPUS_HEAD;

                            $transitions = [];
                            if ($isCampusHead) {
                                $transitions = match ($program->status) {
                                    \App\Models\WorkProgram::STATUS_SUBMITTED => [
                                        \App\Models\WorkProgram::STATUS_APPROVED => 'Setujui Program',
                                        \App\Models\WorkProgram::STATUS_DRAFT => 'Kembalikan ke Draft',
                                    ],
                                    \App\Models\WorkProgram::STATUS_APPROVED => [
                                        \App\Models\WorkProgram::STATUS_ACTIVE => 'Jalankan Program (Aktif)',
                                        \App\Models\WorkProgram::STATUS_POSTPONED => 'Tunda Program',
                                    ],
                                    default => [
                                        \App\Models\WorkProgram::STATUS_ACTIVE => 'Set Berjalan',
                                        \App\Models\WorkProgram::STATUS_COMPLETED => 'Tandai Selesai',
                                        \App\Models\WorkProgram::STATUS_POSTPONED => 'Tunda',
                                    ],
                                };
                            } else {
                                $transitions = match ($program->status) {
                                    \App\Models\WorkProgram::STATUS_DRAFT => [
                                        \App\Models\WorkProgram::STATUS_SUBMITTED => 'Ajukan untuk Persetujuan',
                                        \App\Models\WorkProgram::STATUS_ACTIVE => 'Mulai Program (Langsung Aktif)',
                                        \App\Models\WorkProgram::STATUS_POSTPONED => 'Tunda Program',
                                    ],
                                    \App\Models\WorkProgram::STATUS_SUBMITTED => [
                                        \App\Models\WorkProgram::STATUS_DRAFT => 'Tarik Kembali ke Draft',
                                    ],
                                    \App\Models\WorkProgram::STATUS_APPROVED => [
                                        \App\Models\WorkProgram::STATUS_ACTIVE => 'Mulai Program (Aktif)',
                                        \App\Models\WorkProgram::STATUS_POSTPONED => 'Tunda Program',
                                    ],
                                    \App\Models\WorkProgram::STATUS_ACTIVE => [
                                        \App\Models\WorkProgram::STATUS_COMPLETED => 'Selesaikan Program',
                                        \App\Models\WorkProgram::STATUS_POSTPONED => 'Tunda Program',
                                    ],
                                    \App\Models\WorkProgram::STATUS_POSTPONED => [
                                        \App\Models\WorkProgram::STATUS_ACTIVE => 'Lanjutkan Kembali',
                                        \App\Models\WorkProgram::STATUS_COMPLETED => 'Selesaikan Program',
                                    ],
                                    default => [],
                                };
                            }
                        @endphp

                        @if (count($transitions))
                            <div class="border-top pt-3 mt-3" style="border-color: var(--border-subtle) !important;">
                                <form method="POST" action="{{ route('work-programs.status', $program) }}" class="d-flex gap-2 align-items-center">
                                    @csrf
                                    @method('PATCH')
                                    <select class="form-select form-select-sm form-control-editorial" name="status" required>
                                        <option value="">Pilih Aksi Status...</option>
                                        @foreach ($transitions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-app text-nowrap" type="submit">Ubah Status</button>
                                </form>
                            </div>
                        @endif
                    @endcan
                </div>
            </section>
        </div>
    </div>

    <!-- 12-Month Target & Realisasi Matrix -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <div class="eyebrow-luxury">Distribusi 12 Bulan</div>
            <h2 class="h5 text-navy mb-0">Matriks Target & Realisasi Bulanan (12 Bulan)</h2>
            <p class="text-secondary small mb-0">Rincian target operasional dan pencapaian aktual bulanan untuk periode tahun {{ $program->year }}.</p>
        </div>
        <div class="card-body p-3 p-md-4">
            @can('update', $program)
                <form method="POST" action="{{ route('work-programs.monthly-targets', $program) }}">
                    @csrf
                    @method('PUT')
            @endcan

            <div class="table-responsive">
                <table class="table table-editorial align-middle mb-3">
                    <thead>
                        <tr>
                            <th style="min-width: 110px;">Bulan</th>
                            <th style="min-width: 140px;">Target ({{ $program->target_unit }})</th>
                            <th style="min-width: 140px;">Realisasi ({{ $program->target_unit }})</th>
                            <th style="min-width: 100px;">Capaian (%)</th>
                            <th style="min-width: 120px;">Status</th>
                            <th style="min-width: 220px;">Catatan Kendala / Penjelasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($program->monthlyTargets as $monthly)
                            @php
                                $pct = $monthly->progressPercent();
                                $eff = $monthly->effectiveStatus();
                                $statusBadge = match($eff) {
                                    \App\Models\WorkProgramMonthlyTarget::STATUS_ACHIEVED => 'background: rgba(21, 128, 61, 0.1); color: var(--success);',
                                    \App\Models\WorkProgramMonthlyTarget::STATUS_IN_PROGRESS => 'background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue);',
                                    \App\Models\WorkProgramMonthlyTarget::STATUS_NEEDS_ATTENTION => 'background: rgba(183, 121, 31, 0.1); color: var(--warning);',
                                    default => 'background: rgba(100, 116, 139, 0.08); color: var(--text-muted);',
                                };
                            @endphp
                            <tr>
                                <td class="fw-semibold text-navy">{{ $monthly->monthName() }}</td>
                                <td>
                                    @can('update', $program)
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm form-control-editorial"
                                               name="targets[{{ $monthly->month }}][target]"
                                               value="{{ old('targets.'.$monthly->month.'.target', $monthly->target) }}" required>
                                    @else
                                        <span>{{ $monthly->target }}</span>
                                    @endcan
                                </td>
                                <td>
                                    @can('update', $program)
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm form-control-editorial"
                                               name="targets[{{ $monthly->month }}][actual]"
                                               value="{{ old('targets.'.$monthly->month.'.actual', $monthly->actual) }}" placeholder="Belum ada">
                                    @else
                                        <span>{{ $monthly->actual !== null ? $monthly->actual : 'Belum diisi' }}</span>
                                    @endcan
                                </td>
                                <td>
                                    @if ($pct === null)
                                        <span class="text-secondary small">Tidak ada target</span>
                                    @else
                                        <span class="fw-semibold {{ $pct >= 100 ? 'text-success' : ($pct < 50 && $monthly->actual !== null ? 'text-danger' : 'text-navy') }}">
                                            {{ $pct }}%
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="{{ $statusBadge }} font-weight: 500; font-size: 0.72rem;">{{ $monthly->statusLabel() }}</span>
                                </td>
                                <td>
                                    @can('update', $program)
                                        <input type="text" class="form-control form-control-sm form-control-editorial"
                                               name="targets[{{ $monthly->month }}][notes]"
                                               value="{{ old('targets.'.$monthly->month.'.notes', $monthly->notes) }}" maxlength="500" placeholder="Catatan kendala / realisasi...">
                                    @else
                                        <span class="small text-secondary">{{ $monthly->notes ?: 'Tidak ada catatan' }}</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @can('update', $program)
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-app px-4" type="submit">Simpan Target & Realisasi Bulanan</button>
                    </div>
                </form>
            @endcan
        </div>
    </section>

    <!-- Additional Progress Updates History -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <div class="eyebrow-luxury">Log Pelaksanaan</div>
            <h2 class="h5 text-navy mb-0">Riwayat Capaian Tambahan</h2>
            <p class="text-secondary small mb-0">Catatan perkembangan dan kendala yang dimasukkan anggota unit kerja.</p>
        </div>
        <div class="card-body p-3 p-md-4">
            @can('reportProgress', $program)
                <form class="row g-3 mb-4 p-3 rounded-2" method="POST" action="{{ route('work-programs.progress', $program) }}" style="background: #fafbfc; border: 1px solid var(--border-subtle);">
                    @csrf
                    <div class="col-12 col-md-3">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="actual">Capaian Terakhir ({{ $program->target_unit }})</label>
                        <input class="form-control form-control-editorial" id="actual" name="actual" type="number" min="0" step="0.01" required placeholder="Nilai capaian">
                    </div>
                    <div class="col-12 col-md-7">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="progress-notes">Catatan Perkembangan / Kendala</label>
                        <input class="form-control form-control-editorial" id="progress-notes" name="notes" maxlength="5000" placeholder="Keterangan singkat pelaksanaan kegiatan...">
                    </div>
                    <div class="col-12 col-md-2 d-grid align-items-end">
                        <button class="btn btn-app" type="submit">Catat Capaian</button>
                    </div>
                </form>
            @endcan

            <div class="table-responsive">
                <table class="table table-editorial align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Waktu</th>
                            <th>Capaian</th>
                            <th>Persentase</th>
                            <th>Dicatat Oleh</th>
                            <th class="pe-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($program->progressUpdates as $update)
                            <tr>
                                <td class="ps-3 small">{{ $update->created_at->format('d/m/Y H:i') }}</td>
                                <td class="fw-medium">{{ $update->actual }} {{ $program->target_unit }}</td>
                                <td>{{ $program->progressPercent($update) === null ? 'Belum ada data' : $program->progressPercent($update).'%' }}</td>
                                <td class="small">{{ $update->reporter?->name ?? 'Pengguna tidak tersedia' }}</td>
                                <td class="pe-3 small text-secondary">{{ $update->notes ?: 'Tidak ada catatan' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4 small">Belum ada pembaruan capaian tambahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
@endsection
