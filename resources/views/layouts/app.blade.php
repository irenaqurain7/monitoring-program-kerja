<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Monitoring Program Kerja')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400..500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --midnight-navy: #0B1D3A;
            --deep-navy: #10264A;
            --corporate-blue: #214A7B;
            --corporate-blue-hover: #193a61;
            --navy: #0B1D3A;
            --navy-dark: #071326;
            --navy-light: #10264A;
            --navy-sidebar: #0B1D3A;
            --ubsi-red: #DF0B2B;
            --red: #DF0B2B;
            --red-hover: #b80923;
            --red-soft: rgba(223, 11, 43, 0.08);
            --paper: #F3F5F8;
            --surface: #FFFFFF;
            --slate: #475569;
            --text-main: #1E293B;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --border-gray: #E2E8F0;
            --border-subtle: #E2E8F0;
            --border-light: rgba(11, 29, 58, 0.05);
            --success: #15803D;
            --warning: #B7791F;
            --error: #C6283D;
            --focus-ring: rgba(33, 74, 123, 0.25);
            --sidebar-width: 260px;
            
            --font-serif: 'Newsreader', Garamond, Georgia, serif;
            --font-sans: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background: var(--paper);
            color: var(--text-main);
            min-height: 100vh;
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
        }

        /* Typography */
        h1, .h1, .page-title {
            font-family: var(--font-serif);
            font-weight: 500;
            letter-spacing: -0.02em;
            color: var(--navy);
        }

        h2, .h2 {
            font-family: var(--font-serif);
            font-weight: 500;
            letter-spacing: -0.015em;
            color: var(--navy);
        }

        h3, .h3 {
            font-family: var(--font-serif);
            font-weight: 500;
            letter-spacing: -0.01em;
            color: var(--navy);
        }

        .eyebrow-luxury {
            font-family: var(--font-sans);
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--red);
            margin-bottom: 0.35rem;
        }

        /* Top Header */
        .header-top {
            background: var(--surface);
            border-bottom: 1px solid var(--border-subtle);
            height: 62px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .brand-mark {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-crest {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: var(--navy);
            display: grid;
            place-items: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(15, 31, 46, 0.12);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .brand-title {
            font-family: var(--font-serif);
            font-size: 1.125rem;
            font-weight: 500;
            letter-spacing: -0.01em;
            color: var(--navy);
        }

        .brand-sub {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            font-weight: 600;
        }

        .accent-hairline {
            height: 2px;
            background: linear-gradient(90deg, var(--red) 0%, rgba(158, 42, 43, 0.4) 60%, transparent 100%);
        }

        /* App Layout Shell */
        .app-shell {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        /* Sidebar Navigation */
        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--navy-sidebar);
            color: #d9e2ec;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.04);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sidebar-nav {
            padding: 1.25rem 0.85rem;
            flex: 1;
            overflow-y: auto;
        }

        .nav-group-title {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #7188a1;
            font-weight: 600;
            padding: 0.75rem 0.75rem 0.35rem;
            margin-top: 0.65rem;
        }

        .nav-group-title:first-child {
            margin-top: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.55rem 0.75rem;
            color: #9fb3c8;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.84rem;
            font-weight: 500;
            transition: all 0.16s ease;
            margin-bottom: 0.15rem;
            position: relative;
        }

        .sidebar-link:hover, .sidebar-link:focus-visible {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-weight: 600;
        }

        .sidebar-link.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 5px;
            bottom: 5px;
            width: 3px;
            background: var(--red);
            border-radius: 0 2px 2px 0;
        }

        /* Main Content Container */
        .app-content {
            flex: 1;
            min-width: 0;
            padding: 1.75rem 2rem;
            background: var(--paper);
        }

        /* Reusable Components */
        .card-app {
            background: var(--surface);
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(15, 31, 46, 0.02), 0 4px 12px rgba(15, 31, 46, 0.02);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .card-app:hover {
            border-color: rgba(15, 31, 46, 0.14);
        }

        .stat-editorial {
            background: var(--surface);
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 1.25rem 1.35rem;
            height: 100%;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-editorial:hover {
            border-color: rgba(15, 31, 46, 0.16);
            box-shadow: 0 2px 8px rgba(15, 31, 46, 0.03);
        }

        .stat-editorial .stat-label {
            font-family: var(--font-sans);
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .stat-editorial .stat-number {
            font-family: var(--font-serif);
            font-size: 1.95rem;
            font-weight: 500;
            line-height: 1.1;
            color: var(--navy);
        }

        .stat-editorial .stat-number.accent {
            color: var(--red);
        }

        .table-editorial {
            font-size: 0.86rem;
            margin-bottom: 0;
        }

        .table-editorial thead th {
            font-family: var(--font-sans);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-secondary);
            background: #f8fafc;
            border-bottom: 1px solid var(--border-subtle);
            padding: 0.85rem 1rem;
            white-space: nowrap;
        }

        .table-editorial tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(15, 31, 46, 0.06);
            color: var(--text-main);
        }

        .table-editorial tbody tr:last-child td {
            border-bottom: none;
        }

        .table-editorial tbody tr:hover td {
            background: rgba(15, 31, 46, 0.015);
        }

        .text-navy {
            color: var(--navy) !important;
        }

        .text-accent {
            color: var(--red) !important;
            font-weight: 600;
        }

        .btn-app {
            background: var(--corporate-blue);
            border: 1px solid var(--corporate-blue);
            color: #ffffff;
            font-family: var(--font-sans);
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.02em;
            padding: 0.48rem 1.15rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-app:hover, .btn-app:focus-visible {
            background: var(--corporate-blue-hover);
            border-color: var(--corporate-blue-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(33, 74, 123, 0.25);
        }

        .btn-ubsi, .btn-accent-red {
            background: var(--ubsi-red);
            border: 1px solid var(--ubsi-red);
            color: #ffffff;
            font-family: var(--font-sans);
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.02em;
            padding: 0.48rem 1.15rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-ubsi:hover, .btn-ubsi:focus-visible, .btn-accent-red:hover {
            background: var(--red-hover);
            border-color: var(--red-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(223, 11, 43, 0.25);
        }

        .btn-outline-navy {
            background: transparent;
            border: 1px solid rgba(11, 29, 58, 0.22);
            color: var(--navy);
            font-family: var(--font-sans);
            font-weight: 500;
            font-size: 0.84rem;
            padding: 0.45rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-outline-navy:hover, .btn-outline-navy:focus-visible {
            background: var(--navy);
            border-color: var(--navy);
            color: #ffffff;
        }

        .btn-outline-app {
            background: transparent;
            border: 1px solid var(--corporate-blue);
            color: var(--corporate-blue);
            font-family: var(--font-sans);
            font-weight: 500;
            font-size: 0.84rem;
            padding: 0.45rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-outline-app:hover, .btn-outline-app:focus-visible {
            background: rgba(33, 74, 123, 0.08);
            color: var(--corporate-blue);
        }

        .form-control-editorial, .form-select.form-control-editorial {
            font-family: var(--font-sans);
            font-size: 0.88rem;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            padding: 0.55rem 0.85rem;
            color: var(--text-main);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control-editorial:focus, .form-select.form-control-editorial:focus {
            border-color: var(--corporate-blue);
            box-shadow: 0 0 0 3px var(--focus-ring);
            outline: none;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            background: var(--paper);
        }

        /* Mobile Sidebar Drawer */
        @media (max-width: 991.98px) {
            .app-sidebar {
                position: fixed;
                top: 64px;
                bottom: 0;
                left: 0;
                z-index: 1015;
                transform: translateX(-100%);
            }

            .app-sidebar.show {
                transform: translateX(0);
                box-shadow: 6px 0 24px rgba(10, 22, 34, 0.25);
            }

            .sidebar-backdrop {
                position: fixed;
                top: 64px;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(10, 22, 34, 0.4);
                z-index: 1010;
                display: none;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .app-content {
                padding: 1.25rem 1rem;
            }
        }

        :focus-visible {
            outline: 2px solid var(--red);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    @auth
        <header class="header-top">
            <div class="container-fluid px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-navy d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle navigation">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <a class="brand-mark" href="{{ route('dashboard') }}">
                        <div class="brand-crest">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 3L2 8L12 13L22 8L12 3Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 10.5V16C6 17.5 8.7 19 12 19C15.3 19 18 17.5 18 16V10.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="1.5" fill="#9e2a2b"/>
                            </svg>
                        </div>
                        <div class="brand-text">
                            <span class="brand-title">Monitoring Program Kerja</span>
                            <span class="brand-sub">Universitas BSI</span>
                        </div>
                    </a>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-semibold small text-navy">{{ auth()->user()->name }}</div>
                        <div class="text-secondary" style="font-size: 0.72rem; letter-spacing: 0.02em;">
                            {{ auth()->user()->roleLabel() }}
                            @if (!auth()->user()->isSystemAdmin() && auth()->user()->campus)
                                &bull; Kampus {{ auth()->user()->campus->name }}
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-navy" type="submit">Keluar</button>
                    </form>
                </div>
            </div>
        </header>
        <div class="accent-hairline"></div>

        <div class="app-shell">
            <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
            <aside class="app-sidebar" id="appSidebar">
                <nav class="sidebar-nav">
                    <div class="nav-group-title">Menu Utama</div>
                    <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span>Dasbor</span>
                    </a>

                    @if (in_array(auth()->user()->role, ['campus_head', 'unit_head', 'unit_member'], true))
                        <div class="nav-group-title">Program Kerja</div>
                        <a class="sidebar-link {{ request()->routeIs('work-programs.index') ? 'active' : '' }}" href="{{ route('work-programs.index') }}">
                            <span>Daftar Program Kerja</span>
                        </a>
                        @if (auth()->user()->role === 'unit_head')
                            <a class="sidebar-link {{ request()->routeIs('work-programs.create') ? 'active' : '' }}" href="{{ route('work-programs.create') }}">
                                <span>Tambah Program</span>
                            </a>
                        @endif
                    @endif

                    @if (in_array(auth()->user()->role, ['campus_head', 'unit_head', 'unit_member'], true))
                        <div class="nav-group-title">Komunikasi & Aduan</div>
                        <a class="sidebar-link {{ request()->routeIs('communication.index') ? 'active' : '' }}" href="{{ route('communication.index') }}">
                            <span>Kotak Masuk Aduan</span>
                        </a>
                        <a class="sidebar-link {{ request()->routeIs('communication.create') ? 'active' : '' }}" href="{{ route('communication.create') }}">
                            <span>Buat Aduan / Aspirasi</span>
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['campus_head', 'unit_head', 'unit_member'], true))
                        <div class="nav-group-title">Laporan</div>
                        <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                            <span>Laporan Kinerja</span>
                        </a>
                    @endif

                    @if (in_array(auth()->user()->role, ['system_admin', 'campus_head'], true))
                        <div class="nav-group-title">Organisasi</div>
                        @if (auth()->user()->role === 'campus_head')
                            <a class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}">
                                <span>Unit Bagian & Tim</span>
                            </a>
                        @endif
                        <a class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <span>Pengguna</span>
                        </a>
                    @endif
                </nav>
            </aside>

            <main class="app-content">
                @yield('content')
            </main>
        </div>

        <script>
            const sidebar = document.getElementById('appSidebar');
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (toggle && sidebar && backdrop) {
                toggle.addEventListener('click', () => {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                });

                backdrop.addEventListener('click', () => {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                });
            }
        </script>
    @else
        <div class="accent-line"></div>
        @yield('content')
    @endauth
</body>
</html>
