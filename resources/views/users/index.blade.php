@extends('layouts.app')

@section('title', 'Manajemen Pengguna | Monitoring Program Kerja')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Administrasi Pengguna</div>
            <h1 class="h2 text-navy fw-medium mb-1">Manajemen Pengguna</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">Kelola akun personel, status kepegawaian, dan penugasan unit kerja kampus.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-app">Tambah pengguna</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success small mb-3" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger small mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <!-- Filter Bar Card -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="eyebrow-luxury">Filter & Pencarian</div>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="search" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Cari nama atau username</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="form-control form-control-editorial" maxlength="100" placeholder="Ketik nama atau username">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-employment-type" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Status kepegawaian</label>
                    <select id="filter-employment-type" name="employment_type" class="form-select form-control-editorial">
                        <option value="">Semua</option>
                        @foreach (\App\Models\User::employmentTypeLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['employment_type'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-campus" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Kampus</label>
                    <select id="filter-campus" name="campus_id" class="form-select form-control-editorial">
                        <option value="">Semua kampus</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected((string) ($filters['campus_id'] ?? '') === (string) $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-unit-section" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Peran / Unit Bagian</label>
                    <select id="filter-unit-section" name="unit_section" class="form-select form-control-editorial">
                        <option value="">Semua</option>
                        @foreach (\App\Models\User::unitSectionLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['unit_section'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-status" class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Status akun</label>
                    <select id="filter-status" name="status" class="form-select form-control-editorial">
                        <option value="">Semua status</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 col-xl-1 d-flex align-items-end">
                    <button class="btn btn-app w-100" type="submit">Terapkan</button>
                </div>
                @if (count(array_filter($filters, fn ($value) => $value !== null && $value !== '')))
                    <div class="col-12">
                        <a href="{{ route('users.index') }}" class="small fw-semibold text-decoration-none" style="color: var(--corporate-blue);">Hapus filter</a>
                    </div>
                @endif
            </form>
        </div>
    </section>

    <!-- Users Table Card -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="eyebrow-luxury">Daftar Akun</div>
                <h2 class="h5 text-navy mb-0">Pengguna Sistem</h2>
            </div>
            <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600;">
                {{ $users->total() }} Akun
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status kepegawaian</th>
                        <th>Peran / Unit Bagian</th>
                        <th>Kampus</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        <tr>
                            @php($assignedCampus = $managedUser->role === 'campus_head' ? $managedUser->campus : $managedUser->unit?->campus)
                            <td class="ps-4 fw-semibold text-navy">{{ $managedUser->name }}</td>
                            <td><code>{{ $managedUser->username }}</code></td>
                            <td class="small">{{ $managedUser->email }}</td>
                            <td>
                                <span class="badge" style="background: rgba(11, 29, 58, 0.06); color: var(--text-main); font-weight: 500;">
                                    {{ $managedUser->employmentTypeLabel() }}
                                </span>
                            </td>
                            <td>{{ $managedUser->unitSectionLabel() }}</td>
                            <td class="small">{{ $managedUser->isSystemAdmin() ? '-' : ($assignedCampus?->name ?? '-') }}</td>
                            <td>
                                @if ($managedUser->isActive())
                                    <span class="badge" style="background: rgba(21, 128, 61, 0.1); color: var(--success); font-weight: 500;">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary" style="font-size: 0.68rem;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @can('update', $managedUser)
                                        <a href="{{ route('users.edit', $managedUser) }}" class="btn btn-sm btn-outline-navy">Ubah</a>
                                    @endcan
                                    @can('toggleStatus', $managedUser)
                                        <form method="POST" action="{{ route('users.status', $managedUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $managedUser->isActive() ? '0' : '1' }}">
                                            <button type="submit" class="btn btn-sm {{ $managedUser->isActive() ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                {{ $managedUser->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-5 text-center text-secondary">Tidak ada akun yang sesuai dengan pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer bg-white border-top p-3">{{ $users->links() }}</div>
        @endif
    </section>
</main>
@endsection
