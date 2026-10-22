@extends('layouts.app')

@section('title', $topic->ticket_number . ' - ' . $topic->title . ' | Two-Way Communication')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="mb-3">
        <a href="{{ route('communication.index') }}" class="text-decoration-none small fw-semibold" style="color: var(--corporate-blue);">
            &larr; Kembali ke Kotak Masuk
        </a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">{{ $topic->ticket_number }} &bull; {{ $topic->categoryLabel() }}</div>
            <h1 class="h2 text-navy fw-medium mb-0" style="font-family: var(--font-serif);">{{ $topic->title }}</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $statusBadge = match($topic->status) {
                    \App\Models\CommunicationTopic::STATUS_RESOLVED => 'background: rgba(21, 128, 61, 0.1); color: var(--success); border-color: rgba(21, 128, 61, 0.2);',
                    \App\Models\CommunicationTopic::STATUS_IN_PROGRESS => 'background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border-color: rgba(33, 74, 123, 0.2);',
                    \App\Models\CommunicationTopic::STATUS_UNDER_REVIEW => 'background: rgba(183, 121, 31, 0.1); color: var(--warning); border-color: rgba(183, 121, 31, 0.2);',
                    \App\Models\CommunicationTopic::STATUS_NEW => 'background: rgba(223, 11, 43, 0.1); color: var(--red); border-color: rgba(223, 11, 43, 0.2);',
                    default => 'background: rgba(100, 116, 139, 0.08); color: var(--text-muted); border-color: rgba(100, 116, 139, 0.2);',
                };
            @endphp
            <span class="badge px-3 py-2" style="{{ $statusBadge }} border: 1px solid; font-weight: 600; font-size: 0.82rem;">
                {{ $topic->statusLabel() }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger small mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <!-- Initial Issue Description Card -->
            <section class="card card-app mb-4">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold text-navy">{{ $topic->creator->name }}</span>
                        <span class="small text-secondary">&bull; {{ $topic->creator->unitSectionLabel() }}</span>
                    </div>
                    <span class="small text-secondary">{{ $topic->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0" style="white-space: pre-line; line-height: 1.6; color: var(--text-main);">{{ $topic->description }}</p>
                </div>
            </section>

            <!-- Conversation Thread -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="eyebrow-luxury mb-0">Riwayat Dialog</div>
                <span class="small text-secondary">{{ $topic->messages->count() }} Balasan</span>
            </div>

            @forelse ($topic->messages as $msg)
                <div class="card card-app mb-3 {{ $msg->user_id === auth()->id() ? 'border-primary' : '' }}" style="border-radius: 8px;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-semibold text-navy">{{ $msg->user->name }}</span>
                                <span class="badge ms-1" style="background: rgba(11, 29, 58, 0.06); color: var(--text-main); font-size: 0.68rem;">{{ $msg->user->roleLabel() }}</span>
                            </div>
                            <span class="small text-secondary" style="font-size: 0.74rem;">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="mb-0 text-dark" style="white-space: pre-line; font-size: 0.88rem; line-height: 1.6;">{{ $msg->message }}</p>
                    </div>
                </div>
            @empty
                <div class="p-4 bg-white rounded-2 border text-center text-secondary mb-4 small" style="border-color: var(--border-subtle) !important;">
                    Belum ada tanggapan untuk aduan ini. Tulis balasan atau tindak lanjut pada formulir di bawah.
                </div>
            @endforelse

            <!-- Reply Form -->
            @if ($topic->status !== \App\Models\CommunicationTopic::STATUS_CLOSED)
                <section class="card card-app">
                    <div class="card-header bg-white border-bottom p-3 p-md-4">
                        <div class="eyebrow-luxury">Tanggapan</div>
                        <h2 class="h6 text-navy mb-0">Kirim Tanggapan Baru</h2>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <form method="POST" action="{{ route('communication.message', $topic) }}">
                            @csrf
                            <div class="mb-3">
                                <textarea class="form-control form-control-editorial" name="message" rows="4" placeholder="Tulis tanggapan atau informasi tambahan secara profesional..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-app px-4">Kirim Balasan</button>
                            </div>
                        </form>
                    </div>
                </section>
            @else
                <div class="alert alert-secondary small">
                    Topik aduan ini telah ditutup. Tidak dapat mengirimkan tanggapan tambahan.
                </div>
            @endif
        </div>

        <!-- Sidebar Details & Action Panel -->
        <div class="col-12 col-lg-4">
            <section class="card card-app mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <div class="eyebrow-luxury">Informasi Tiket</div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <dl class="row mb-0 small gy-2">
                        <dt class="col-5 text-secondary">Prioritas</dt>
                        <dd class="col-7 mb-1">
                            <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border: 1px solid rgba(33, 74, 123, 0.2);">
                                {{ $topic->priorityLabel() }}
                            </span>
                        </dd>

                        <dt class="col-5 text-secondary">Sifat Privasi</dt>
                        <dd class="col-7 mb-1">{{ $topic->privacyLabel() }}</dd>

                        <dt class="col-5 text-secondary">Unit Pelapor</dt>
                        <dd class="col-7 mb-1">{{ $topic->unit?->name ?? 'Lintas Unit' }}</dd>

                        <dt class="col-5 text-secondary">PIC Terpilih</dt>
                        <dd class="col-7 mb-0 fw-semibold text-navy">{{ $topic->assignedTo?->name ?? 'Belum Ditugaskan' }}</dd>
                    </dl>
                </div>
            </section>

            <!-- Status & Action Update Panel -->
            @if (auth()->user()->role === \App\Models\User::ROLE_CAMPUS_HEAD || $topic->creator_id === auth()->id())
                <section class="card card-app mb-4">
                    <div class="card-header bg-white border-bottom p-3">
                        <div class="eyebrow-luxury">Tindak Lanjut</div>
                        <h2 class="h6 text-navy mb-0">Pembaruan Status</h2>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <form method="POST" action="{{ route('communication.status', $topic) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="status">Ubah Status</label>
                                <select class="form-select form-control-editorial form-select-sm" id="status" name="status" required>
                                    @foreach ($statuses as $statKey => $statLabel)
                                        <option value="{{ $statKey }}" @selected($topic->status === $statKey)>{{ $statLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if (auth()->user()->role === \App\Models\User::ROLE_CAMPUS_HEAD)
                                <div class="mb-3">
                                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="assigned_to_user_id">Tugaskan PIC Tindak Lanjut</label>
                                    <select class="form-select form-control-editorial form-select-sm" id="assigned_to_user_id" name="assigned_to_user_id">
                                        <option value="">Belum Ditugaskan</option>
                                        @foreach ($campusUsers as $cUser)
                                            <option value="{{ $cUser->id }}" @selected($topic->assigned_to_user_id === $cUser->id)>{{ $cUser->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="action_note">Catatan Tindak Lanjut</label>
                                <textarea class="form-control form-control-editorial form-control-sm" id="action_note" name="action_note" rows="2" placeholder="Keterangan langkah yang diambil..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-sm btn-outline-navy w-100">Simpan Perubahan</button>
                        </form>
                    </div>
                </section>
            @endif

            <!-- Status Transition History -->
            <section class="card card-app">
                <div class="card-header bg-white border-bottom p-3">
                    <div class="eyebrow-luxury">Audit Trail</div>
                    <h2 class="h6 text-navy mb-0">Riwayat Status</h2>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="small">
                        @forelse ($topic->statusLogs as $log)
                            <div class="border-bottom pb-2 mb-2" style="border-color: var(--border-subtle) !important;">
                                <div class="d-flex justify-content-between text-secondary" style="font-size: 0.72rem;">
                                    <span class="fw-semibold text-navy">{{ $log->user->name }}</span>
                                    <span>{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="fw-semibold mt-1" style="color: var(--corporate-blue);">
                                    {{ $log->toStatusLabel() }}
                                </div>
                                @if ($log->action_note)
                                    <div class="text-secondary mt-1" style="font-size: 0.76rem;">{{ $log->action_note }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-secondary text-center py-2">Belum ada riwayat perubahan status.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
@endsection
