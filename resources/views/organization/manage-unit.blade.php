@extends('layouts.app')

@section('title', 'Pengelolaan Unit ' . $unit->name)

@section('content')
<main class="container py-4 py-md-5">
    <section class="card card-app">
        <div class="card-body p-4">
            <p class="text-uppercase small fw-semibold text-danger mb-2">Area pengelola</p>
            <h1 class="h3 text-navy">{{ $unit->name }}</h1>
            <p class="text-secondary mb-0">Halaman ini dilindungi otorisasi. Fitur pengelolaan unit belum dibangun pada tahap ini.</p>
        </div>
    </section>
</main>
@endsection
