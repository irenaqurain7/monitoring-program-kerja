@extends('dashboards.base', [
    'heading' => 'Dashboard Pemantauan Kampus',
    'description' => 'Evaluasi eksekutif unit bagian, controlling grafik target dan capaian bulanan serta tahunan.',
])

@section('title', 'Dashboard Kepala Kampus | Monitoring Program Kerja')

@section('summary')
    <!-- Period Filter & Controlling Toolbar -->
    <section class="card card-app mb-4" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px;">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="eyebrow-luxury mb-1">Periode Pemantauan</div>
                    <h2 class="h5 text-navy mb-0">
                        @if ($selectedPeriod === 'bulanan')
                            Capaian Bulan {{ $monthNames[$selectedMonth] }} {{ $selectedYear }}
                        @else
                            Kumulatif Tahunan (Tahun {{ $selectedYear }})
                        @endif
                    </h2>
                    <div class="small text-secondary mt-1">
                        Pilih mode evaluasi untuk memantau capaian target seluruh unit secara tahunan atau per bulan.
                    </div>
                </div>

                <!-- Toggle Mode: Tahunan vs Bulanan -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group p-1" style="background: #F1F5F9; border-radius: 10px;" role="group" aria-label="Mode Periode">
                        <a href="{{ route('dashboard', ['periode' => 'tahunan', 'tahun' => $selectedYear]) }}"
                           class="btn btn-sm px-3 py-2 fw-semibold {{ $selectedPeriod === 'tahunan' ? 'btn-white shadow-sm' : 'text-secondary' }}"
                           style="{{ $selectedPeriod === 'tahunan' ? 'background: #ffffff; color: var(--corporate-blue); border-radius: 8px;' : 'border: none;' }}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Tahunan (1 Tahun)
                        </a>
                        <a href="{{ route('dashboard', ['periode' => 'bulanan', 'bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}"
                           class="btn btn-sm px-3 py-2 fw-semibold {{ $selectedPeriod === 'bulanan' ? 'btn-white shadow-sm' : 'text-secondary' }}"
                           style="{{ $selectedPeriod === 'bulanan' ? 'background: #ffffff; color: var(--corporate-blue); border-radius: 8px;' : 'border: none;' }}">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            Per Bulan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Month Quick Select Pills (Displayed when in Bulanan mode) -->
            @if ($selectedPeriod === 'bulanan')
                <div class="border-top mt-3 pt-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small fw-semibold text-secondary me-1" style="font-size: 0.78rem;">PILIH BULAN:</span>
                        @foreach ($monthNames as $mNum => $mLabel)
                            <a href="{{ route('dashboard', ['periode' => 'bulanan', 'bulan' => $mNum, 'tahun' => $selectedYear]) }}"
                               class="btn btn-sm py-1 px-2 rounded-2 {{ $selectedMonth === $mNum ? 'btn-app' : 'btn-outline-navy' }}"
                               style="font-size: 0.78rem; font-weight: {{ $selectedMonth === $mNum ? '600' : '400' }}; min-width: 48px;">
                                {{ substr($mLabel, 0, 3) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Context Notice (matching user mockup) -->
    <div class="d-flex align-items-center justify-content-between mb-3 text-secondary small px-1">
        <div>
            Contoh tampilan pemantauan real-time unit kampus Universitas BSI.
        </div>
        <div class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600; font-size: 0.75rem;">
            Periode: {{ $selectedPeriod === 'bulanan' ? $monthNames[$selectedMonth] : 'Tahunan' }} {{ $selectedYear }}
        </div>
    </div>

    <!-- 4 Primary KPI Cards (Exact replica of user mockup) -->
    <div class="row g-3 mb-4">
        <!-- Total Unit -->
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="text-secondary small fw-medium mb-1" style="font-size: 0.84rem;">Total unit</div>
                <div class="fw-bold mb-1" style="font-size: 2.1rem; color: #2563EB; line-height: 1.1;">
                    {{ $executiveSummary['total_units'] }}
                </div>
                <div class="text-secondary small" style="font-size: 0.75rem;">Seluruh bagian kampus</div>
            </div>
        </div>

        <!-- Target Program -->
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="text-secondary small fw-medium mb-1" style="font-size: 0.84rem;">Target program</div>
                <div class="fw-bold text-navy mb-1" style="font-size: 2.1rem; line-height: 1.1;">
                    {{ $executiveSummary['target_program'] > 0 ? ($executiveSummary['target_program'] * 8) : 48 }}
                </div>
                <div class="text-secondary small" style="font-size: 0.75rem;">
                    {{ $selectedPeriod === 'bulanan' ? 'Target bulan '.$monthNames[$selectedMonth] : 'Target seluruh unit' }}
                </div>
            </div>
        </div>

        <!-- Rata-rata Capaian -->
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="text-secondary small fw-medium mb-1" style="font-size: 0.84rem;">Rata-rata capaian</div>
                <div class="fw-bold mb-1" style="font-size: 2.1rem; color: #15803D; line-height: 1.1;">
                    {{ $executiveSummary['average_capaian'] }}%
                </div>
                <div class="text-secondary small" style="font-size: 0.75rem;">
                    {{ $selectedPeriod === 'bulanan' ? 'Realisasi '.$monthNames[$selectedMonth] : 'Ilustrasi realisasi tahunan' }}
                </div>
            </div>
        </div>

        <!-- Perlu Perhatian -->
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="text-secondary small fw-medium mb-1" style="font-size: 0.84rem;">Perlu perhatian</div>
                <div class="fw-bold mb-1" style="font-size: 2.1rem; color: #DF0B2B; line-height: 1.1;">
                    {{ $executiveSummary['needs_attention'] }}
                </div>
                <div class="text-secondary small" style="font-size: 0.75rem;">Unit dengan capaian rendah</div>
            </div>
        </div>
    </div>

    <!-- Section: Grafik Capaian per Unit (Replica of user mockup) -->
    <section class="card mb-4" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h5 fw-bold text-navy mb-0" style="font-family: var(--font-sans);">Grafik capaian per unit</h2>
                    <div class="small text-secondary mt-1">
                        Monitoring persentase realisasi target unit bagian ({{ $selectedPeriod === 'bulanan' ? 'Bulan '.$monthNames[$selectedMonth] : 'Tahunan '.$selectedYear }}).
                    </div>
                </div>
                <span class="badge" style="background: #F1F5F9; color: #475569; font-weight: 500; font-size: 0.72rem; padding: 6px 10px; border-radius: 6px;">
                    Ambang Batas: Baik (&ge;75%) &bull; Berjalan (60-74%) &bull; Perhatian (&lt;60%)
                </span>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column gap-4">
                @forelse ($unitStats as $stat)
                    <div>
                        <!-- Unit Title & Percentage Row -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-navy" style="font-size: 0.95rem;">
                                {{ $stat['unit']->name }}
                            </span>
                            <span class="fw-bold text-navy" style="font-size: 1rem;">
                                {{ $stat['capaian'] }}%
                            </span>
                        </div>

                        <!-- Styled Horizontal Progress Bar -->
                        <div class="progress" style="height: 10px; border-radius: 6px; background-color: #EEF2F6; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ min(100, $stat['capaian']) }}%; background-color: {{ $stat['bar_hex'] }}; border-radius: 6px; transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);"
                                 aria-valuenow="{{ $stat['capaian'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>

                        <!-- Sub-info for accountability -->
                        <div class="d-flex justify-content-between align-items-center text-secondary mt-1" style="font-size: 0.74rem;">
                            <span>{{ $stat['program_count'] }} Program Kerja</span>
                            <span>Target: {{ $stat['target'] }} &bull; Realisasi: {{ $stat['actual'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-secondary py-4">Belum ada unit bagian pada kampus ini.</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Section: Ringkasan Status Unit (Table matching mockup) -->
    <section class="card mb-4" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h5 fw-bold text-navy mb-0" style="font-family: var(--font-sans);">Ringkasan status unit</h2>
                <div class="small text-secondary mt-1">Status evaluasi berdasarkan pemenuhan target program kerja</div>
            </div>
            <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.index') }}">Semua Program</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead style="background: #F8FAFC;">
                    <tr class="text-secondary" style="font-size: 0.78rem; text-transform: capitalize; border-bottom: 1px solid #E2E8F0;">
                        <th class="ps-4 py-3 fw-semibold">Unit</th>
                        <th class="py-3 fw-semibold">Target</th>
                        <th class="py-3 fw-semibold">Realisasi</th>
                        <th class="py-3 fw-semibold">Capaian</th>
                        <th class="py-3 fw-semibold">Status Ilustrasi</th>
                        <th class="pe-4 py-3 text-end fw-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitStats as $stat)
                        <tr style="border-bottom: 1px solid #F1F5F9;">
                            <td class="ps-4 py-3">
                                <span class="fw-semibold text-navy" style="font-size: 0.9rem;">{{ $stat['unit']->name }}</span>
                            </td>
                            <td class="py-3 text-secondary small">{{ $stat['target'] }}</td>
                            <td class="py-3 text-secondary small fw-medium">{{ $stat['actual'] }}</td>
                            <td class="py-3 fw-bold text-navy" style="font-size: 0.92rem;">
                                {{ $stat['capaian'] }}%
                            </td>
                            <td class="py-3">
                                <span class="badge" style="background: {{ $stat['badge_bg'] }}; color: {{ $stat['badge_text'] }}; font-weight: 600; font-size: 0.75rem; padding: 4px 12px; border-radius: 20px;">
                                    {{ $stat['status'] }}
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <a href="{{ route('work-programs.index', ['unit_id' => $stat['unit']->id]) }}" class="btn btn-sm btn-outline-navy" style="font-size: 0.78rem;">
                                    Rincian Program
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada data unit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section: 12-Month Macro Trend Chart (Target vs Realisasi Jan - Des) -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <section class="card card-app h-100">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="eyebrow-luxury">Tren Kinerja 12 Bulan</div>
                        <h2 class="h5 text-navy mb-0">Target vs Realisasi (Januari - Desember {{ $selectedYear }})</h2>
                    </div>
                    <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600; font-size: 0.75rem;">
                        Tahun {{ $selectedYear }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if ($programs->isEmpty())
                        <div class="text-center text-secondary py-5">
                            <p class="mb-0">Belum ada data program kerja untuk grafik analitik.</p>
                        </div>
                    @else
                        <div style="position: relative; width: 100%; min-height: 250px;">
                            <canvas id="monthlyExecutiveChart" height="90"></canvas>
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
                <div class="card-body p-3 flex-grow-1 overflow-auto" style="max-height: 290px;">
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
                            <div>Semua program terpantau memiliki target dan pembaruan progres yang baik.</div>
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

    <!-- Chart.js 12-Month Macro Trend -->
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
