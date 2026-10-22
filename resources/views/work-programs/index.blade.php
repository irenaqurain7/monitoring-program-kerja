@extends('layouts.app')

@section('title', 'Program Kerja')

@section('content')
<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 text-navy mb-1">Program Kerja</h1>
            <p class="text-secondary mb-0">Daftar program kerja sesuai lingkup penugasan Anda.</p>
        </div>
        @if (auth()->user()->role === \App\Models\User::ROLE_UNIT_HEAD)
            <a class="btn btn-app" href="{{ route('work-programs.create') }}">Tambah Program</a>
        @endif
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach ($statuses as $status => $label)
            <div class="col-6 col-lg-3">
                <div class="card card-app h-100">
                    <div class="card-body">
                        <div class="small text-secondary">{{ $label }}</div>
                        <div class="fs-4 fw-semibold text-navy">{{ $summary->get($status, 0) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <section class="card card-app mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('work-programs.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="search">Cari nama program</label>
                    <input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="year">Tahun</label>
                    <input class="form-control" id="year" name="year" type="number" min="2000" max="2100" value="{{ $filters['year'] ?? '' }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="unit_id">Unit</label>
                    <select class="form-select" id="unit_id" name="unit_id">
                        <option value="">Semua unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) ($filters['unit_id'] ?? '') === (string) $unit->id)>
                                {{ $unit->name }}{{ auth()->user()->role === \App\Models\User::ROLE_CAMPUS_HEAD ? ' — '.$unit->campus->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card card-app">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Program</th>
                            <th>Kampus</th>
                            <th>Unit</th>
                            <th>Tahun</th>
                            <th>Tenggat</th>
                            <th>Status</th>
                            <th><span class="visually-hidden">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program)
                            <tr>
                                <td class="fw-semibold">{{ $program->name }}</td>
                                <td>{{ $program->campus->name }}</td>
                                <td>{{ $program->unit->name }}</td>
                                <td>{{ $program->year }}</td>
                                <td>{{ $program->due_date->format('d/m/Y') }}</td>
                                <td>{{ $program->statusLabel() }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('work-programs.show', $program) }}">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada program kerja yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($programs->hasPages())
            <div class="card-footer bg-white">{{ $programs->links() }}</div>
        @endif
    </section>
</main>
@endsection
