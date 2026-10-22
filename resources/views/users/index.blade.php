@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<main class="container py-4 py-md-5">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-danger mb-2">Administrasi</p>
            <h1 class="h2 text-navy fw-bold mb-1">Manajemen Pengguna</h1>
            <p class="text-secondary mb-0">Kelola akun sesuai cakupan kewenangan Anda.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-app">Tambah pengguna</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="card card-app mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="search" class="form-label">Cari nama atau username</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="form-control" maxlength="100" placeholder="Ketik nama atau username">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-employment-type" class="form-label">Status kepegawaian</label>
                    <select id="filter-employment-type" name="employment_type" class="form-select">
                        <option value="">Semua</option>
                        @foreach (\App\Models\User::employmentTypeLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['employment_type'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-campus" class="form-label">Kampus</label>
                    <select id="filter-campus" name="campus_id" class="form-select">
                        <option value="">Semua kampus</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected((string) ($filters['campus_id'] ?? '') === (string) $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-unit-section" class="form-label">Peran / Unit Bagian</label>
                    <select id="filter-unit-section" name="unit_section" class="form-select">
                        <option value="">Semua</option>
                        @foreach (\App\Models\User::unitSectionLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['unit_section'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label for="filter-status" class="form-label">Status akun</label>
                    <select id="filter-status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 col-xl-1 d-flex align-items-end gap-2">
                    <button class="btn btn-app flex-grow-1" type="submit">Terapkan</button>
                </div>
                @if (count(array_filter($filters, fn ($value) => $value !== null && $value !== '')))
                    <div class="col-12">
                        <a href="{{ route('users.index') }}" class="link-secondary">Hapus filter</a>
                    </div>
                @endif
            </form>
        </div>
    </section>

    <section class="card card-app">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status kepegawaian</th>
                        <th>Peran / Unit Bagian</th>
                        <th>Kampus</th>
                        <th>Status</th>
                        <th class="pe-4">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        <tr>
                            @php($assignedCampus = $managedUser->role === 'campus_head' ? $managedUser->campus : $managedUser->unit?->campus)
                            <td class="ps-4 fw-semibold">{{ $managedUser->name }}</td>
                            <td>{{ $managedUser->username }}</td>
                            <td>{{ $managedUser->email }}</td>
                            <td>{{ $managedUser->employmentTypeLabel() }}</td>
                            <td>{{ $managedUser->unitSectionLabel() }}</td>
                            <td>{{ $managedUser->isSystemAdmin() ? '—' : ($assignedCampus?->name ?? '—') }}</td>
                            <td>
                                <span class="badge {{ $managedUser->isActive() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $managedUser->isActive() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex flex-wrap gap-2">
                                    @can('update', $managedUser)
                                        <a href="{{ route('users.edit', $managedUser) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
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
            <div class="card-body border-top">{{ $users->links() }}</div>
        @endif
    </section>
</main>
@endsection
