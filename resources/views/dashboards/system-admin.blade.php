@extends('dashboards.base', [
    'heading' => 'Administrasi Sistem',
    'description' => 'Ringkasan akses teknis akun Anda.',
])

@section('title', 'Dashboard Admin Sistem')
@section('summary')
    <section class="card card-app">
        <div class="card-body p-4">
            <h2 class="h5 text-navy mb-2">Informasi Akses</h2>
            <p class="text-secondary mb-0">Akun ini dikhususkan untuk pengelolaan teknis sistem dan tidak memiliki akses ke data organisasi.</p>
        </div>
    </section>
@endsection
