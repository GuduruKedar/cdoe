@php
    $hasChildren = !empty($item['children']);
    $icon        = $item['icon_name'] ?? ($hasChildren ? 'fa fa-folder-o' : 'fa fa-circle-o');
    $menuName    = ucwords(str_replace(['_', '-'], ' ', $item['menu_name']));
    $isActive    = $item['url'] && request()->is(ltrim($item['url'], '/'));
@endphp

@if ($hasChildren)
    <li class="menu-node">
        <a href="javascript:void(0)"
           class="menu-item has-children {{ $isActive ? 'active' : '' }}"
           onclick="toggleMenu('m-{{ $item['menu_id'] }}')">
            <i class="{{ $icon }}"></i>
            <span class="menu-label">{{ $menuName }}</span>
            <i class="fa fa-chevron-down arrow" id="arrow-m-{{ $item['menu_id'] }}"></i>
        </a>

        <ul id="m-{{ $item['menu_id'] }}" class="submenu" style="display:none;">
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
