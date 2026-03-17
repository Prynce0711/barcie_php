<?php
$resetScope = $resetScope ?? 'default';
?>
<div class="reset-filter-wrapper" data-reset-filter data-scope="<?= htmlspecialchars($resetScope) ?>">
    <button type="button" class="btn btn-light btn-sm" data-reset-btn>
        <i class="fas fa-redo me-1"></i>Reset
    </button>
</div>

<script>
(function () {
    var scriptEl = document.currentScript;
    var root = null;
    if (scriptEl) {
        var prev = scriptEl.previousElementSibling;
        for (var i = 0; i < 3 && prev; i++) {
            if (prev.hasAttribute && prev.hasAttribute('data-reset-filter')) { root = prev; break; }
            prev = prev.previousElementSibling;
        }
        if (!root && scriptEl.parentNode) {
            var all = scriptEl.parentNode.querySelectorAll('[data-reset-filter]');
            if (all.length) root = all[all.length - 1];
        }
    }
    if (!root) return;

    const scope = root.getAttribute('data-scope') || 'default';
    const btn = root.querySelector('[data-reset-btn]');
    if (!btn) return;

    function reset() {
        document.dispatchEvent(new CustomEvent('filters-reset', {
            detail: { scope: scope }
        }));
    }

    function init() {
        btn.addEventListener('click', reset);

        window.ResetFilter = window.ResetFilter || {};
        window.ResetFilter[scope] = { reset: reset };
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>