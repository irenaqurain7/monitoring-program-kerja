@extends('layouts.app')

@section('title', 'Kelola Unit')

@section('content')
<main class="container py-4 py-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h2 text-navy fw-bold mb-1">Kelola Unit</h1>
            <p class="text-secondary mb-0">Unit kampus {{ $campus->name }}. Unit yang dinonaktifkan tetap menyimpan riwayatnya.</p>
        </div>
    </div>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <section class="card card-app mb-4">
        <div class="card-body p-4">
            <h2 class="h5 text-navy">Tambah unit</h2>
            <form method="POST" action="{{ route('units.store') }}" class="row g-3">
                @csrf
                <div class="col-12 col-md-4">
                    <label class="form-label" for="name">Nama unit</label>
                    <input class="form-control" id="name" name="name" maxlength="255" value="{{ old('name') }}" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="description">Informasi</label>
                    <input class="form-control" id="description" name="description" maxlength="5000" value="{{ old('description') }}">
                </div>
                <div class="col-12 col-md-2 d-grid align-items-end">
                    <button class="btn btn-app mt-md-4" type="submit">Tambah Unit</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card card-app">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">Nama unit</th><th>Tim aktif/total</th><th>Program kerja</th><th>Berjalan</th><th>Selesai</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td class="ps-4"><a class="fw-semibold text-navy" href="{{ route('units.show', $unit) }}">{{ $unit->name }}</a></td>
                        <td>{{ $unit->active_teams_count }} / {{ $unit->teams_count }}</td>
                        <td>{{ $unit->work_programs_count }}</td>
                        <td>{{ $unit->active_programs_count }}</td>
                        <td>{{ $unit->completed_programs_count }}</td>
                        <td><span class="badge {{ $unit->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('units.show', $unit) }}">Detail</a>
                                <form method="POST" action="{{ route('units.status', $unit) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $unit->is_active ? '0' : '1' }}">
                                    <button class="btn btn-sm {{ $unit->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">{{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada unit.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($units->hasPages())<div class="card-body border-top">{{ $units->links() }}</div>@endif
    </section>
</main>
@endsection
