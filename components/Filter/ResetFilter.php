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
    const scriptEl = document.currentScript;
    const root = scriptEl ? scriptEl.previousElementSibling : null;
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