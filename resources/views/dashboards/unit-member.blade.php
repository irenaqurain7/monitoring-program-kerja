@extends('dashboards.base', [
    'heading' => 'Dashboard Anggota Unit',
    'description' => 'Pantau program kerja unit dan sampaikan aspirasi atau kendala melalui Two-Way Communication.',
])

@section('title', 'Dashboard Anggota Unit')
@section('summary')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-6">
            <div class="stat-editorial">
                <div class="stat-label">Program Kerja di Unit Anda</div>
                <div class="stat-number">{{ $unitPrograms->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-6">
            <div class="stat-editorial">
                <div class="stat-label">Aspirasi / Aduan Saya</div>
                <div class="stat-number {{ $myTopicsCount > 0 ? 'accent' : '' }}">{{ $myTopicsCount }}</div>
            </div>
        </div>
    </div>

    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="eyebrow-luxury">Tugas & Pelaksanaan</div>
                <h2 class="h5 text-navy mb-0">Program Kerja Terkait Unit Anda</h2>
                <div class="small text-secondary">Pantau jadwal dan progres target yang ditugaskan kepada unit Anda</div>
            </div>
            <div class="d-flex gap-2">
                @can('create', \App\Models\WorkProgram::class)
                    <a class="btn btn-sm btn-app" href="{{ route('work-programs.create') }}">Tambah Program</a>
                @endcan
                <a class="btn btn-sm btn-outline-navy" href="{{ route('communication.create') }}">Sampaikan Aspirasi</a>
                <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.index') }}">Daftar Program</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama Program</th>
                        <th>Tahun</th>
                        <th>Target</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitPrograms as $prog)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('work-programs.show', $prog) }}" class="fw-semibold text-navy text-decoration-none">
                                    {{ $prog->name }}
                                </a>
                            </td>
                            <td>{{ $prog->year }}</td>
                            <td class="fw-medium">{{ $prog->target }} {{ $prog->target_unit }}</td>
                            <td>{{ $prog->due_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border: 1px solid rgba(33, 74, 123, 0.2);">
                                    {{ $prog->statusLabel() }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.show', $prog) }}">Lihat Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Belum ada program kerja di unit Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
