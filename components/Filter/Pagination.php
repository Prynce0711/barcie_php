<?php
$paginationScope = $paginationScope ?? 'default';
$paginationPageSize = $paginationPageSize ?? 10;
?>
<div class="pagination-wrapper" data-pagination data-scope="<?= htmlspecialchars($paginationScope) ?>" data-page-size="<?= (int)$paginationPageSize ?>">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted" data-pagination-info>No items</small>
        <div class="d-flex align-items-center gap-1">
            <div class="btn-group btn-group-sm" role="group" aria-label="Pagination">
                <button type="button" class="btn btn-light btn-sm" data-pagination-prev disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="btn btn-light btn-sm disabled" data-pagination-current style="min-width:70px; pointer-events:none;">
                    Page 1
                </span>
                <button type="button" class="btn btn-light btn-sm" data-pagination-next disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const scriptEl = document.currentScript;
    const root = scriptEl ? scriptEl.previousElementSibling : null;
    if (!root) return;

    const scope = root.getAttribute('data-scope') || 'default';
    const pageSize = parseInt(root.getAttribute('data-page-size'), 10) || 10;
    const prevBtn = root.querySelector('[data-pagination-prev]');
    const nextBtn = root.querySelector('[data-pagination-next]');
    const currentEl = root.querySelector('[data-pagination-current]');
    const infoEl = root.querySelector('[data-pagination-info]');

    let currentPage = 1;
    let totalItems = 0;
    let totalPages = 1;

    function dispatch() {
        document.dispatchEvent(new CustomEvent('page-changed', {
            detail: { scope: scope, page: currentPage, pageSize: pageSize, totalPages: totalPages }
        }));
    }

    function render() {
        totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;

        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= totalPages;

        currentEl.textContent = 'Page ' + currentPage + ' / ' + totalPages;

        var start = totalItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
        var end = Math.min(currentPage * pageSize, totalItems);
        infoEl.textContent = totalItems === 0 ? 'No items' : 'Showing ' + start + '–' + end + ' of ' + totalItems;
    }

    function update(total, page) {
        totalItems = total || 0;
        currentPage = page || 1;
        render();
    }

    function init() {
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) { currentPage--; render(); dispatch(); }
        });
        nextBtn.addEventListener('click', function () {
            if (currentPage < totalPages) { currentPage++; render(); dispatch(); }
        });

        document.addEventListener('filters-reset', function (e) {
            if (e.detail && e.detail.scope && e.detail.scope !== scope) return;
            currentPage = 1; render();
        });

        window.Pagination = window.Pagination || {};
        window.Pagination[scope] = {
            update: update,
            getPage: function () { return currentPage; },
            getPageSize: function () { return pageSize; },
            reset: function () { currentPage = 1; render(); dispatch(); }
        };

        render();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>