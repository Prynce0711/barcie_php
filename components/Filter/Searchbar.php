<?php
$searchScope = $searchScope ?? 'default';
$searchPlaceholder = $searchPlaceholder ?? 'Search...';
?>
<div class="searchbar-wrapper" data-searchbar data-scope="<?= htmlspecialchars($searchScope) ?>">
    <div class="input-group input-group-sm">
        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
        <input type="text" class="form-control border-start-0" data-search-input
            placeholder="<?= htmlspecialchars($searchPlaceholder) ?>"
            aria-label="<?= htmlspecialchars($searchPlaceholder) ?>">
    </div>
</div>

<script>
(function () {
    var scriptEl = document.currentScript;
    var root = null;
    if (scriptEl) {
        var prev = scriptEl.previousElementSibling;
        for (var i = 0; i < 3 && prev; i++) {
            if (prev.hasAttribute && prev.hasAttribute('data-searchbar')) { root = prev; break; }
            prev = prev.previousElementSibling;
        }
        if (!root && scriptEl.parentNode) {
            var all = scriptEl.parentNode.querySelectorAll('[data-searchbar]');
            if (all.length) root = all[all.length - 1];
        }
    }
    if (!root) return;

    const scope = root.getAttribute('data-scope') || 'default';
    const input = root.querySelector('[data-search-input]');
    if (!input) return;

    const STORAGE_KEY = 'searchFilter_' + scope;
    let debounceTimer = null;

    function readStored() {
        try { return localStorage.getItem(STORAGE_KEY) || ''; } catch (e) { return ''; }
    }
    function writeStored(v) {
        try { localStorage.setItem(STORAGE_KEY, v); } catch (e) { }
    }

    function dispatch(value) {
        writeStored(value);
        document.dispatchEvent(new CustomEvent('search-changed', {
            detail: { scope: scope, value: value }
        }));
    }

    function init() {
        const stored = readStored();
        if (stored) { input.value = stored; dispatch(stored); }

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { dispatch(input.value.trim()); }, 300);
        });

        document.addEventListener('filters-reset', function (e) {
            if (e.detail && e.detail.scope && e.detail.scope !== scope) return;
            input.value = '';
            dispatch('');
        });

        window.Searchbar = window.Searchbar || {};
        window.Searchbar[scope] = {
            getValue: function () { return input.value.trim(); },
            setValue: function (v) { input.value = v; dispatch(v); },
            clear: function () { input.value = ''; writeStored(''); }
        };
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>