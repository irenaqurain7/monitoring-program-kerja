@extends('layouts.app')

@section('title', 'Unit '.$unit->name.' | Monitoring Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="mb-3">
        <a href="{{ route('units.index') }}" class="text-decoration-none small fw-semibold" style="color: var(--corporate-blue);">
            &larr; Kembali ke Kelola Unit
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success small mb-3" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger small mb-3">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Unit Overview Card -->
    <section class="card card-app mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="eyebrow-luxury">Kampus {{ $unit->campus->name }}</div>
                    <h1 class="h3 text-navy mb-1" style="font-family: var(--font-serif); font-weight: 500;">{{ $unit->name }}</h1>
                    <p class="text-secondary small mb-2" style="max-width: 650px;">{{ $unit->description ?: 'Belum ada deskripsi profil unit.' }}</p>
                    @if ($unit->is_active)
                        <span class="badge" style="background: rgba(21, 128, 61, 0.1); color: var(--success); font-weight: 500;">Status Unit: Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Status Unit: Nonaktif</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.index', ['unit_id' => $unit->id]) }}">Lihat Program Unit</a>
                    <a class="btn btn-sm btn-app" href="#tim-baru">Tambah Tim</a>
                </div>
            </div>

            <hr class="my-4" style="border-color: var(--border-subtle);">

            <div>
                <div class="eyebrow-luxury mb-2">Perbarui Profil Unit</div>
                <form method="POST" action="{{ route('units.update', $unit) }}" class="row g-3">
                    @csrf @method('PUT')
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="unit-name">Nama unit</label>
                        <input class="form-control form-control-editorial @error('name') is-invalid @enderror" id="unit-name" name="name" value="{{ old('name', $unit->name) }}" maxlength="255" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="unit-description">Informasi</label>
                        <input class="form-control form-control-editorial @error('description') is-invalid @enderror" id="unit-description" name="description" value="{{ old('description', $unit->description) }}" maxlength="5000">
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-2 d-grid align-items-end">
                        <button class="btn btn-outline-navy" type="submit">Simpan Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Tim Management Section -->
    <section class="card card-app mb-4" id="tim">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <div class="eyebrow-luxury">Struktur Sub-Unit</div>
            <h2 class="h5 text-navy mb-0">Tim Kerja Internal</h2>
            <p class="text-secondary small mb-0">Tim dikelola terpisah dari akun pengguna untuk fleksibilitas penugasan program.</p>
        </div>
        <div class="card-body p-3 p-md-4">
            @unless ($unit->is_active)
                <div class="alert alert-warning small mb-3">Unit sedang nonaktif. Aktifkan unit terlebih dahulu untuk menambah tim baru.</div>
            @else
                <form method="POST" action="{{ route('units.teams.store', $unit) }}" class="row g-3 mb-4 p-3 rounded-2" id="tim-baru" style="background: #fafbfc; border: 1px solid var(--border-subtle);">
                    @csrf
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="team-name">Nama tim</label>
                        <input class="form-control form-control-editorial @error('name') is-invalid @enderror" id="team-name" name="name" maxlength="255" placeholder="Contoh: Tim Sosialisasi" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="team-description">Informasi</label>
                        <input class="form-control form-control-editorial @error('description') is-invalid @enderror" id="team-description" name="description" maxlength="5000" placeholder="Fungsi dan tanggung jawab tim">
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-2 d-grid align-items-end">
                        <button class="btn btn-app" type="submit">Tambah Tim</button>
                    </div>
                </form>
            @endunless

            <div class="table-responsive">
                <table class="table table-editorial align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Nama tim</th>
                            <th>Informasi</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($unit->teams as $team)
                        <tr>
                            <td class="ps-3" style="min-width: 280px;">
                                <form method="POST" action="{{ route('units.teams.update', [$unit, $team]) }}" class="d-flex flex-wrap gap-2">
                                    @csrf @method('PUT')
                                    <input class="form-control form-control-sm form-control-editorial" name="name" aria-label="Nama tim" value="{{ $team->name }}" maxlength="255" required style="max-width: 130px;">
                                    <input class="form-control form-control-sm form-control-editorial" name="description" aria-label="Informasi tim" value="{{ $team->description }}" maxlength="5000" style="max-width: 140px;">
                                    <button class="btn btn-sm btn-outline-navy" type="submit">Simpan</button>
                                </form>
                            </td>
                            <td class="small">{{ $team->description ?: '-' }}</td>
                            <td class="fw-medium">{{ $team->work_programs_count }}</td>
                            <td>
                                @if ($team->is_active)
                                    <span class="badge" style="background: rgba(21, 128, 61, 0.1); color: var(--success); font-weight: 500;">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary" style="font-size: 0.68rem;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <form method="POST" action="{{ route('units.teams.status', [$unit, $team]) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $team->is_active ? '0' : '1' }}">
                                    <button class="btn btn-sm {{ $team->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                                        {{ $team->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Belum ada tim terdaftar di unit ini.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Program Kerja Unit Section -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <div class="eyebrow-luxury">Agenda Kinerja</div>
            <h2 class="h5 text-navy mb-0">Program Kerja Terkait Unit</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Program</th>
                        <th>Tim</th>
                        <th>Target</th>
                        <th>Capaian terakhir</th>
                        <th>Progres</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($unit->workPrograms as $program)
                    @php($latest = $program->latestProgress)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('work-programs.show', $program) }}" class="fw-semibold text-navy text-decoration-none">
                                {{ $program->name }}
                            </a>
                        </td>
                        <td class="small">{{ $program->team?->name ?? 'Seluruh unit' }}</td>
                        <td class="fw-medium">{{ $program->target }} {{ $program->target_unit }}</td>
                        <td class="small">{{ $latest ? $latest->actual.' '.$program->target_unit : 'Belum ada data' }}</td>
                        <td>
                            @php($pct = $program->progressPercent())
                            @if ($pct === null)
                                <span class="text-secondary small">Belum ada data</span>
                            @else
                                <span class="fw-semibold {{ $pct >= 80 ? 'text-success' : 'text-navy' }}">{{ $pct }}%</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border: 1px solid rgba(33, 74, 123, 0.2);">
                                {{ $program->statusLabel() }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a class="btn btn-sm btn-outline-navy" href="{{ route('work-programs.show', $program) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">Belum ada program kerja untuk unit ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
@endsection
