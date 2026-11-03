<aside class="sidebar" id="appSidebar">

    {{-- Mobile drawer header (hidden on desktop to avoid duplicate header below topnav) --}}
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-logo-card">
                <img src="{{ asset('images/vignan-logo-rect.jpeg') }}?v={{ file_exists(public_path('images/vignan-logo-rect.jpeg')) ? filemtime(public_path('images/vignan-logo-rect.jpeg')) : '1.0' }}" class="sidebar-brand-emblem" alt="Vignan Logo">
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

    {{-- Bottom pinned Appearance & Theme Widget --}}
    <div class="sidebar-theme-section">
        <div class="sidebar-theme-title">Appearance & Theme</div>
        @include('partials.theme-toggle')
    </div>

</aside>
