<div class="theme-widget-container" role="region" aria-label="Theme Controls">
    {{-- Color Palette Preset Dots --}}
    <div class="color-presets-group" role="group" aria-label="Color Themes">
        <button type="button"
                class="color-swatch-btn color-swatch-crimson"
                data-color="crimson"
                onclick="setColorTheme('crimson')"
                title="Vignan Crimson (Default)"
                aria-label="Vignan Crimson Theme">
            <svg class="swatch-check" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>

        <button type="button"
                class="color-swatch-btn color-swatch-blue"
                data-color="blue"
                onclick="setColorTheme('blue')"
                title="Royal Blue Theme"
                aria-label="Royal Blue Theme">
            <svg class="swatch-check" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>

        <button type="button"
                class="color-swatch-btn color-swatch-green"
                data-color="green"
                onclick="setColorTheme('green')"
                title="Emerald Green Theme"
                aria-label="Emerald Green Theme">
            <svg class="swatch-check" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>

        <button type="button"
                class="color-swatch-btn color-swatch-violet"
                data-color="violet"
                onclick="setColorTheme('violet')"
                title="Purple Violet Theme"
                aria-label="Purple Violet Theme">
            <svg class="swatch-check" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>

    {{-- Separator --}}
    <div class="theme-widget-sep"></div>

    {{-- Dark / Light Mode Animated Sliding Switch --}}
    <button type="button"
            id="themeToggleBtn"
            class="theme-switch-track"
            onclick="toggleThemeMode()"
            data-theme-toggle
            role="switch"
            aria-checked="false"
            title="Toggle Light / Dark Mode"
            aria-label="Toggle Light / Dark Mode">
        <span class="theme-switch-knob">
            {{-- Sun Icon for Light Mode --}}
            <svg class="theme-icon-sun" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
            </svg>
            {{-- Moon Icon for Dark Mode --}}
            <svg class="theme-icon-moon" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
            </svg>
        </span>
    </button>
</div>
