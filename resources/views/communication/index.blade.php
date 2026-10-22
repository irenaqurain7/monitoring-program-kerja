@extends('layouts.app')

@section('title', 'Kotak Masuk Aduan & Aspirasi | Two-Way Communication')

@section('content')
<main class="container-fluid px-3 px-md-4 py-3 py-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="eyebrow-luxury">Saluran Terpadu</div>
            <h1 class="h2 text-navy fw-medium mb-1">Two-Way Communication</h1>
            <p class="text-secondary mb-0" style="font-size: 0.92rem;">Saluran aspirasi, kendala pekerjaan, pengaduan fasilitas, dan koordinasi kampus.</p>
        </div>
        <a class="btn btn-app" href="{{ route('communication.create') }}">Buat Aduan / Aspirasi Baru</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Status Overview Metrics -->
    <div class="row g-3 mb-4">
        @foreach ($statuses as $statusKey => $statusLabel)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-editorial p-3">
                    <div class="stat-label text-truncate" title="{{ $statusLabel }}" style="font-size: 0.68rem;">{{ $statusLabel }}</div>
                    <div class="stat-number fs-4">{{ $statusSummary->get($statusKey, 0) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter Card -->
    <section class="card card-app mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="eyebrow-luxury">Pencarian & Filter</div>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('communication.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="search">Cari Nomor Tiket / Judul</label>
                    <input class="form-control form-control-editorial" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Contoh: TWC-2026-0001...">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="category">Kategori</label>
                    <select class="form-select form-control-editorial" id="category" name="category">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" @selected(($filters['category'] ?? '') === $catKey)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="priority">Prioritas</label>
                    <select class="form-select form-control-editorial" id="priority" name="priority">
                        <option value="">Semua</option>
                        @foreach ($priorities as $priKey => $priLabel)
                            <option value="{{ $priKey }}" @selected(($filters['priority'] ?? '') === $priKey)>{{ $priLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="status">Status</label>
                    <select class="form-select form-control-editorial" id="status" name="status">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $statKey => $statLabel)
                            <option value="{{ $statKey }}" @selected(($filters['status'] ?? '') === $statKey)>{{ $statLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <button class="btn btn-outline-navy" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Communication Topics Table -->
    <section class="card card-app">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="eyebrow-luxury">Kotak Masuk Aduan</div>
                <h2 class="h5 text-navy mb-0">Daftar Komunikasi Antarunit</h2>
            </div>
            <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); font-weight: 600;">
                {{ $topics->total() }} Topik
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-editorial align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No. Tiket</th>
                        <th>Judul Aspirasi / Kendala</th>
                        <th>Kategori</th>
                        <th>Prioritas</th>
                        <th>Sifat</th>
                        <th>Status</th>
                        <th>Tanggapan</th>
                        <th>Waktu</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topics as $topic)
                        @php
                            $priorityBadge = match($topic->priority) {
                                \App\Models\CommunicationTopic::PRIORITY_URGENT => 'background: rgba(223, 11, 43, 0.1); color: var(--red); border-color: rgba(223, 11, 43, 0.2);',
                                \App\Models\CommunicationTopic::PRIORITY_HIGH => 'background: rgba(183, 121, 31, 0.1); color: var(--warning); border-color: rgba(183, 121, 31, 0.2);',
                                \App\Models\CommunicationTopic::PRIORITY_MEDIUM => 'background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border-color: rgba(33, 74, 123, 0.2);',
                                default => 'background: rgba(100, 116, 139, 0.08); color: var(--text-muted); border-color: rgba(100, 116, 139, 0.2);',
                            };

                            $statusBadge = match($topic->status) {
                                \App\Models\CommunicationTopic::STATUS_RESOLVED => 'background: rgba(21, 128, 61, 0.1); color: var(--success); border-color: rgba(21, 128, 61, 0.2);',
                                \App\Models\CommunicationTopic::STATUS_IN_PROGRESS => 'background: rgba(33, 74, 123, 0.08); color: var(--corporate-blue); border-color: rgba(33, 74, 123, 0.2);',
                                \App\Models\CommunicationTopic::STATUS_UNDER_REVIEW => 'background: rgba(183, 121, 31, 0.1); color: var(--warning); border-color: rgba(183, 121, 31, 0.2);',
                                \App\Models\CommunicationTopic::STATUS_NEW => 'background: rgba(223, 11, 43, 0.1); color: var(--red); border-color: rgba(223, 11, 43, 0.2);',
                                default => 'background: rgba(100, 116, 139, 0.08); color: var(--text-muted); border-color: rgba(100, 116, 139, 0.2);',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 fw-semibold text-navy">
                                <code>{{ $topic->ticket_number }}</code>
                            </td>
                            <td>
                                <a href="{{ route('communication.show', $topic) }}" class="fw-semibold text-navy text-decoration-none">
                                    {{ $topic->title }}
                                </a>
                                <div class="small text-secondary" style="font-size: 0.74rem;">Oleh: {{ $topic->creator->name }} @if($topic->unit) &bull; {{ $topic->unit->name }} @endif</div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(11, 29, 58, 0.05); color: var(--navy); border: 1px solid rgba(11, 29, 58, 0.1); font-weight: 500;">
                                    {{ $topic->categoryLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="{{ $priorityBadge }} border: 1px solid; font-weight: 500;">
                                    {{ $topic->priorityLabel() }}
                                </span>
                            </td>
                            <td>
                                @if ($topic->privacy === \App\Models\CommunicationTopic::PRIVACY_PRIVATE_CAMPUS_HEAD)
                                    <span class="badge" style="background: var(--midnight-navy); color: #ffffff;">Privat</span>
                                @else
                                    <span class="badge" style="background: rgba(100, 116, 139, 0.08); color: var(--text-secondary); border: 1px solid var(--border-subtle);">Terbuka Unit</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="{{ $statusBadge }} border: 1px solid; font-weight: 500;">
                                    {{ $topic->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(33, 74, 123, 0.06); color: var(--corporate-blue); border: 1px solid rgba(33, 74, 123, 0.15);">
                                    {{ $topic->messages_count }} balasan
                                </span>
                            </td>
                            <td class="small text-secondary">{{ $topic->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                <a class="btn btn-sm btn-outline-navy" href="{{ route('communication.show', $topic) }}">Buka Percakapan</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-5">
                                Belum ada topik aspirasi atau aduan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($topics->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $topics->links() }}
            </div>
        @endif
    </section>
</main>
@endsection
