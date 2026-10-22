@extends('layouts.app')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Dasbor Eksekutif</div>
            <h1 class="h2 text-navy fw-medium mb-1">{{ $heading }}</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">{{ $description }}</p>
        </div>
    </div>

    <section class="card card-app mb-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <h2 class="h6 text-secondary small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.06em;">Nama pengguna</h2>
                    <p class="h6 text-navy fw-semibold mb-0">{{ $user->name }}</p>
                </div>
                <div class="col-6 col-md-3 border-start">
                    <h2 class="h6 text-secondary small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.06em;">Peran</h2>
                    <p class="h6 text-navy fw-semibold mb-0">{{ $user->roleLabel() }}</p>
                </div>
                @if ($assignedCampus)
                    <div class="col-6 col-md-3 border-start">
                        <h2 class="h6 text-secondary small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.06em;">Kampus</h2>
                        <p class="h6 text-navy fw-semibold mb-0">{{ $assignedCampus->name }}</p>
                    </div>
                @endif
                @if (! $user->isSystemAdmin() && $user->unit)
                    <div class="col-6 col-md-3 border-start">
                        <h2 class="h6 text-secondary small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.06em;">Unit</h2>
                        <p class="h6 text-navy fw-semibold mb-0">{{ $user->unit->name }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mt-3">
        @yield('summary')
    </div>
</main>
@endsection
