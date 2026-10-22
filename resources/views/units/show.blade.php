@extends('layouts.app')

@section('title', 'Unit '.$unit->name)

@section('content')
<main class="container py-4 py-md-5">
    <div class="mb-4"><a href="{{ route('units.index') }}" class="text-decoration-none">&larr; Kelola Unit</a></div>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <section class="card card-app mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div>
                    <p class="small text-secondary mb-1">{{ $unit->campus->name }}</p>
                    <h1 class="h3 text-navy">{{ $unit->name }}</h1>
                    <p class="text-secondary">{{ $unit->description ?: 'Belum ada informasi unit.' }}</p>
                    <span class="badge {{ $unit->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <a class="btn btn-outline-primary" href="{{ route('work-programs.index', ['unit_id' => $unit->id]) }}">Lihat Program</a>
                    <a class="btn btn-app" href="#tim-baru">Tambah Tim</a>
                </div>
            </div>
            <hr>
            <form method="POST" action="{{ route('units.update', $unit) }}" class="row g-3">
                @csrf @method('PUT')
                <div class="col-12 col-md-4"><label class="form-label" for="unit-name">Nama unit</label><input class="form-control" id="unit-name" name="name" value="{{ old('name', $unit->name) }}" maxlength="255" required></div>
                <div class="col-12 col-md-6"><label class="form-label" for="unit-description">Informasi</label><input class="form-control" id="unit-description" name="description" value="{{ old('description', $unit->description) }}" maxlength="5000"></div>
                <div class="col-12 col-md-2 d-grid"><button class="btn btn-outline-primary mt-md-4" type="submit">Simpan Unit</button></div>
            </form>
        </div>
    </section>

    <section class="card card-app mb-4" id="tim">
        <div class="card-header bg-white border-0 p-4 pb-0"><h2 class="h5 text-navy">Tim</h2><p class="text-secondary mb-0">Tim dikelola terpisah dari akun pengguna.</p></div>
        <div class="card-body p-4">
            @unless ($unit->is_active)
                <div class="alert alert-warning">Unit nonaktif. Aktifkan unit untuk menambah tim.</div>
            @else
                <form method="POST" action="{{ route('units.teams.store', $unit) }}" class="row g-3 mb-4" id="tim-baru">
                    @csrf
                    <div class="col-12 col-md-4"><label class="form-label" for="team-name">Nama tim</label><input class="form-control" id="team-name" name="name" maxlength="255" required></div>
                    <div class="col-12 col-md-6"><label class="form-label" for="team-description">Informasi</label><input class="form-control" id="team-description" name="description" maxlength="5000"></div>
                    <div class="col-12 col-md-2 d-grid"><button class="btn btn-app mt-md-4" type="submit">Tambah Tim</button></div>
                </form>
            @endunless

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr><th>Nama tim</th><th>Informasi</th><th>Program</th><th>Status</th><th>Tindakan</th></tr></thead>
                    <tbody>
                    @forelse ($unit->teams as $team)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('units.teams.update', [$unit, $team]) }}" class="d-flex flex-wrap gap-2">
                                    @csrf @method('PUT')
                                    <input class="form-control form-control-sm" name="name" aria-label="Nama tim" value="{{ $team->name }}" maxlength="255" required>
                                    <input class="form-control form-control-sm" name="description" aria-label="Informasi tim" value="{{ $team->description }}" maxlength="5000">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Simpan</button>
                                </form>
                            </td>
                            <td>{{ $team->description ?: '—' }}</td>
                            <td>{{ $team->work_programs_count }}</td>
                            <td><span class="badge {{ $team->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $team->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('units.teams.status', [$unit, $team]) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $team->is_active ? '0' : '1' }}">
                                    <button class="btn btn-sm {{ $team->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">{{ $team->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada tim di unit ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="card card-app">
        <div class="card-header bg-white border-0 p-4 pb-0"><h2 class="h5 text-navy">Program Kerja</h2></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">Program</th><th>Tim</th><th>Target</th><th>Capaian terakhir</th><th>Progres</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($unit->workPrograms as $program)
                    @php($latest = $program->latestProgress)
                    <tr>
                        <td class="ps-4">{{ $program->name }}</td>
                        <td>{{ $program->team?->name ?? 'Seluruh unit' }}</td>
                        <td>{{ $program->target }} {{ $program->target_unit }}</td>
                        <td>{{ $latest ? $latest->actual.' '.$program->target_unit : 'Belum ada data' }}</td>
                        <td>{{ $program->progressPercent() === null ? 'Belum ada data' : $program->progressPercent().'%' }}</td>
                        <td>{{ $program->statusLabel() }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('work-programs.show', $program) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada program kerja untuk unit ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
@endsection
