@php
    $hasChildren = !empty($item['children']);
    $icon        = $item['icon_name'] ?? ($hasChildren ? 'fa fa-folder-o' : 'fa fa-circle-o');
    $menuName    = ucwords(str_replace(['_', '-'], ' ', $item['menu_name']));
    
    // Check if current item is active (matching path and query if present)
    $targetPath  = !empty($item['url']) ? parse_url($item['url'], PHP_URL_PATH) : null;
    $targetQuery = !empty($item['url']) ? parse_url($item['url'], PHP_URL_QUERY) : null;
    
    $isActive = false;
    if (!empty($item['url'])) {
        if ($targetQuery) {
            $isActive = request()->is(ltrim($targetPath, '/')) && str_contains(request()->getQueryString() ?? '', $targetQuery);
        } else {
            $isActive = request()->is(ltrim($item['url'], '/'));
        }
    }

    // Check if any child item is active to auto-expand parent
    $hasActiveChild = false;
    if ($hasChildren) {
        foreach ($item['children'] as $child) {
            if (!empty($child['url'])) {
                $cPath  = parse_url($child['url'], PHP_URL_PATH);
                $cQuery = parse_url($child['url'], PHP_URL_QUERY);
                if ($cQuery) {
                    if (request()->is(ltrim($cPath, '/')) && str_contains(request()->getQueryString() ?? '', $cQuery)) {
                        $hasActiveChild = true;
                        break;
                    }
                } elseif (request()->is(ltrim($child['url'], '/'))) {
                    $hasActiveChild = true;
                    break;
                }
            }
        }
    }
@endphp

@if ($hasChildren)
    <li class="menu-node">
        <a href="javascript:void(0)"
           class="menu-item has-children {{ ($isActive || $hasActiveChild) ? 'active' : '' }}"
           onclick="toggleMenu('m-{{ $item['menu_id'] }}')">
            <i class="{{ $icon }}"></i>
            <span class="menu-label">{{ $menuName }}</span>
            <i class="fa fa-chevron-down arrow {{ $hasActiveChild ? 'open' : '' }}" id="arrow-m-{{ $item['menu_id'] }}" style="{{ $hasActiveChild ? 'transform: rotate(180deg);' : '' }}"></i>
        </a>

        <ul id="m-{{ $item['menu_id'] }}" class="submenu" style="{{ $hasActiveChild ? 'display:block;' : 'display:none;' }}">
            @foreach ($item['children'] as $child)
                @include('partials.menu-item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    </li>
@else
    <li class="menu-leaf">
        @if ($item['url'])
            <a href="{{ $item['url'] }}"
               class="menu-item {{ $isActive ? 'active' : '' }}">
                <i class="{{ $icon }}"></i>
                <span class="menu-label">{{ $menuName }}</span>
            </a>
        @else
            <a href="javascript:void(0)"
               class="menu-item"
               onclick="alert('Coming soon: {{ $menuName }}')">
                <i class="{{ $icon }}"></i>
                <span class="menu-label">{{ $menuName }}</span>
            </a>
        @endif
    </li>
@endif
