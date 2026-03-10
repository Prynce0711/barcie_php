<?php
$dateScope = $dateScope ?? 'default';
$dateShowRange = $dateShowRange ?? false;
?>
<div class="date-filter-wrapper" data-date-filter data-scope="<?= htmlspecialchars($dateScope) ?>">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="input-group input-group-sm" style="max-width:170px;">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
            <input type="date" class="form-control border-start-0" data-date-from aria-label="Date from">
        </div>
        <?php if ($dateShowRange): ?>
        <span class="text-muted small">to</span>
        <div class="input-group input-group-sm" style="max-width:170px;">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
            <input type="date" class="form-control border-start-0" data-date-to aria-label="Date to">
        </div>
        <?php endif; ?>
        <div class="btn-group btn-group-sm" role="group" aria-label="Quick date">
            <button type="button" class="btn btn-light btn-sm" data-date-today>
                <i class="fas fa-calendar-day me-1"></i>Today
            </button>
            <button type="button" class="btn btn-light btn-sm" data-date-clear>
                <i class="fas fa-calendar me-1"></i>All
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const scriptEl = document.currentScript;
    const root = scriptEl ? scriptEl.previousElementSibling : null;
    if (!root) return;

    const scope = root.getAttribute('data-scope') || 'default';
    const dateFrom = root.querySelector('[data-date-from]');
    const dateTo = root.querySelector('[data-date-to]');
    const todayBtn = root.querySelector('[data-date-today]');
    const clearBtn = root.querySelector('[data-date-clear]');

    const STORAGE_FROM = 'dateFilter_from_' + scope;
    const STORAGE_TO = 'dateFilter_to_' + scope;

    function todayStr() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function readStored(key) {
        try { return localStorage.getItem(key) || ''; } catch (e) { return ''; }
    }
    function writeStored(key, v) {
        try { localStorage.setItem(key, v); } catch (e) { }
    }

    function getValues() {
        return { from: dateFrom ? dateFrom.value : '', to: dateTo ? dateTo.value : '' };
    }

    function dispatch() {
        var vals = getValues();
        writeStored(STORAGE_FROM, vals.from);
        writeStored(STORAGE_TO, vals.to);

        todayBtn.classList.toggle('active', vals.from === todayStr() && !vals.to);
        clearBtn.classList.toggle('active', !vals.from && !vals.to);

        document.dispatchEvent(new CustomEvent('date-filter-changed', {
            detail: { scope: scope, from: vals.from, to: vals.to }
        }));
    }

    function setToday() {
        var t = todayStr();
        if (dateFrom) dateFrom.value = t;
        if (dateTo) dateTo.value = '';
        dispatch();
    }

    function clearDates() {
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';
        dispatch();
    }

    function init() {
        if (dateFrom) dateFrom.addEventListener('change', dispatch);
        if (dateTo) dateTo.addEventListener('change', dispatch);
        todayBtn.addEventListener('click', setToday);
        clearBtn.addEventListener('click', clearDates);

        // Mark "All" as active by default
        clearBtn.classList.add('active');

        document.addEventListener('filters-reset', function (e) {
            if (e.detail && e.detail.scope && e.detail.scope !== scope) return;
            clearDates();
        });

        window.DateFilter = window.DateFilter || {};
        window.DateFilter[scope] = {
            getValues: getValues,
            setToday: setToday,
            clear: clearDates
        };
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>