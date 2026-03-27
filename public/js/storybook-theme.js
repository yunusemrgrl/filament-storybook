(function () {
    var storageKey = 'theme';
    var rootSelector = '[data-storybook-theme-root]';
    var buttonSelector = '[data-storybook-theme-button]';
    var valueSelector = '[data-storybook-theme-value]';

    function normalizeTheme(theme) {
        return theme === 'dark' ? 'dark' : 'light';
    }

    function getStoredTheme() {
        try {
            var storedTheme = window.localStorage.getItem(storageKey);

            return storedTheme === 'dark' ? 'dark' : storedTheme === 'light' ? 'light' : null;
        } catch (error) {
            return null;
        }
    }

    function setStoredTheme(theme) {
        try {
            window.localStorage.setItem(storageKey, theme);
        } catch (error) {
            return;
        }
    }

    function updateRoot(root, theme) {
        root.classList.toggle('is-dark', theme === 'dark');

        var value = root.querySelector(valueSelector);

        if (value) {
            value.textContent = theme === 'dark' ? 'Dark' : 'Light';
        }

        root.querySelectorAll(buttonSelector).forEach(function (button) {
            var isActive = button.dataset.storybookThemeButton === theme;

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function applyTheme(theme) {
        var normalizedTheme = normalizeTheme(theme);

        document.documentElement.classList.toggle('dark', normalizedTheme === 'dark');
        setStoredTheme(normalizedTheme);

        document.querySelectorAll(rootSelector).forEach(function (root) {
            updateRoot(root, normalizedTheme);
        });
    }

    function syncTheme() {
        var root = document.querySelector(rootSelector);

        if (! root) {
            return;
        }

        var defaultTheme = normalizeTheme(root.dataset.storybookThemeDefault);
        var theme = getStoredTheme() || defaultTheme;

        applyTheme(theme);
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest(buttonSelector);

        if (! button) {
            return;
        }

        event.preventDefault();

        applyTheme(button.dataset.storybookThemeButton);
    });

    document.addEventListener('livewire:navigated', syncTheme);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncTheme, { once: true });
    } else {
        syncTheme();
    }
})();
