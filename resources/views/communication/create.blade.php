@extends('layouts.app')

@section('title', 'Buat Aduan atau Aspirasi Baru | Two-Way Communication')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4" style="max-width: 960px;">
    <div class="mb-3">
        <a href="{{ route('communication.index') }}" class="text-decoration-none small fw-semibold" style="color: var(--corporate-blue);">
            &larr; Kembali ke Kotak Masuk
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Formulir Koordinasi</div>
            <h1 class="h2 text-navy fw-medium mb-1">Buat Topik Aspirasi / Pengaduan Baru</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">Sampaikan aspirasi, kendala pekerjaan, pengaduan fasilitas, atau kebutuhan koordinasi kampus secara terstruktur.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger small mb-4" role="alert">
            <div class="fw-semibold mb-1">Periksa kembali data isian berikut:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card card-app">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('communication.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Judul Aspirasi / Kendala <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-editorial @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Keterlambatan pengiriman perangkat laboratorium..." maxlength="255" required autofocus>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label for="category" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select form-control-editorial @error('category') is-invalid @enderror" id="category" name="category" required>
                            <option value="">Pilih Kategori...</option>
                            @foreach ($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}" @selected(old('category') === $catKey)>{{ $catLabel }}</option>
                            @endforeach
                        </select>
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="priority" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tingkat Prioritas <span class="text-danger">*</span></label>
                        <select class="form-select form-control-editorial @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            @foreach ($priorities as $priKey => $priLabel)
                                <option value="{{ $priKey }}" @selected(old('priority', 'medium') === $priKey)>{{ $priLabel }}</option>
                            @endforeach
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="privacy" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tingkat Privasi <span class="text-danger">*</span></label>
                        <select class="form-select form-control-editorial @error('privacy') is-invalid @enderror" id="privacy" name="privacy" required>
                            @foreach ($privacies as $privKey => $privLabel)
                                <option value="{{ $privKey }}" @selected(old('privacy', 'public_unit') === $privKey)>{{ $privLabel }}</option>
                            @endforeach
                        </select>
                        @error('privacy') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Uraian Pesan / Masalah <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-editorial @error('description') is-invalid @enderror" id="description" name="description" rows="6" placeholder="Jelaskan secara rinci apa yang menjadi kendala atau usulan koordinasi Anda..." required>{{ old('description') }}</textarea>
                    <div class="form-text text-secondary mt-1" style="font-size: 0.74rem;">Aspirasi privat hanya dapat diakses oleh Anda dan pihak yang berwenang (Kepala Kampus).</div>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('communication.index') }}" class="btn btn-outline-navy">Batal</a>
                    <button type="submit" class="btn btn-app px-4">Kirim Aspirasi / Aduan</button>
                </div>
            </form>
        </div>
    </section>
</main>
@endsection
