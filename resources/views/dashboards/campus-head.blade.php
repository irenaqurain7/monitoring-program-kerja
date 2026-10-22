@extends('dashboards.base', [
    'heading' => 'Dasbor Pemantauan Kampus',
    'description' => 'Pantau unit, tim, program kerja, dan capaian di kampus Anda.',
])

@section('title', 'Dashboard Kepala Kampus')
@section('summary')
    <div class="row g-3 mb-4">
        @foreach ([
            'Unit aktif' => $summary['active_units'],
            'Tim aktif' => $summary['active_teams'],
            'Program kerja' => $summary['programs'],
            'Program selesai' => $summary['completed'],
            'Program berjalan' => $summary['running'],
            'Program terlambat' => $summary['overdue'],
            'Program berjalan tanpa pembaruan progres' => $summary['without_update'],
        ] as $label => $value)
            <div class="col-6 col-lg-3">
                <div class="border rounded-3 bg-white p-3 h-100">
                    <div class="small text-secondary">{{ $label }}</div>
                    <div class="fs-4 fw-semibold text-navy">{{ $value }}</div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="alert alert-light border">
        Target memiliki satuan yang berbeda, sehingga tidak dijumlahkan lintas program. Persentase rata-rata dihitung dari program dengan target positif dan pembaruan capaian.
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h5 text-navy mb-0">Pemantauan unit — {{ $assignedCampus->name }}</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-app" href="{{ route('work-programs.index') }}">Program Kerja</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('units.index') }}">Kelola Unit</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Unit</th>
                    <th>Tim</th>
                    <th>Program</th>
                    <th>Rata-rata progres</th>
                    <th>Selesai</th>
                    <th>Berjalan</th>
                    <th>Terlambat</th>
                    <th>Pemantauan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    @php
                        $percentages = $unit->workPrograms
                            ->map(fn ($program) => $program->progressPercent())
                            ->filter(fn ($percentage) => $percentage !== null);
                        $average = $percentages->isEmpty() ? null : round($percentages->avg(), 1);
                        $completed = $unit->workPrograms->where('status', \App\Models\WorkProgram::STATUS_COMPLETED)->count();
                        $running = $unit->workPrograms->where('status', \App\Models\WorkProgram::STATUS_ACTIVE);
                        $overdue = $running->filter(fn ($program) => $program->due_date->lt(now()->startOfDay()))->count();
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $unit->name }}</div>
                            @unless ($unit->is_active)<span class="badge text-bg-secondary">Nonaktif</span>@endunless
                        </td>
                        <td>{{ $unit->active_teams_count }} aktif / {{ $unit->teams_count }} total</td>
                        <td>{{ $unit->work_programs_count }}</td>
                        <td>{{ $average === null ? 'Belum ada data' : $average.'%' }}</td>
                        <td>{{ $completed }}</td>
                        <td>{{ $running->count() }}</td>
                        <td>{{ $overdue }}</td>
                        <td>{{ $unit->programs_without_update_count > 0 ? 'Perlu pembaruan' : 'Terpantau' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('units.show', $unit) }}">Lihat Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-secondary py-4">Belum ada unit pada kampus ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
