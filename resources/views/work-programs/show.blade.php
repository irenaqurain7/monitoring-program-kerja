@extends('layouts.app')

@section('title', $program->name)

@section('content')
<main class="container py-4">
    <div class="mb-4">
        <a href="{{ route('work-programs.index') }}" class="text-decoration-none">&larr; Daftar program kerja</a>
    </div>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <section class="card card-app">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <div>
                    <div class="text-secondary">{{ $program->campus->name }} / {{ $program->unit->name }} · {{ $program->year }}</div>
                    <h1 class="h3 text-navy mt-2 mb-0">{{ $program->name }}</h1>
                </div>
                <div class="text-md-end">
                    <span class="badge text-bg-primary">{{ $program->statusLabel() }}</span>
                    @can('update', $program)
                        <div class="mt-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('work-programs.edit', $program) }}">Ubah Program</a></div>
                    @endcan
                </div>
            </div>
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <h2 class="h6 text-navy">Target dan jadwal</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Target</dt><dd class="col-sm-7">{{ $program->target }} {{ $program->target_unit }}</dd>
                        <dt class="col-sm-5">Tanggal mulai</dt><dd class="col-sm-7">{{ $program->start_date->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5">Tenggat waktu</dt><dd class="col-sm-7">{{ $program->due_date->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5">Penanggung jawab</dt><dd class="col-sm-7">{{ $program->responsible->name }}</dd>
                        <dt class="col-sm-5">Tim</dt><dd class="col-sm-7">{{ $program->team?->name ?? 'Seluruh unit' }}</dd>
                    </dl>
                </div>
                <div class="col-12 col-lg-6">
                    @foreach (['description' => 'Deskripsi', 'objective' => 'Tujuan', 'success_indicator' => 'Indikator keberhasilan', 'notes' => 'Catatan'] as $field => $label)
                        @if ($program->{$field})
                            <h2 class="h6 text-navy">{{ $label }}</h2>
                            <p class="text-break">{{ $program->{$field} }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
            @can('changeStatus', $program)
                @php
                    $transitions = match ($program->status) {
                        \App\Models\WorkProgram::STATUS_DRAFT => [
                            \App\Models\WorkProgram::STATUS_ACTIVE,
                            \App\Models\WorkProgram::STATUS_POSTPONED,
                        ],
                        \App\Models\WorkProgram::STATUS_ACTIVE => [
                            \App\Models\WorkProgram::STATUS_COMPLETED,
                            \App\Models\WorkProgram::STATUS_POSTPONED,
                        ],
                        \App\Models\WorkProgram::STATUS_POSTPONED => [
                            \App\Models\WorkProgram::STATUS_ACTIVE,
                            \App\Models\WorkProgram::STATUS_COMPLETED,
                        ],
                        default => [],
                    };
                @endphp
                @if ($transitions)
                    <hr class="my-4">
                    <form class="row g-2 align-items-end" method="POST" action="{{ route('work-programs.status', $program) }}">
                        @csrf
                        @method('PATCH')
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="status">Perbarui status</label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach ($transitions as $transition)
                                    <option value="{{ $transition }}">{{ \App\Models\WorkProgram::statusLabels()[$transition] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto"><button class="btn btn-app" type="submit">Simpan Status</button></div>
                    </form>
                @endif
            @endcan
            <hr class="my-4">
            <h2 class="h5 text-navy">Riwayat capaian</h2>
            @can('reportProgress', $program)
                <form class="row g-3 mb-4" method="POST" action="{{ route('work-programs.progress', $program) }}">
                    @csrf
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="actual">Capaian terbaru ({{ $program->target_unit }})</label>
                        <input class="form-control" id="actual" name="actual" type="number" min="0" step="0.01" required>
                    </div>
                    <div class="col-12 col-md-7">
                        <label class="form-label" for="progress-notes">Catatan kendala atau perkembangan</label>
                        <input class="form-control" id="progress-notes" name="notes" maxlength="5000">
                    </div>
                    <div class="col-12 col-md-2 d-grid align-items-end"><button class="btn btn-app mt-md-4" type="submit">Catat Capaian</button></div>
                    <div class="col-12 form-text">Nilai yang dicatat adalah capaian kumulatif terbaru, bukan tambahan. Persentase dihitung terhadap target program ini.</div>
                </form>
            @endcan
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Waktu</th><th>Capaian</th><th>Progres</th><th>Dicatat oleh</th><th>Catatan</th></tr></thead>
                    <tbody>
                    @forelse ($program->progressUpdates as $update)
                        <tr>
                            <td>{{ $update->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $update->actual }} {{ $program->target_unit }}</td>
                            <td>{{ $program->progressPercent($update) === null ? 'Belum ada data' : $program->progressPercent($update).'%' }}</td>
                            <td>{{ $update->reporter?->name ?? 'Pengguna tidak tersedia' }}</td>
                            <td>{{ $update->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-3">Belum ada pembaruan capaian.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
@endsection
