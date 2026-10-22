@extends('layouts.app')

@section('content')
<main class="container py-4 py-md-5">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-danger mb-2">Dashboard</p>
        <h1 class="h2 text-navy fw-bold mb-1">{{ $heading }}</h1>
        <p class="text-secondary mb-0">{{ $description }}</p>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <section class="card card-app h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-secondary">Nama pengguna</h2>
                    <p class="h5 text-navy mb-0">{{ $user->name }}</p>
                </div>
            </section>
        </div>
        <div class="col-12 col-md-6">
            <section class="card card-app h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-secondary">Peran</h2>
                    <p class="h5 text-navy mb-0">{{ $user->roleLabel() }}</p>
                </div>
            </section>
        </div>
        @if ($assignedCampus)
            <div class="col-12 col-md-6">
                <section class="card card-app h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-secondary">Kampus</h2>
                        <p class="h5 text-navy mb-0">{{ $assignedCampus->name }}</p>
                    </div>
                </section>
            </div>
        @endif
        @if (! $user->isSystemAdmin() && $user->unit)
            <div class="col-12 col-md-6">
                <section class="card card-app h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-secondary">Unit</h2>
                        <p class="h5 text-navy mb-0">{{ $user->unit->name }}</p>
                    </div>
                </section>
            </div>
        @endif
    </div>

    <section class="card card-app mt-4">
        <div class="card-body p-4">
            <h2 class="h5 text-navy">Ringkasan</h2>
            <p class="text-secondary mb-0">@yield('summary')</p>
        </div>
    </section>
</main>
@endsection
