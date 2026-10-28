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

</aside>
