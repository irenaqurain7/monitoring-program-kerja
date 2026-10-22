@extends('layouts.app')

@section('title', $isEdit ? 'Ubah Program Kerja' : 'Tambah Program Kerja')

@section('content')
<main class="container py-4">
    <div class="mb-4">
        <a href="{{ $isEdit ? route('work-programs.show', $program) : route('work-programs.index') }}" class="text-decoration-none">&larr; Kembali</a>
        <h1 class="h3 text-navy mt-2">{{ $isEdit ? 'Ubah Program Kerja' : 'Tambah Program Kerja' }}</h1>
        @if (! $isEdit)
            <p class="text-secondary mb-0">Program baru akan disimpan dengan status Rancangan.</p>
        @endif
    </div>

    <form method="POST" action="{{ $isEdit ? route('work-programs.update', $program) : route('work-programs.store') }}" class="card card-app">
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <div class="card-body">
            @unless ($isEdit)
                <input type="hidden" name="campus_id" value="{{ $unit->campus_id }}">
                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
            @endunless
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold mb-1">Periksa kembali isian berikut:</div>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="name">Nama program <span class="text-danger">*</span></label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $program->name) }}" maxlength="255" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $program->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="objective">Tujuan</label>
                    <textarea class="form-control" id="objective" name="objective" rows="3">{{ old('objective', $program->objective) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="success_indicator">Indikator keberhasilan</label>
                    <textarea class="form-control" id="success_indicator" name="success_indicator" rows="2">{{ old('success_indicator', $program->success_indicator) }}</textarea>
                </div>
                <div class="col-6 col-lg-3">
                    <label class="form-label" for="target">Target <span class="text-danger">*</span></label>
                    <input class="form-control" id="target" name="target" type="number" min="0" step="0.01" value="{{ old('target', $program->target) }}" required>
                </div>
                <div class="col-6 col-lg-3">
                    <label class="form-label" for="target_unit">Satuan target <span class="text-danger">*</span></label>
                    <input class="form-control" id="target_unit" name="target_unit" value="{{ old('target_unit', $program->target_unit) }}" maxlength="50" required>
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label" for="year">Tahun program <span class="text-danger">*</span></label>
                    <input class="form-control" id="year" name="year" type="number" min="2000" max="2100" value="{{ old('year', $program->year) }}" required>
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label" for="responsible_user_id">Penanggung jawab <span class="text-danger">*</span></label>
                    <select class="form-select" id="responsible_user_id" name="responsible_user_id" required>
                        <option value="">Pilih pengguna aktif</option>
                        @foreach ($responsibles as $responsible)
                            <option value="{{ $responsible->id }}" @selected((string) old('responsible_user_id', $program->responsible_user_id) === (string) $responsible->id)>
                                {{ $responsible->name }} — {{ $responsible->roleLabel() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <label class="form-label" for="team_id">Tim penanggung jawab</label>
                    <select class="form-select" id="team_id" name="team_id">
                        <option value="">Seluruh unit</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected((string) old('team_id', $program->team_id) === (string) $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Opsional; tim harus berasal dari unit program ini.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="start_date">Tanggal mulai <span class="text-danger">*</span></label>
                    <input class="form-control" id="start_date" name="start_date" type="date" value="{{ old('start_date', optional($program->start_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="due_date">Tenggat waktu <span class="text-danger">*</span></label>
                    <input class="form-control" id="due_date" name="due_date" type="date" value="{{ old('due_date', optional($program->due_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-12">
                    <div class="form-label">Penempatan</div>
                    <div class="form-control bg-light">{{ $unit->campus->name }} — {{ $unit->name }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="notes">Catatan</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $program->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="{{ $isEdit ? route('work-programs.show', $program) : route('work-programs.index') }}">Batal</a>
            <button class="btn btn-app" type="submit">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Rancangan' }}</button>
        </div>
    </form>
</main>
@endsection
