@extends('layouts.app')

@section('title', 'Program Kerja | Monitoring Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Agenda Strategis</div>
            <h1 class="h2 text-navy fw-medium mb-1">Program Kerja</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">Daftar program kerja tahunan dan target kinerja sesuai lingkup penugasan Anda.</p>
        </div>
        @if (auth()->user()->role === \App\Models\User::ROLE_UNIT_HEAD)
            <a class="btn btn-app" href="{{ route('work-programs.create') }}">Tambah Program</a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success small mb-3" role="status">{{ session('status') }}</div>
    @endif

    <!-- Status Breakdown Metrics -->
    <div class="row g-3 mb-4">
        @foreach ($statuses as $status => $label)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-editorial p-3">
                    <div class="stat-label text-truncate" title="{{ $label }}" style="font-size: 0.68rem;">{{ $label }}</div>
                    <div class="stat-number fs-4">{{ $summary->get($status, 0) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter Card -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="eyebrow-luxury">Filter & Penelusuran</div>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('work-programs.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="search">Cari nama program</label>
                    <input class="form-control form-control-editorial" id="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Ketik kata kunci nama program">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="year">Tahun</label>
                    <input class="form-control form-control-editorial" id="year" name="year" type="number" min="2000" max="2100" value="{{ $filters['year'] ?? '' }}" placeholder="Tahun">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="status">Status</label>
                    <select class="form-select form-control-editorial" id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="unit_id">Unit</label>
                    <select class="form-select form-control-editorial" id="unit_id" name="unit_id">
                        <option value="">Semua unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) ($filters['unit_id'] ?? '') === (string) $unit->id)>
                                {{ $unit->name }}{{ auth()->user()->role === \App\Models\User::ROLE_CAMPUS_HEAD ? ' ('.$unit->campus->name.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button class="btn btn-outline-navy" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Table Card -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="eyebrow-luxury">Daftar Terdata</div>
                <h2 class="h5 text-navy mb-0">Program Kerja Kampus</h2>
            </div>
            <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600;">
                {{ $programs->total() }} Program
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama Program</th>
                        <th>Kampus</th>
                        <th>Unit</th>
                        <th>Tahun</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programs as $program)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('work-programs.show', $program) }}" class="fw-semibold text-navy text-decoration-none">
                                    {{ $program->name }}
                                </a>
                            </td>
                            <td class="small">{{ $program->campus->name }}</td>
                            <td>{{ $program->unit->name }}</td>
                            <td>{{ $program->year }}</td>
                            <td>{{ $program->due_date->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $badgeStyle = match($program->status) {
                                        'completed' => 'background: rgba(21, 128, 61, 0.1); color: var(--success);',
                                        'active' => 'background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue);',
                                        'draft' => 'background: rgba(100, 116, 139, 0.1); color: var(--text-muted);',
                                        default => 'background: rgba(183, 121, 31, 0.1); color: var(--warning);'
                                    };
                                @endphp
                                <span class="badge" style="{{ $badgeStyle }} border: 1px solid rgba(0,0,0,0.06); font-weight: 500;">
                                    {{ $program->statusLabel() }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.show', $program) }}">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">Belum ada program kerja yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($programs->hasPages())
            <div class="card-footer bg-white border-top p-3">{{ $programs->links() }}</div>
        @endif
    </section>
</main>
@endsection
