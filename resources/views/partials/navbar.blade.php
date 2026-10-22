<div class="topbar">
    <div class="title">@yield('page_title', 'Dashboard')</div>
    <div class="user">
        <div class="avatar">{{ strtoupper(substr($user->usercode, 0, 1)) }}</div>
        <div>
            <div>{{ $user->usercode }}</div>
            <div style="font-size:11px;color:#94a3b8;">Role ID: {{ $user->role_id ?? '—' }}</div>
        </div>
        <a href="{{ route('logout') }}" style="color:#f87171; margin-left:12px; font-size:13px;">Logout</a>
    </div>
</div>
