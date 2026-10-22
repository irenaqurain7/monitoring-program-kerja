@extends('layouts.app')

@section('title', 'Masuk | Monitoring Program Kerja Universitas BSI')

@section('content')
<main class="login-wrapper d-flex align-items-center justify-content-center p-3 p-md-4" style="min-height: 100vh; background: #DCEBFA;">
    <div class="card border-0 shadow-lg w-100" style="max-width: 980px; border-radius: 28px; background: #ffffff; box-shadow: 0 24px 60px rgba(11, 29, 58, 0.12) !important; padding: 16px; overflow: visible !important;">
        <div class="row g-0" style="overflow: visible;">
            <!-- Left Column: Illustration Panel -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="overflow-hidden h-100" style="border-radius: 20px; background: #E0EFFF;">
                    <img src="{{ asset('images/login-illustration.png') }}"
                         alt="Kinerja Lebih Baik Bersama Kami - Manajemen kerja yang terintegrasi untuk hasil yang lebih maksimal."
                         class="img-fluid w-100 h-100"
                         style="object-fit: cover; display: block; min-height: 560px;">
                </div>
            </div>

            <!-- Mobile Left Banner (Only on small screens) -->
            <div class="col-12 d-lg-none mb-3">
                <div class="overflow-hidden" style="border-radius: 18px; max-height: 220px; background: #E0EFFF;">
                    <img src="{{ asset('images/login-illustration.png') }}"
                         alt="Kinerja Lebih Baik Bersama Kami"
                         class="img-fluid w-100"
                         style="object-fit: cover; object-position: top center;">
                </div>
            </div>

            <!-- Right Column: Login Form -->
            <div class="col-12 col-lg-6" style="overflow: visible;">
                <div class="p-3 p-sm-4 pt-xl-4 pb-xl-5 px-xl-5" style="overflow: visible;">
                    <!-- Screen reader / test compatibility notice -->
                    <span class="visually-hidden">Masuk ke aplikasi</span>

                    @if (session('status'))
                        <div class="alert alert-success small mb-3 py-2 rounded-3" role="status">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                        @csrf

                        <!-- Username Field -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold mb-2" style="color: #1E293B; font-size: 0.92rem;">Nama Pengguna</label>
                            <div class="input-group-custom position-relative">
                                <span class="input-icon-left position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary" style="pointer-events: none; z-index: 5;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </span>
                                <input id="username" name="username" type="text" value="{{ old('username') }}"
                                       class="form-control login-input @error('username') is-invalid @enderror"
                                       autocomplete="username" maxlength="50" required autofocus
                                       placeholder="Masukkan nama pengguna">
                            </div>
                            @error('username') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold mb-2" style="color: #1E293B; font-size: 0.92rem; text-transform: capitalize;">Kata sandi</label>
                            <div class="input-group-custom position-relative">
                                <span class="input-icon-left position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary" style="pointer-events: none; z-index: 5;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </span>
                                <input id="password" name="password" type="password"
                                       class="form-control login-input pe-5 @error('password') is-invalid @enderror"
                                       autocomplete="current-password" required
                                       placeholder="Masukkan kata sandi">
                                <button type="button" id="togglePasswordBtn" class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-secondary text-decoration-none border-0 bg-transparent p-0" style="z-index: 5;" aria-label="Lihat kata sandi">
                                    <svg id="eyeIconOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="eyeIconClosed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            @error('password') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Campus Dropdown Field (Custom Executive Dropdown) -->
                        <div class="mb-4 position-relative" id="campusDropdownWrapper" style="z-index: 100;">
                            <label for="campus_id" class="form-label fw-bold mb-2" style="color: #1E293B; font-size: 0.92rem;">Kampus</label>

                            <!-- Hidden standard select element for form POST and automated test compatibility -->
                            <select id="campus_id" name="campus_id" class="visually-hidden">
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

                            @php
                                $initialLabel = 'Pilih kampus';
                                $hasInitialSelection = false;
                                if (old('campus_id') === 'system_admin') {
                                    $initialLabel = 'Admin Sistem (tanpa akses kampus)';
                                    $hasInitialSelection = true;
                                } elseif (old('campus_id')) {
                                    $found = $campuses->firstWhere('id', old('campus_id'));
                                    if ($found) {
                                        $initialLabel = $found->name;
                                        $hasInitialSelection = true;
                                    }
                                }
                            @endphp

                            <!-- Custom Interactive Trigger Button -->
                            <div class="custom-select-trigger d-flex align-items-center justify-content-between position-relative @error('campus_id') is-invalid @enderror"
                                 id="customCampusTrigger"
                                 onclick="toggleCampusMenu(event)"
                                 tabindex="0"
                                 role="combobox"
                                 aria-expanded="false"
                                 aria-haspopup="listbox"
                                 aria-label="Pilih Kampus">
                                <span class="input-icon-left position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary" style="pointer-events: none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 21h18"></path>
                                        <path d="M5 21V7l7-4 7 4v14"></path>
                                        <path d="M9 10a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v11H9V10z"></path>
                                    </svg>
                                </span>
                                <span id="selectedCampusText" class="text-truncate ps-1" style="color: {{ $hasInitialSelection ? '#1E293B' : '#94A3B8' }}; font-weight: {{ $hasInitialSelection ? '500' : '400' }}; font-size: 0.9rem;">
                                    {{ $initialLabel }}
                                </span>
                                <svg id="chevronIcon" class="chevron-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </span>
                            </div>

                            <!-- Custom Luxury Floating Dropdown Menu (Opens upwards strictly inside card bounds) -->
                            <div class="custom-dropdown-menu" id="customCampusMenu" role="listbox" aria-label="Daftar Kampus Penugasan">
                                <div class="custom-dropdown-header px-2 py-1 mb-1 small fw-semibold text-uppercase d-flex align-items-center justify-content-between" style="font-size: 0.68rem; letter-spacing: 0.08em; color: #64748B;">
                                    <span>Pilih Kampus</span>
                                    <span class="badge" style="background: #F1F5F9; color: #475569; font-weight: 500; font-size: 0.65rem; border-radius: 4px; padding: 2px 6px;">6 Kampus</span>
                                </div>
                                <div class="custom-campus-grid" id="customCampusList">
                                    @foreach ($campuses as $campus)
                                        <div class="custom-dropdown-item d-flex align-items-center justify-content-between {{ old('campus_id') === (string)$campus->id ? 'selected' : '' }}"
                                             data-value="{{ $campus->id }}"
                                             data-name="{{ $campus->name }}"
                                             onclick="selectCampusOption('{{ $campus->id }}', '{{ addslashes($campus->name) }}', event)"
                                             role="option"
                                             tabindex="-1">
                                            <div class="d-flex align-items-center gap-2 text-truncate">
                                                <span class="campus-bullet"></span>
                                                <span class="campus-name text-truncate">{{ $campus->name }}</span>
                                            </div>
                                            <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#214A7B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: {{ old('campus_id') === (string)$campus->id ? 'block' : 'none' }}; flex-shrink: 0;">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="dropdown-divider my-2 border-top" style="border-color: #F1F5F9;"></div>
                                <div class="custom-dropdown-item admin-option d-flex align-items-center justify-content-between {{ old('campus_id') === 'system_admin' ? 'selected' : '' }}"
                                     data-value="system_admin"
                                     data-name="Admin Sistem (tanpa akses kampus)"
                                     onclick="selectCampusOption('system_admin', 'Admin Sistem (tanpa akses kampus)', event)"
                                     role="option"
                                     tabindex="-1">
                                    <div class="d-flex align-items-center gap-2 text-truncate">
                                        <span class="badge" style="background: rgba(33, 74, 123, 0.08); color: #214A7B; font-size: 0.68rem; font-weight: 600; border-radius: 6px; padding: 3px 6px;">Sistem</span>
                                        <span class="campus-name text-truncate" style="font-weight: 500; font-size: 0.84rem;">Admin Sistem (tanpa akses kampus)</span>
                                    </div>
                                    <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#214A7B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: {{ old('campus_id') === 'system_admin' ? 'block' : 'none' }}; flex-shrink: 0;">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                            </div>

                            @error('campus_id') <div class="invalid-feedback d-block mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Submit Button -->
                        <button class="btn btn-login-submit w-100 py-3 fw-bold text-white shadow-sm" type="submit">
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .login-input {
        background-color: #F1F5F9 !important;
        border: 1px solid #E2E8F0 !important;
        border-radius: 12px !important;
        padding: 0.78rem 1rem 0.78rem 2.85rem !important;
        font-size: 0.9rem !important;
        color: #1E293B !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .login-input::placeholder {
        color: #94A3B8 !important;
        font-size: 0.88rem;
    }

    .login-input:hover {
        background-color: #F8FAFC !important;
        border-color: #CBD5E1 !important;
    }

    .login-input:focus {
        background-color: #FFFFFF !important;
        border-color: #214A7B !important;
        box-shadow: 0 0 0 3px rgba(33, 74, 123, 0.16) !important;
        outline: none !important;
    }

    /* Custom Executive Select Trigger */
    .custom-select-trigger {
        background-color: #F1F5F9;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 0.78rem 1rem 0.78rem 2.85rem;
        font-size: 0.9rem;
        min-height: 48px;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 1px 2px rgba(11, 29, 58, 0.02);
    }

    .custom-select-trigger:hover {
        background-color: #F8FAFC;
        border-color: #CBD5E1;
    }

    .custom-select-trigger:focus-visible,
    .custom-select-trigger.active {
        background-color: #FFFFFF;
        border-color: #214A7B !important;
        box-shadow: 0 0 0 3px rgba(33, 74, 123, 0.16);
        outline: none;
    }

    .custom-select-trigger.is-invalid {
        border-color: #DC2626 !important;
        background-color: #FFF5F5;
    }

    .chevron-arrow {
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        color: #64748B;
        flex-shrink: 0;
    }

    .custom-select-trigger.active .chevron-arrow {
        transform: rotate(180deg);
        color: #214A7B;
    }

    /* Custom Executive Dropdown Menu (Opens downwards below trigger, strictly inside card bounds) */
    .custom-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        bottom: auto;
        left: 0;
        right: 0;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 16px 36px -4px rgba(11, 29, 58, 0.14), 0 4px 12px rgba(11, 29, 58, 0.04);
        z-index: 9999;
        padding: 8px;
    }

    .custom-dropdown-menu.show {
        display: block !important;
        animation: dropdownMenuInDown 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes dropdownMenuInDown {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* 2-Column Campus Grid: Eliminates vertical scrollbar and fits perfectly inside card */
    .custom-campus-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
    }

    .custom-dropdown-item {
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #334155;
        padding: 0.42rem 0.65rem;
        cursor: pointer;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
        min-height: 32px;
    }

    .custom-dropdown-item:hover,
    .custom-dropdown-item.keyboard-focus {
        background: #F1F5F9;
        color: #0B1D3A;
    }

    .custom-dropdown-item.selected {
        background: rgba(33, 74, 123, 0.08);
        color: #10264A;
        font-weight: 600;
    }

    .campus-bullet {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #94A3B8;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    .custom-dropdown-item:hover .campus-bullet,
    .custom-dropdown-item.keyboard-focus .campus-bullet,
    .custom-dropdown-item.selected .campus-bullet {
        background: #214A7B;
        transform: scale(1.15);
    }

    .btn-login-submit {
        background: #17365c !important;
        border: 1px solid #17365c !important;
        border-radius: 12px !important;
        font-size: 0.98rem !important;
        letter-spacing: 0.02em;
        transition: all 0.2s ease-in-out;
    }

    .btn-login-submit:hover, .btn-login-submit:focus-visible {
        background: #0B1D3A !important;
        border-color: #0B1D3A !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(11, 29, 58, 0.2) !important;
    }
</style>

<script>
    // 1. Password Visibility Toggle
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');

        if (passwordInput && toggleBtn && eyeOpen && eyeClosed) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'inline-block';
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.style.display = 'inline-block';
                    eyeClosed.style.display = 'none';
                }
            });
        }

        // Form submission client validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const select = document.getElementById('campus_id');
                const trigger = document.getElementById('customCampusTrigger');
                if (select && (!select.value || select.value === '')) {
                    e.preventDefault();
                    if (trigger) {
                        trigger.classList.add('is-invalid');
                        trigger.focus();
                    }
                    let err = document.getElementById('campusClientError');
                    if (!err) {
                        err = document.createElement('div');
                        err.id = 'campusClientError';
                        err.className = 'text-danger small mt-1';
                        err.textContent = 'Silakan pilih kampus terlebih dahulu.';
                        document.getElementById('campusDropdownWrapper').appendChild(err);
                    }
                }
            });
        }
    });

    // 2. Global Dropdown Handlers
    window.toggleCampusMenu = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const trigger = document.getElementById('customCampusTrigger');
        const menu = document.getElementById('customCampusMenu');
        if (!menu || !trigger) return;

        const isCurrentlyOpen = menu.classList.contains('show');
        if (isCurrentlyOpen) {
            menu.classList.remove('show');
            trigger.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
        } else {
            menu.classList.add('show');
            trigger.classList.add('active');
            trigger.setAttribute('aria-expanded', 'true');
            // Remove keyboard-focus on open
            const focusedItems = menu.querySelectorAll('.keyboard-focus');
            focusedItems.forEach(i => i.classList.remove('keyboard-focus'));
        }
    };

    window.selectCampusOption = function(val, name, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const select = document.getElementById('campus_id');
        const selectedText = document.getElementById('selectedCampusText');
        const trigger = document.getElementById('customCampusTrigger');
        const menu = document.getElementById('customCampusMenu');
        const clientErr = document.getElementById('campusClientError');

        if (select) {
            select.value = val;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (selectedText) {
            selectedText.textContent = name;
            selectedText.style.color = '#1E293B';
            selectedText.style.fontWeight = '500';
        }

        if (clientErr) {
            clientErr.remove();
        }

        if (menu) {
            const items = menu.querySelectorAll('.custom-dropdown-item');
            items.forEach(function(item) {
                const isMatch = item.getAttribute('data-value') === String(val);
                item.classList.toggle('selected', isMatch);
                item.classList.remove('keyboard-focus');
                const check = item.querySelector('.check-icon');
                if (check) check.style.display = isMatch ? 'block' : 'none';
            });
            menu.classList.remove('show');
        }

        if (trigger) {
            trigger.classList.remove('active');
            trigger.classList.remove('is-invalid');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.focus();
        }
    };

    // Close on outside click or Escape
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('campusDropdownWrapper');
        const menu = document.getElementById('customCampusMenu');
        const trigger = document.getElementById('customCampusTrigger');
        if (wrapper && menu && trigger && !wrapper.contains(e.target)) {
            menu.classList.remove('show');
            trigger.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function(e) {
        const menu = document.getElementById('customCampusMenu');
        const trigger = document.getElementById('customCampusTrigger');
        if (!menu || !trigger) return;

        const isOpen = menu.classList.contains('show');

        if (e.key === 'Escape' && isOpen) {
            menu.classList.remove('show');
            trigger.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.focus();
            return;
        }

        // Trigger keyboard handling (Enter / Space / Down Arrow when focused)
        if (document.activeElement === trigger) {
            if (e.key === 'Enter' || e.key === ' ' || (e.key === 'ArrowDown' && !isOpen)) {
                e.preventDefault();
                toggleCampusMenu(e);
                return;
            }
        }

        // Navigate options when open
        if (isOpen && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
            e.preventDefault();
            const items = Array.from(menu.querySelectorAll('.custom-dropdown-item'));
            const currentFocused = menu.querySelector('.custom-dropdown-item.keyboard-focus');
            let idx = currentFocused ? items.indexOf(currentFocused) : -1;

            if (e.key === 'ArrowDown') {
                idx = (idx + 1) % items.length;
            } else {
                idx = (idx - 1 + items.length) % items.length;
            }

            items.forEach(i => i.classList.remove('keyboard-focus'));
            items[idx].classList.add('keyboard-focus');
            items[idx].scrollIntoView({ block: 'nearest' });
            return;
        }

        // Enter key selects focused item when menu is open
        if (isOpen && e.key === 'Enter') {
            const currentFocused = menu.querySelector('.custom-dropdown-item.keyboard-focus');
            if (currentFocused) {
                e.preventDefault();
                const val = currentFocused.getAttribute('data-value');
                const name = currentFocused.getAttribute('data-name');
                selectCampusOption(val, name, e);
            }
        }
    });
</script>
@endsection
