@extends('layouts.app')

@section('title', ($managedUser->exists ? 'Ubah' : 'Tambah') . ' Pengguna')

@section('content')
<main class="container py-4 py-md-5">
    <div class="mb-4">
        <a href="{{ route('users.index') }}" class="link-secondary text-decoration-none">&larr; Kembali ke daftar pengguna</a>
        <h1 class="h2 text-navy fw-bold mt-3 mb-1">{{ $managedUser->exists ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
        <p class="text-secondary mb-0">Lengkapi data akun dan penempatannya.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <p class="fw-semibold mb-1">Periksa kembali data berikut:</p>
            <ul class="mb-0">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="card card-app">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ $managedUser->exists ? route('users.update', $managedUser) : route('users.store') }}">
                @csrf
                @if ($managedUser->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input id="name" name="name" value="{{ old('name', $managedUser->name) }}"
                               class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" name="username" value="{{ old('username', $managedUser->username) }}"
                               class="form-control @error('username') is-invalid @enderror" maxlength="50"
                               pattern="[a-z0-9._-]+" autocomplete="username" required>
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}"
                               class="form-control @error('email') is-invalid @enderror" maxlength="255" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="employment_type" class="form-label">Status kepegawaian</label>
                        <select id="employment_type" name="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                            <option value="">Pilih status kepegawaian</option>
                            @foreach ($employmentTypeLabels as $key => $label)
                                <option value="{{ $key }}" @selected(old('employment_type', $managedUser->employment_type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('employment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Pilih Staff atau Magang.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="unit_section" class="form-label">Peran / Unit Bagian</label>
                        @if ($isSystemAdminTarget)
                            <input type="text" class="form-control" value="Admin Sistem" disabled>
                            <div class="form-text">Akun Admin Sistem tidak memiliki penempatan organisasi.</div>
                        @elseif ($isSelf)
                            <input type="text" class="form-control" value="{{ $managedUser->unitSectionLabel() }}" disabled>
                            <input type="hidden" name="unit_section" value="{{ $managedUser->unit_section }}">
                            <div class="form-text">Unit bagian akun sendiri tidak dapat diubah.</div>
                        @else
                            <select id="unit_section" name="unit_section" class="form-select @error('unit_section') is-invalid @enderror" required>
                                <option value="">Pilih peran / unit bagian</option>
                                @foreach ($unitSectionLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(old('unit_section', $managedUser->unit_section) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('unit_section') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Kepala Kampus menjadi peran Kepala Kampus. Pilihan lainnya menjadi Anggota Unit pada bagian yang dipilih.</div>
                        @endif
                    </div>

                    <div class="col-12 col-md-6" id="campus-group">
                        <label for="campus_id" class="form-label">Kampus</label>
                        @if ($isSystemAdminTarget)
                            <input type="text" class="form-control" value="Tidak ada penempatan kampus" disabled>
                        @elseif (auth()->user()->role !== 'system_admin' || $isSelf)
                            @php($fixedCampusId = $isSelf ? $managedUser->campus_id : auth()->user()->assignedCampusId())
                            <select id="campus_id" class="form-select" disabled>
                                <option value="">Pilih kampus</option>
                                @foreach ($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) $fixedCampusId === (string) $campus->id)>{{ $campus->name }}</option>
                                @endforeach
                            </select>
                            @unless ($isSelf)
                                <input type="hidden" name="campus_id" value="{{ $fixedCampusId }}">
                            @else
                                <input type="hidden" name="campus_id" value="{{ $managedUser->campus_id }}">
                                <div class="form-text">Penempatan akun sendiri tidak dapat diubah.</div>
                            @endunless
                        @else
                            <select id="campus_id" name="campus_id" class="form-select @error('campus_id') is-invalid @enderror">
                                <option value="">Pilih kampus</option>
                                @foreach ($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) old('campus_id', $managedUser->campus_id) === (string) $campus->id)>{{ $campus->name }}</option>
                                @endforeach
                            </select>
                            @error('campus_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="password" class="form-label">Kata sandi {{ $managedUser->exists ? '(opsional)' : '' }}</label>
                        <input id="password" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" minlength="6" {{ $managedUser->exists ? '' : 'required' }}>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if ($managedUser->exists)<div class="form-text">Kosongkan jika tidak ingin mengganti kata sandi.</div>@endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="password_confirmation" class="form-label">Konfirmasi kata sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                               class="form-control" autocomplete="new-password" minlength="6">
                    </div>
                    @if ($managedUser->exists)
                        <div class="col-12">
                            <span class="badge {{ $managedUser->isActive() ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $managedUser->isActive() ? 'Akun aktif' : 'Akun nonaktif' }}
                            </span>
                        </div>
                    @endif
                    <div class="col-12 d-flex flex-column flex-sm-row gap-2 mt-4">
                        <button class="btn btn-app px-4" type="submit">Simpan</button>
                        <a class="btn btn-outline-secondary px-4" href="{{ route('users.index') }}">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</main>
@endsection
