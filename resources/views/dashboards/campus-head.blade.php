@extends('dashboards.base', [
    'heading' => 'Dasbor Pemantauan Kampus',
    'description' => 'Evaluasi eksekutif unit bagian, program kerja tahunan, capaian bulanan, dan koordinasi kampus.',
])

@section('title', 'Dashboard Kepala Kampus')
@section('summary')
    <!-- Primary Executive KPI Cards (Row 1) -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Unit aktif</div>
                <div class="stat-number">{{ $summary['active_units'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Tim aktif</div>
                <div class="stat-number">{{ $summary['active_teams'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Program kerja</div>
                <div class="stat-number">{{ $summary['programs'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Program selesai</div>
                <div class="stat-number" style="color: var(--success);">{{ $summary['completed'] }}</div>
            </div>
        </div>
    </div>

    <!-- Secondary Operational Status KPI Cards (Row 2) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Program berjalan</div>
                <div class="stat-number">{{ $summary['running'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Program terlambat</div>
                <div class="stat-number {{ $summary['overdue'] > 0 ? 'accent' : '' }}">{{ $summary['overdue'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Program berjalan tanpa pembaruan progres</div>
                <div class="stat-number {{ $summary['without_update'] > 0 ? 'accent' : '' }}">{{ $summary['without_update'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-editorial">
                <div class="stat-label">Aduan perlu tindak lanjut</div>
                <div class="stat-number {{ $summary['pending_topics'] > 0 ? 'accent' : '' }}">{{ $summary['pending_topics'] }}</div>
            </div>
        </div>
    </div>

    <!-- Analytic Context Note -->
    <div class="card card-app p-3 mb-4" style="background: #fafbfc;">
        <div class="d-flex align-items-center gap-2 small text-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted flex-shrink-0"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <div>Target memiliki satuan yang berbeda, sehingga tidak dijumlahkan lintas program. Persentase rata-rata dihitung dari program dengan target positif dan pembaruan capaian.</div>
        </div>
    </div>

    <!-- Analytics Chart & Priority Attention Row -->
    <div class="row g-4 mb-4">
        <!-- 12-Month Target vs Realisasi Chart -->
        <div class="col-12 col-xl-8">
            <section class="card card-app h-100">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="eyebrow-luxury">Analisis Kinerja Bulanan</div>
                        <h2 class="h5 text-navy mb-0">Tren Target vs Realisasi (Januari - Desember)</h2>
                    </div>
                    <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600; font-size: 0.75rem;">
                        Tahun {{ date('Y') }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if ($programs->isEmpty())
                        <div class="text-center text-secondary py-5">
                            <p class="mb-0">Belum ada data program kerja untuk grafik analitik.</p>
                        </div>
                    @else
                        <div style="position: relative; width: 100%; min-height: 260px;">
                            <canvas id="monthlyExecutiveChart" height="100"></canvas>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <!-- Priority Attention Programs -->
        <div class="col-12 col-xl-4">
            <section class="card card-app h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="eyebrow-luxury">Fokus Tindakan</div>
                        <h2 class="h5 text-navy mb-0">Program Perlu Perhatian</h2>
                    </div>
                    @if ($attentionPrograms->isNotEmpty())
                        <span class="badge" style="background: rgba(223, 11, 43, 0.1); color: var(--red); font-weight: 600;">
                            {{ $attentionPrograms->count() }} Perhatian
                        </span>
                    @endif
                </div>
                <div class="card-body p-3 flex-grow-1 overflow-auto" style="max-height: 330px;">
                    @forelse ($attentionPrograms as $prog)
                        <div class="p-3 mb-2 border rounded-2" style="background: #ffffff; border-color: var(--border-subtle) !important;">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <a href="{{ route('work-programs.show', $prog) }}" class="fw-semibold text-navy text-decoration-none small">
                                    {{ $prog->name }}
                                </a>
                                @if ($prog->due_date->lt(now()->startOfDay()))
                                    <span class="badge text-bg-danger" style="font-size: 0.65rem;">Terlambat</span>
                                @else
                                    <span class="badge text-bg-warning" style="font-size: 0.65rem; color: #ffffff !important;">Tanpa Update</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-secondary small" style="font-size: 0.74rem;">
                                <span>Unit: {{ $prog->unit?->name ?? 'N/A' }}</span>
                                <span>Tenggat: {{ $prog->due_date->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4 small">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-success mb-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <div>Semua program terpantau tepat waktu dan memiliki pembaruan capaian.</div>
                        </div>
                    @endforelse
                </div>
                <div class="card-footer bg-white border-top p-2 text-center">
                    <a href="{{ route('work-programs.index') }}" class="small text-decoration-none fw-semibold" style="color: var(--corporate-blue); font-size: 0.78rem;">
                        Lihat Semua Program Kerja &rarr;
                    </a>
                </div>
            </section>
        </div>
    </div>

    <!-- Unit Performance Comparison Table -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="eyebrow-luxury">Pembandingan Antarunit</div>
                <h2 class="h5 text-navy mb-0">Pemantauan unit: {{ $assignedCampus->name }}</h2>
                <div class="small text-secondary">Evaluasi kinerja program kerja di seluruh unit bagian kampus secara objektif</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-app" href="{{ route('work-programs.index') }}">Program Kerja</a>
                <a class="btn btn-sm btn-outline-navy" href="{{ route('units.index') }}">Kelola Unit</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-editorial align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Unit</th>
                        <th>Tim</th>
                        <th>Program</th>
                        <th>Rata-rata progres</th>
                        <th>Selesai</th>
                        <th>Berjalan</th>
                        <th>Terlambat</th>
                        <th>Pemantauan</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        @php
                            $percentages = $unit->workPrograms
                                ->map(fn ($program) => $program->progressPercent())
                                ->filter(fn ($percentage) => $percentage !== null);
                            $average = $percentages->isEmpty() ? null : round($percentages->avg(), 1);
                            $completed = $unit->workPrograms->where('status', \App\Models\WorkProgram::STATUS_COMPLETED)->count();
                            $running = $unit->workPrograms->where('status', \App\Models\WorkProgram::STATUS_ACTIVE);
                            $overdue = $running->filter(fn ($program) => $program->due_date->lt(now()->startOfDay()))->count();
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-navy">{{ $unit->name }}</div>
                                @unless ($unit->is_active)<span class="badge text-bg-secondary" style="font-size: 0.65rem;">Nonaktif</span>@endunless
                            </td>
                            <td>{{ $unit->active_teams_count }} aktif / {{ $unit->teams_count }} total</td>
                            <td class="fw-medium">{{ $unit->work_programs_count }}</td>
                            <td>
                                @if ($average === null)
                                    <span class="text-secondary small">Belum ada data</span>
                                @else
                                    <span class="fw-semibold {{ $average >= 80 ? 'text-success' : 'text-navy' }}">{{ $average }}%</span>
                                @endif
                            </td>
                            <td>{{ $completed }}</td>
                            <td>{{ $running->count() }}</td>
                            <td>
                                @if ($overdue > 0)
                                    <span class="badge text-bg-danger">{{ $overdue }}</span>
                                @else
                                    <span>0</span>
                                @endif
                            </td>
                            <td>
                                @if ($unit->programs_without_update_count > 0)
                                    <span class="badge" style="background: rgba(223, 11, 43, 0.1); color: var(--red); font-weight: 500;">Perlu pembaruan</span>
                                @else
                                    <span class="badge" style="background: rgba(21, 128, 61, 0.1); color: var(--success); font-weight: 500;">Terpantau</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <a class="btn btn-sm btn-outline-navy" href="{{ route('units.show', $unit) }}">Lihat Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-secondary py-5">Belum ada unit pada kampus ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Two-Way Communication Executive Overview -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
            <div>
                <div class="eyebrow-luxury">Koordinasi Terbuka</div>
                <h2 class="h5 text-navy mb-0">Two-Way Communication Antarunit</h2>
            </div>
            <a class="btn btn-sm btn-outline-navy" href="{{ route('communication.index') }}">Buka Kotak Masuk</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-editorial align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">No. Ref</th>
                            <th>Judul Aduan / Koordinasi</th>
                            <th>Unit</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th class="pe-4 text-end">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTopics as $topic)
                            <tr>
                                <td class="ps-4 fw-medium text-navy small">{{ $topic->reference_number }}</td>
                                <td>
                                    <a href="{{ route('communication.show', $topic) }}" class="fw-semibold text-navy text-decoration-none">
                                        {{ $topic->title }}
                                    </a>
                                </td>
                                <td class="small">{{ $topic->unit?->name ?? 'Lintas Unit' }}</td>
                                <td>
                                    @php
                                        $priorityClass = match($topic->priority) {
                                            'urgent' => 'text-bg-danger',
                                            'high' => 'text-bg-warning',
                                            'medium' => 'text-bg-primary',
                                            default => 'text-bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $priorityClass }}" style="font-size: 0.68rem;">{{ $topic->priorityLabel() }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border: 1px solid rgba(33, 74, 123, 0.2);">
                                        {{ $topic->statusLabel() }}
                                    </span>
                                </td>
                                <td class="small text-secondary">{{ $topic->updated_at->diffForHumans() }}</td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('communication.show', $topic) }}" class="btn btn-sm btn-outline-navy">Respon</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4 small">Belum ada aduan atau topik komunikasi aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Chart.js Initialization -->
    @if ($programs->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('monthlyExecutiveChart');
                if (canvas && typeof Chart !== 'undefined') {
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: @json($chartMonths),
                            datasets: [
                                {
                                    label: 'Target Terencana',
                                    data: @json($chartTargets),
                                    backgroundColor: 'rgba(33, 74, 123, 0.85)',
                                    borderColor: '#214A7B',
                                    borderWidth: 1,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Realisasi Capaian',
                                    data: @json($chartActuals),
                                    backgroundColor: 'rgba(223, 11, 43, 0.85)',
                                    borderColor: '#DF0B2B',
                                    borderWidth: 1,
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    align: 'end',
                                    labels: {
                                        boxWidth: 10,
                                        usePointStyle: true,
                                        font: {
                                            family: "'Plus Jakarta Sans', sans-serif",
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#0B1D3A',
                                    padding: 10,
                                    cornerRadius: 6,
                                    titleFont: {
                                        family: "'Plus Jakarta Sans', sans-serif",
                                        weight: '600'
                                    },
                                    bodyFont: {
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(11, 29, 58, 0.05)'
                                    },
                                    ticks: {
                                        font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endif
@endsection
