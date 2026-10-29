<!DOCTYPE html>
<html>
<head>
    <title>Vignan Online — @yield('page_title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ file_exists(public_path('css/theme.css')) ? filemtime(public_path('css/theme.css')) : '1.0' }}">

    {{-- Immediate theme initialization to prevent flash of wrong mode --}}
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('cdoe_theme_mode');
                const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                }
                const savedColor = localStorage.getItem('cdoe_color_theme') || 'crimson';
                document.documentElement.setAttribute('data-theme-color', savedColor);
            } catch (e) {}
        })();
    </script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html {
            background-color: var(--bg-main);
            color-scheme: light;
        }
        html.dark {
            color-scheme: dark;
            background-color: var(--bg-main);
        }
        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            color: var(--text-secondary);
            background-color: var(--bg-main);
            background-image: var(--bg-main-gradient);
            background-attachment: fixed;
            min-height: 100vh;
            transition: background-color 0.3s ease, background-image 0.3s ease, color 0.3s ease;
        }
        html.dark body {
            background-color: var(--bg-main) !important;
            background-image: var(--bg-main-gradient) !important;
            color: var(--text-secondary);
        }

        /* ============ TOP NAVBAR (PERMANENTLY FIXED & NEVER CUT OFF) ============ */
        .topnav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 64px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--topnav-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1.5px solid var(--topnav-border);
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
            z-index: 1000;
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        .brand,
        .topnav-brand-wrap {
            display: flex;
            align-items: center;
            gap: 11px;
            height: 100%;
        }
        .topnav .breadcrumb,
        .topnav .topnav-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            padding: 5px 14px;
            background: var(--bg-hover-row);
            border: 1px solid var(--border-subtle);
            border-radius: 999px;
            transition: all 0.25s ease;
        }
        .topnav .topnav-breadcrumb .bc-root { color: var(--theme-primary); font-weight: 700; }
        .topnav .topnav-breadcrumb .bc-sep { color: var(--text-muted); opacity: 0.5; font-size: 11px; }
        .topnav .topnav-breadcrumb .bc-role { color: var(--text-secondary); font-weight: 600; }
        .topnav .topnav-breadcrumb .bc-page { color: var(--text-primary); font-weight: 700; }
        .topnav .user-area { display: flex; align-items: center; gap: 14px; }

        .topnav .logout-btn {
            border: 1px solid var(--border-subtle);
            background: var(--bg-card);
            color: var(--text-secondary);
            width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 15px;
            text-decoration: none; transition: all .2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .topnav .logout-btn:hover {
            background: var(--theme-primary);
            border-color: var(--theme-primary);
            color: #fff;
            transform: scale(1.05);
        }

        /* ============ LAYOUT ============ */
        .layout {
            display: flex;
            margin-top: 64px;
            min-height: calc(100vh - 64px);
            position: relative;
        }

        /* ============ ROLE SWITCHER ============ */
        .role-switcher { position: relative; }

        .role-pill {
            background: var(--theme-gradient);
            border: 1px solid rgba(255,255,255,.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 4px 14px var(--theme-glow);
            font-family: inherit;
        }
        .role-pill:hover {
            transform: translateY(-1px);
            filter: brightness(1.08);
            box-shadow: 0 6px 18px var(--theme-glow);
        }
        .role-pill .role-caret {
            font-size: 11px;
            transition: transform .25s;
        }

        .role-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            min-width: 260px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 8px;
            box-shadow: var(--card-shadow);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all .2s ease;
            z-index: 999;
        }
        .role-menu.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .role-menu-title {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            padding: 8px 12px 6px;
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            color: var(--text-secondary);
            text-align: left;
            font-size: 13px;
            cursor: pointer;
            transition: all .15s;
            font-family: inherit;
        }
        .role-option:hover {
            background: var(--bg-hover-row);
            color: var(--text-primary);
        }
        .role-option.active {
            background: var(--theme-primary-light);
            color: var(--theme-primary);
            border-color: var(--theme-focus-ring);
            font-weight: 600;
        }
        .role-option i { font-size: 16px; color: var(--text-muted); }
        .role-option.active i { color: var(--theme-primary); }
        .role-option .rname { font-weight: 600; color: inherit; }
        .role-option .rsub { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
        .role-option.active .rsub { color: var(--theme-primary); }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 260px;
            flex-shrink: 0;
            padding: 12px 14px 20px;
            overflow-y: auto;
            background: var(--sidebar-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-right: 1.5px solid var(--sidebar-border);
            box-shadow: 2px 0 8px rgba(15, 23, 42, 0.05);
            position: fixed;
            top: 64px;
            bottom: 0;
            left: 0;
            height: calc(100vh - 64px);
            display: flex;
            flex-direction: column;
            z-index: 900;
            transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.28s ease, background 0.3s ease, border-color 0.3s ease;
        }

        .menu-tree { list-style: none; padding: 0; margin: 0; flex: 1; }
        .submenu { list-style: none; padding: 3px 0 5px 8px; margin: 2px 0 6px 18px; border-left: 2px solid var(--sidebar-border); }
        .submenu .menu-item { font-size: 13.5px; color: var(--sidebar-text); border-radius: 10px; }

        .sidebar a.menu-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 3px;
            transition: all .18s ease;
            border: 1px solid transparent;
        }
        .sidebar a.menu-item i {
            font-size: 15px; width: 20px; text-align: center;
            opacity: .85;
        }
        .sidebar a.menu-item:hover {
            background: var(--sidebar-hover-bg);
            color: var(--sidebar-text-hover);
            border-color: rgba(255,255,255,.05);
        }
        .sidebar a.menu-item.active {
            background: var(--theme-active-pill) !important;
            color: #fff !important;
            font-weight: 600;
            border-color: rgba(255,255,255,.15);
            box-shadow: 0 4px 14px var(--theme-glow);
        }
        .sidebar a.menu-item.active i { color: #fff; opacity: 1; }

        .menu-item.has-children { cursor: pointer; }
        .menu-item .arrow {
            transition: transform .25s ease;
            font-size: 11px;
            margin-left: auto;
            opacity: .7;
        }

        /* ============ SIDEBAR THEME WIDGET (BOTTOM PINNED) ============ */
        .sidebar-theme-section {
            margin-top: auto;
            padding: 14px 4px 6px;
            border-top: 1px solid var(--sidebar-border);
        }
        .sidebar-theme-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0 4px 8px;
        }
        .sidebar .theme-widget-container {
            width: 100%;
            justify-content: space-between;
            padding: 6px 12px;
            background: var(--bg-hover-row);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            backdrop-filter: none;
        }
        html.dark .sidebar .theme-widget-container {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(255, 255, 255, 0.08);
        }

        /* ============ MAIN CONTENT ============ */
        .main {
            flex: 1;
            margin-left: 260px;
            padding: 24px 28px;
            min-width: 0;
            position: relative;
            transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.sidebar-collapsed .main {
            margin-left: 0;
        }
        .page-title { font-size: 24px; font-weight: 700; color: var(--text-primary); transition: color 0.3s ease; }
        .page-sub { color: var(--text-muted); font-size: 13px; margin-top: 4px; transition: color 0.3s ease; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px; margin-top: 24px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            padding: 20px;
            border-left: 4px solid #64748b;
            box-shadow: var(--card-shadow);
            transition: transform .2s, background 0.3s ease, border-color 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card.green  { border-left-color: #10b981; }
        .stat-card.blue   { border-left-color: #3b82f6; }
        .stat-card.yellow { border-left-color: #f59e0b; }
        .stat-card.red    { border-left-color: #ef4444; }

        .stat-card .label {
            font-size: 11px; color: var(--text-muted); font-weight: 700;
            text-transform: uppercase; letter-spacing: .8px;
        }
        .stat-card .value { font-size: 26px; font-weight: 800; color: var(--text-primary); margin-top: 6px; }
        .stat-card .note { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            padding: 22px;
            margin-top: 22px;
            color: var(--text-secondary);
            box-shadow: var(--card-shadow);
            transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .card h3 { font-size: 16px; color: var(--text-primary); margin-bottom: 14px; }
        .card p { color: var(--text-secondary); font-size: 14px; }

        .quick-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .quick-actions a {
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
            transition: all .2s;
        }
        .btn-navy { background: linear-gradient(135deg, #1e3a8a, #1e40af); color: #fff; }
        .btn-navy:hover { filter: brightness(1.1); }
        .btn-outline {
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border-color: var(--border-subtle);
        }
        .btn-outline:hover {
            background: var(--bg-hover-row);
            color: var(--text-primary);
            border-color: var(--border-hover);
        }
        .btn-red {
            background: var(--theme-gradient);
            color: #fff;
            box-shadow: 0 4px 12px var(--theme-glow);
        }
        .btn-red:hover { filter: brightness(1.1); }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .topnav { padding: 0 14px; }
            .topnav .breadcrumb,
            .topnav .topnav-breadcrumb { display: none !important; }
            .topnav .user-pill { padding: 7px 12px; font-size: 12px; }
            .main { margin-left: 0 !important; padding: 18px 14px; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ===================== INSTITUTIONAL WATERMARK ===================== --}}
<div class="vignan-watermark" aria-hidden="true" style="background-image: url('{{ asset('images/vignan-emblem.png') }}?v={{ file_exists(public_path('images/vignan-emblem.png')) ? filemtime(public_path('images/vignan-emblem.png')) : '1.0' }}');"></div>

{{-- Mobile Sidebar Backdrop --}}
<div class="sidebar-backdrop" onclick="toggleSidebar()" aria-hidden="true"></div>

{{-- ===================== TOP NAVBAR ===================== --}}
@php
    /* ---- Lazy role fetch & human-friendly name resolution ---- */
    $userCode = session('user.usercode');
    $assignedRoles = $userCode
        ? \App\Common\Services\RoleService::getAssignedRoles($userCode)
        : [];

    $currentRoleId   = session('user.role_id');
    $currentRoleName = null;

    foreach ($assignedRoles as $r) {
        if ((int) $r['role_id'] === (int) $currentRoleId) {
            $currentRoleName = $r['role_name'];
            break;
        }
    }

    if (! $currentRoleName && $currentRoleId) {
        $currentRoleName = \App\Models\Role::where('role_id', $currentRoleId)->value('role_name');
    }

    if (! $currentRoleName && ! empty($assignedRoles)) {
        $adminRole = collect($assignedRoles)->firstWhere('role_name', 'Super Admin');
        $currentRoleName = $adminRole ? $adminRole['role_name'] : $assignedRoles[0]['role_name'];
    }

    if (! $currentRoleName) {
        $currentRoleName = (session('user.user_type') == 1) ? 'Student' : 'Super Admin';
    }

    // Resolve real full name from HRMS employee profile or student registration
    $userName = session('user.name');
    if (! $userName && $userCode) {
        $userName = \Modules\Hrms\Models\Employee::where('emp_code', $userCode)->value('full_name')
            ?: \Modules\Admissions\Models\StudentRegistration::where('register_no', $userCode)->value('student_name');
    }
    $displayName = $userName ?: ($user->usercode ?? $userCode ?? 'Administrator');
    $avatarInitial = strtoupper(substr(trim(preg_replace('/^(Mr\.|Mrs\.|Ms\.|Dr\.|Prof\.)\s+/i', '', $displayName)), 0, 1)) ?: 'A';

    $hasMultipleRoles = count($assignedRoles) > 1;
@endphp

<div class="topnav">
    <div class="brand topnav-brand-wrap">
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle navigation" title="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div class="brand-logo-card">
            <img src="{{ asset('images/vignan-emblem.png') }}?v={{ file_exists(public_path('images/vignan-emblem.png')) ? filemtime(public_path('images/vignan-emblem.png')) : '1.0' }}" class="brand-emblem" alt="Vignan Emblem">
        </div>
        <span class="brand-title">
            <span class="d-none d-sm-inline">{{ str_starts_with($currentRoleName ?? '', 'CDOE') ? $currentRoleName : 'CDOE ' . ($currentRoleName ?: 'Administrator') }}</span>
            <span class="d-inline d-sm-none">CDOE</span>
        </span>
    </div>

    <div class="topnav-breadcrumb d-none d-lg-inline-flex">
        <span class="bc-root"><i class="bi bi-mortarboard-fill me-1"></i> CDOE</span>
        <span class="bc-sep">/</span>
        <span class="bc-role">{{ $currentRoleName }}</span>
        <span class="bc-sep">/</span>
        <span class="bc-page">@yield('page_title', 'Dashboard')</span>
    </div>

    <div class="user-area">

        {{-- ===== Single Consolidated Profile Dropdown ===== --}}
        <div class="role-switcher" id="roleSwitcher">
            <button type="button" class="user-pill-btn" onclick="toggleRoleMenu()" aria-expanded="false" title="Account, Appearance & Roles">
                <span class="user-avatar-circle">
                    {{ $avatarInitial }}
                </span>
                <div class="user-pill-info d-none d-md-flex">
                    <span class="user-pill-name">{{ $displayName }}</span>
                    <span class="user-pill-role">{{ $currentRoleName }}</span>
                </div>
                <i class="bi bi-chevron-down role-caret d-none d-md-inline-block"></i>
            </button>

            <div class="role-menu" id="roleMenu">
                {{-- User Profile Card --}}
                <div class="role-menu-header">
                    <div class="role-menu-user-card">
                        <span class="user-avatar-circle sm">
                            {{ $avatarInitial }}
                        </span>
                        <div>
                            <div class="fw-bold" style="font-size: 13.5px; color: var(--text-primary);">{{ $displayName }}</div>
                            <div class="text-muted" style="font-size: 11.5px;">{{ $user->usercode ?? $userCode }} • {{ $currentRoleName }}</div>
                        </div>
                    </div>
                </div>

                {{-- Consolidated Theme Palette & Mode Switcher --}}
                <div class="role-menu-section">
                    <div class="role-menu-title">Appearance & Theme</div>
                    @include('partials.theme-toggle')
                </div>

                <div class="role-menu-divider"></div>

                {{-- Role Switcher --}}
                @if ($hasMultipleRoles)
                    <div class="role-menu-title">Switch Role</div>

                    @foreach ($assignedRoles as $r)
                        <form method="POST" action="{{ route('switch.role', ['roleId' => $r['role_id']]) }}">
                            @csrf
                            <button type="submit"
                                    class="role-option {{ (int)$r['role_id'] === (int)$currentRoleId ? 'active' : '' }}">
                                <i class="bi bi-check2-circle"></i>
                                <div>
                                    <div class="rname">{{ $r['role_name'] }}</div>
                                    <div class="rsub">
                                        @if (!empty($r['end_date']))
                                             Till {{ \Carbon\Carbon::parse($r['end_date'])->format('d M Y') }}
                                        @else
                                            Active
                                        @endif
                                    </div>
                                </div>
                            </button>
                        </form>
                    @endforeach
                    <div class="role-menu-divider"></div>
                @endif

                {{-- Log Out Button --}}
                <a href="{{ route('logout') }}" class="role-logout-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </div>

    </div>
</div>

{{-- ===================== LAYOUT ===================== --}}
<div class="layout">

    @include('partials.sidebar')

    <div class="main">
        @unless(View::hasSection('hideTitle'))
        <div class="page-title">@yield('page_title', 'Dashboard')</div>
        @endunless
        @unless(View::hasSection('hidePageSub'))
        <div class="page-sub">
            Welcome, {{ $user->usercode ?? session('user.usercode', 'User') }} — CDOE Portal /
            {{ $currentRoleName !== 'User' ? $currentRoleName : (session('user.user_type') == 1 ? 'Student' : 'Staff') }} /
            @yield('page_title', 'Dashboard')
        </div>
        @endunless
        @yield('content')
    </div>

</div>

{{-- ===================== SCRIPTS ===================== --}}
<script>
    function toggleMenu(id) {
        const el = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        if (!el) return;

        const isHidden = (el.style.display === 'none' || el.style.display === '');
        if (isHidden) {
            el.style.display = 'block';
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            el.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.menu-item.active').forEach(function (el) {
            let parent = el.closest('.submenu');
            while (parent) {
                parent.style.display = 'block';
                const arrow = document.getElementById('arrow-' + parent.id);
                if (arrow) arrow.style.transform = 'rotate(180deg)';
                parent = parent.parentElement
                    ? parent.parentElement.closest('.submenu')
                    : null;
            }
        });
    });

    function toggleRoleMenu() {
        const menu = document.getElementById('roleMenu');
        const caret = document.querySelector('.role-caret');
        if (!menu) return;

        menu.classList.toggle('open');
        if (caret) {
            caret.style.transform = menu.classList.contains('open')
                ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    }

    document.addEventListener('click', function (e) {
        const switcher = document.getElementById('roleSwitcher');
        const menu = document.getElementById('roleMenu');
        if (!switcher || !menu) return;

        if (!switcher.contains(e.target)) {
            menu.classList.remove('open');
            const caret = document.querySelector('.role-caret');
            if (caret) caret.style.transform = 'rotate(0deg)';
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/theme.js') }}?v={{ file_exists(public_path('js/theme.js')) ? filemtime(public_path('js/theme.js')) : '1.0' }}"></script>

@stack('scripts')
</body>
</html>

