<aside class="sidebar" id="appSidebar">

    {{-- Mobile drawer header (hidden on desktop to avoid duplicate header below topnav) --}}
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-logo-card">
                <img src="{{ asset('images/vignan-emblem.png') }}" class="sidebar-brand-emblem" alt="Vignan Emblem">
            </div>
            <span class="sidebar-brand-title">CDOE PORTAL</span>
        </div>
        <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Close navigation" title="Close navigation">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <ul class="menu-tree">
        @foreach ($menus ?? [] as $menu)
            @include('partials.menu-item', ['item' => $menu, 'level' => 1])
        @endforeach
    </ul>

    <div class="user-box">
        <div class="d-flex align-items-center gap-2">
            <span class="user-avatar-circle sm" style="width: 32px; height: 32px; font-size: 13px;">
                {{ $avatarInitial ?? strtoupper(substr($user->usercode ?? session('user.usercode', 'U'), 0, 1)) }}
            </span>
            <div style="min-width: 0; flex: 1;">
                <div class="uname text-truncate">{{ $displayName ?? ($user->usercode ?? session('user.usercode', 'User')) }}</div>
                <div class="uemail text-truncate">{{ $currentRoleName ?? 'Administrator' }} • {{ $user->usercode ?? session('user.usercode', '02266') }}</div>
            </div>
        </div>
    </div>

</aside>
