/**
 * ==============================================================================
 * CDOE PORTAL - THEME ENGINE JAVASCRIPT
 * Handles Light/Dark mode toggling and Color Themes with LocalStorage persistence
 * ==============================================================================
 */

(function () {
    // 1. Initial State Determination (executes instantly to avoid FOUC)
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
})();

// Global functions exposed to window
window.toggleThemeMode = function () {
    const isDark = document.documentElement.classList.toggle('dark');
    const mode = isDark ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', mode);
    localStorage.setItem('cdoe_theme_mode', mode);
    updateToggleButtonsUI(isDark);
    window.dispatchEvent(new CustomEvent('cdoeThemeChanged', { detail: { mode, isDark } }));
};

window.setColorTheme = function (colorName) {
    const validColors = ['crimson', 'blue', 'green', 'violet'];
    const activeColor = validColors.includes(colorName) ? colorName : 'crimson';
    document.documentElement.setAttribute('data-theme-color', activeColor);
    localStorage.setItem('cdoe_color_theme', activeColor);
    updateColorSwatchesUI(activeColor);
    window.dispatchEvent(new CustomEvent('cdoeColorChanged', { detail: { color: activeColor } }));
};

function updateToggleButtonsUI(isDark) {
    // Any custom aria or tooltips update
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.setAttribute('aria-checked', isDark ? 'true' : 'false');
        btn.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
    });
}

function updateColorSwatchesUI(activeColor) {
    document.querySelectorAll('.color-swatch-btn').forEach(btn => {
        const color = btn.getAttribute('data-color');
        if (color === activeColor) {
            btn.classList.add('active');
            btn.setAttribute('aria-pressed', 'true');
        } else {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', 'false');
        }
    });
}

// Global sidebar toggle
window.toggleSidebar = function () {
    if (window.innerWidth <= 768) {
        document.body.classList.toggle('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-collapsed');
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
        try {
            localStorage.setItem('cdoe_sidebar_collapsed', isCollapsed ? '1' : '0');
        } catch (e) {}
    }
};

// Attach listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    const currentColor = localStorage.getItem('cdoe_color_theme') || 'crimson';
    const isDark = document.documentElement.classList.contains('dark');
    updateColorSwatchesUI(currentColor);
    updateToggleButtonsUI(isDark);

    try {
        if (window.innerWidth > 768 && localStorage.getItem('cdoe_sidebar_collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }
    } catch (e) {}
});
