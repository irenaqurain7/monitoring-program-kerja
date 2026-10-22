@extends('layouts.app')

@section('title', 'Kelola Unit | Monitoring Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Modul Organisasi</div>
            <h1 class="h2 text-navy fw-medium mb-1">Kelola Unit Bagian</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">
                Unit kerja kampus {{ $campus->name }}. Unit yang dinonaktifkan tetap menyimpan seluruh riwayat program dan koordinasi.
            </p>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success small mb-4" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger small mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Tambah Unit -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <div class="eyebrow-luxury">Registrasi Struktur</div>
            <h2 class="h5 text-navy mb-0">Tambah Unit Baru</h2>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('units.store') }}" class="row g-3">
                @csrf
                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="name">Nama unit</label>
                    <input class="form-control form-control-editorial @error('name') is-invalid @enderror" id="name" name="name" maxlength="255" value="{{ old('name') }}" placeholder="Contoh: Marketing Communication" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="description">Informasi / Lingkup Tanggung Jawab</label>
                    <input class="form-control form-control-editorial @error('description') is-invalid @enderror" id="description" name="description" maxlength="5000" value="{{ old('description') }}" placeholder="Deskripsi tugas dan fungsi utama unit">
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-2 d-grid align-items-end">
                    <button class="btn btn-app" type="submit">Tambah Unit</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Tabel Daftar Unit -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
            <div>
                <div class="eyebrow-luxury">Daftar Aktif</div>
                <h2 class="h5 text-navy mb-0">Unit Bagian Terdaftar</h2>
            </div>
            <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600;">
                {{ $units->total() }} Unit
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama unit</th>
                        <th>Tim aktif/total</th>
                        <th>Program kerja</th>
                        <th>Berjalan</th>
                        <th>Selesai</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td class="ps-4">
                            <a class="fw-semibold text-navy text-decoration-none" href="{{ route('units.show', $unit) }}">
                                {{ $unit->name }}
                            </a>
                            @if ($unit->description)
                                <div class="text-secondary small text-truncate" style="max-width: 280px; font-size: 0.76rem;">{{ $unit->description }}</div>
                            @endif
                        </td>
                        <td>{{ $unit->active_teams_count }} / {{ $unit->teams_count }}</td>
                        <td class="fw-medium">{{ $unit->work_programs_count }}</td>
                        <td>{{ $unit->active_programs_count }}</td>
                        <td style="color: var(--success); font-weight: 500;">{{ $unit->completed_programs_count }}</td>
                        <td>
                            @if ($unit->is_active)
                                <span class="badge" style="background: rgba(21, 128, 61, 0.1); color: var(--success); font-weight: 500;">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary" style="font-size: 0.68rem;">Nonaktif</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-navy" href="{{ route('units.show', $unit) }}">Detail</a>
                                <form method="POST" action="{{ route('units.status', $unit) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $unit->is_active ? '0' : '1' }}">
                                    <button class="btn btn-sm {{ $unit->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                                        {{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-5">Belum ada unit bagian terdaftar pada kampus ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($units->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $units->links() }}
            </div>
        @endif
    </section>
</main>
@endsection
