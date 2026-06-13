(function () {
    const STORAGE_KEY = 'quizarena-theme';

    function getTheme() {
        var t = document.documentElement.getAttribute('data-theme');
        if (t === 'light' || t === 'dark') return t;
        return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }

    function updateToggleButtons(theme) {
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            var isLight = theme === 'light';
            btn.textContent = isLight ? '🌙' : '☀️';
            btn.setAttribute('aria-label', isLight ? 'Switch to dark mode' : 'Switch to light mode');
            btn.setAttribute('title', isLight ? 'Dark mode' : 'Light mode');
        });
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {}
        updateToggleButtons(theme);
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateToggleButtons(getTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTheme(getTheme() === 'light' ? 'dark' : 'light');
            });
        });
    });
})();
