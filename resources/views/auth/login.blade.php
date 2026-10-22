@extends('layouts.app')

@section('title', 'Masuk | Monitoring Program Kerja')

@section('content')
<main class="login-shell">
    <section class="card card-app w-100" style="max-width: 460px">
        <div class="card-body p-4 p-md-5">
            <p class="text-uppercase small fw-semibold text-danger mb-2">Akses pengguna</p>
            <h1 class="h3 text-navy fw-bold mb-2">Masuk ke aplikasi</h1>
            <p class="text-secondary mb-4">Masuk menggunakan nama pengguna, kata sandi, dan kampus penugasan.</p>

            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label">Nama Pengguna</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}"
                           class="form-control @error('username') is-invalid @enderror"
                           autocomplete="username" maxlength="50" required autofocus>
                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Kata sandi</label>
                    <input id="password" name="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           autocomplete="current-password" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label for="campus_id" class="form-label">Kampus</label>
                    <select id="campus_id" name="campus_id"
                            class="form-select @error('campus_id') is-invalid @enderror" required>
                        <option value="">Pilih kampus</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id') === (string) $campus->id)>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                        <option value="system_admin" @selected(old('campus_id') === 'system_admin')>
                            Admin Sistem (tanpa akses kampus)
                        </option>
                    </select>
                    @error('campus_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button class="btn btn-app w-100 py-2 fw-semibold" type="submit">Masuk</button>
            </form>
        </div>
    </section>
</main>
@endsection
