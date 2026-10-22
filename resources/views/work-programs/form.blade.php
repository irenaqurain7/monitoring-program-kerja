@extends('layouts.app')

@section('title', $isEdit ? 'Ubah Program Kerja' : 'Tambah Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4" style="max-width: 1080px;">
    <div class="mb-3">
        <a href="{{ $isEdit ? route('work-programs.show', $program) : route('work-programs.index') }}" class="text-decoration-none small fw-semibold" style="color: var(--corporate-blue);">
            &larr; Kembali
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Formulir Program Kerja</div>
            <h1 class="h2 text-navy fw-medium mb-1">{{ $isEdit ? 'Ubah Program Kerja' : 'Tambah Program Kerja' }}</h1>
            @if (! $isEdit)
                <p class="text-secondary mb-0" style="font-size: 0.92rem;">Program baru akan disimpan dengan status Rancangan sebelum diajukan untuk disetujui.</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('work-programs.update', $program) : route('work-programs.store') }}" class="card card-app">
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <div class="card-body p-4">
            @unless ($isEdit)
                <input type="hidden" name="campus_id" value="{{ $unit->campus_id }}">
                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
            @endunless

            @if ($errors->any())
                <div class="alert alert-danger small mb-4" role="alert">
                    <div class="fw-semibold mb-1">Periksa kembali isian berikut:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="name">Nama program <span class="text-danger">*</span></label>
                    <input class="form-control form-control-editorial @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $program->name) }}" maxlength="255" required placeholder="Contoh: Sosialisasi Penerimaan Mahasiswa Baru">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="description">Deskripsi</label>
                    <textarea class="form-control form-control-editorial @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Uraian ringkas latar belakang dan pelaksanaan program">{{ old('description', $program->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="objective">Tujuan</label>
                    <textarea class="form-control form-control-editorial @error('objective') is-invalid @enderror" id="objective" name="objective" rows="2" placeholder="Hasil akhir yang diharapkan">{{ old('objective', $program->objective) }}</textarea>
                    @error('objective') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="success_indicator">Indikator keberhasilan</label>
                    <textarea class="form-control form-control-editorial @error('success_indicator') is-invalid @enderror" id="success_indicator" name="success_indicator" rows="2" placeholder="Tolak ukur pencapaian kuantitatif / kualitatif">{{ old('success_indicator', $program->success_indicator) }}</textarea>
                    @error('success_indicator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-6 col-lg-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="target">Target <span class="text-danger">*</span></label>
                    <input class="form-control form-control-editorial @error('target') is-invalid @enderror" id="target" name="target" type="number" min="0" step="0.01" value="{{ old('target', $program->target) }}" required placeholder="Contoh: 500">
                    @error('target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-6 col-lg-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="target_unit">Satuan target <span class="text-danger">*</span></label>
                    <input class="form-control form-control-editorial @error('target_unit') is-invalid @enderror" id="target_unit" name="target_unit" value="{{ old('target_unit', $program->target_unit) }}" maxlength="50" required placeholder="Contoh: peserta / dokumen / kegiatan">
                    @error('target_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="year">Tahun program <span class="text-danger">*</span></label>
                    <input class="form-control form-control-editorial @error('year') is-invalid @enderror" id="year" name="year" type="number" min="2000" max="2100" value="{{ old('year', $program->year) }}" required>
                    @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-lg-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="responsible_user_id">Penanggung jawab <span class="text-danger">*</span></label>
                    <select class="form-select form-control-editorial @error('responsible_user_id') is-invalid @enderror" id="responsible_user_id" name="responsible_user_id" required>
                        <option value="">Pilih pengguna aktif</option>
                        @foreach ($responsibles as $responsible)
                            <option value="{{ $responsible->id }}" @selected((string) old('responsible_user_id', $program->responsible_user_id) === (string) $responsible->id)>
                                {{ $responsible->name }} ({{ $responsible->roleLabel() }})
                            </option>
                        @endforeach
                    </select>
                    @error('responsible_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-lg-4">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="team_id">Tim penanggung jawab</label>
                    <select class="form-select form-control-editorial @error('team_id') is-invalid @enderror" id="team_id" name="team_id">
                        <option value="">Seluruh unit</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected((string) old('team_id', $program->team_id) === (string) $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text" style="font-size: 0.74rem;">Opsional; tim harus berasal dari unit kerja program ini.</div>
                    @error('team_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="start_date">Tanggal mulai <span class="text-danger">*</span></label>
                    <input class="form-control form-control-editorial @error('start_date') is-invalid @enderror" id="start_date" name="start_date" type="date" value="{{ old('start_date', optional($program->start_date)->format('Y-m-d')) }}" required>
                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="due_date">Tenggat waktu <span class="text-danger">*</span></label>
                    <input class="form-control form-control-editorial @error('due_date') is-invalid @enderror" id="due_date" name="due_date" type="date" value="{{ old('due_date', optional($program->due_date)->format('Y-m-d')) }}" required>
                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Penempatan Kampus & Unit</label>
                    <div class="form-control form-control-editorial bg-light">{{ $unit->campus->name }} / {{ $unit->name }}</div>
                </div>

                <div class="col-12">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="notes">Catatan Tambahan</label>
                    <textarea class="form-control form-control-editorial @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Catatan kendala khusus atau instruksi pelaksanaan">{{ old('notes', $program->notes) }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-end gap-2">
            <a class="btn btn-outline-navy" href="{{ $isEdit ? route('work-programs.show', $program) : route('work-programs.index') }}">Batal</a>
            <button class="btn btn-app" type="submit">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Rancangan' }}</button>
        </div>
    </form>
</main>
@endsection
