<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Monitoring Program Kerja')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --navy: #102a43; --red: #b42332; --paper: #f4f6f9; }
        body { background: var(--paper); color: #243447; min-height: 100vh; }
        .navbar-app { background: var(--navy); }
        .brand-mark { color: #fff; font-weight: 700; letter-spacing: .02em; }
        .accent-line { height: 4px; background: var(--red); }
        .card-app { border: 0; border-radius: 1rem; box-shadow: 0 12px 32px rgba(16, 42, 67, .08); }
        .text-navy { color: var(--navy); }
        .btn-app { background: var(--red); border-color: var(--red); color: #fff; }
        .btn-app:hover, .btn-app:focus { background: #941b28; border-color: #941b28; color: #fff; }
        .login-shell { min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; }
        .app-menu { position: relative; }
        .app-menu summary { list-style: none; cursor: pointer; }
        .app-menu summary::-webkit-details-marker { display: none; }
        .app-menu summary::after { content: 'v'; margin-left: .45rem; font-size: .75em; }
        .app-menu[open] summary::after { content: '^'; }
        .app-menu-list {
            position: absolute;
            top: calc(100% + .5rem);
            right: 0;
            z-index: 1000;
            min-width: 12rem;
            padding: .5rem;
            background: #fff;
            border: 1px solid rgba(16, 42, 67, .12);
            border-radius: .5rem;
            box-shadow: 0 .5rem 1rem rgba(16, 42, 67, .15);
        }
        .app-menu-list a { display: block; padding: .5rem .75rem; color: var(--navy); text-decoration: none; border-radius: .25rem; }
        .app-menu-list a:hover, .app-menu-list a:focus { background: #f4f6f9; }
    </style>
</head>
<body>
    <nav class="navbar navbar-app">
        <div class="container">
            <a class="navbar-brand brand-mark" href="{{ route('dashboard') }}">Monitoring Program Kerja</a>
            @auth
                <div class="d-flex align-items-center gap-3 text-white">
                    <details class="app-menu">
                        <summary class="btn btn-sm btn-outline-light">Menu</summary>
                        <div class="app-menu-list">
                            @if (in_array(auth()->user()->role, ['campus_head', 'unit_head', 'unit_member'], true))
                                <a href="{{ route('work-programs.index') }}">Program Kerja</a>
                            @endif
                            @if (auth()->user()->role === 'campus_head')
                                <a href="{{ route('units.index') }}">Kelola Unit & Tim</a>
                            @endif
                            @if (in_array(auth()->user()->role, ['system_admin', 'campus_head', 'unit_head'], true))
                                <a href="{{ route('users.index') }}">Pengguna</a>
                            @endif
                        </div>
                    </details>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">Keluar</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>
    <div class="accent-line"></div>
    @yield('content')
</body>
</html>
