<?php
$filterScope = $filterScope ?? 'default';
?>
<div class="filter-types-wrapper" data-filter-types data-scope="<?= htmlspecialchars($filterScope) ?>">
    <div class="btn-group btn-group-sm" role="group" aria-label="Filter items" data-filter-types-btns>
        <button type="button" class="btn btn-light btn-sm" data-filter="all">All</button>
        <button type="button" class="btn btn-light btn-sm" data-filter="room">Rooms</button>
        <button type="button" class="btn btn-light btn-sm" data-filter="facility">Facilities</button>
    </div>
</div>

<script>
    (function () {
        const defaultFilter = 'all';

        // Scope this script to the markup block immediately above it.
        var scriptEl = document.currentScript;
        var root = null;
        if (scriptEl) {
            var prev = scriptEl.previousElementSibling;
            for (var i = 0; i < 3 && prev; i++) {
                if (prev.hasAttribute && prev.hasAttribute('data-filter-types')) { root = prev; break; }
                prev = prev.previousElementSibling;
            }
            if (!root && scriptEl.parentNode) {
                var all = scriptEl.parentNode.querySelectorAll('[data-filter-types]');
                if (all.length) root = all[all.length - 1];
            }
        }
        const group = root ? root.querySelector('[data-filter-types-btns]') : null;
        const scope = root ? (root.getAttribute('data-scope') || 'default') : 'default';
        const STORAGE_KEY = 'availabilityFilter_' + scope;

        if (!group) return;
        function readStored() {
            try { return (typeof localStorage !== 'undefined') ? localStorage.getItem(STORAGE_KEY) : null; } catch (e) { return null; }
        }
        function writeStored(value) {
            try { if (typeof localStorage !== 'undefined') localStorage.setItem(STORAGE_KEY, value); } catch (e) { }
        }
        function setActiveButton(filter) {
            group.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            const btn = group.querySelector(`[data-filter="${filter}"]`);
            if (btn) btn.classList.add('active');
        }
        function getFilter() {
            const stored = readStored();
            return stored || defaultFilter;
        }
        function setFilter(value, notify = true) {
            writeStored(value);
            document.querySelectorAll('[data-filter-types][data-scope="' + scope + '"] [data-filter-types-btns]').forEach((groupEl) => {
                groupEl.querySelectorAll('[data-filter]').forEach((btn) => btn.classList.remove('active'));
                const active = groupEl.querySelector(`[data-filter="${value}"]`);
                if (active) active.classList.add('active');
            });

            // Backward compatibility for legacy consumers.
            if (scope === 'availability') {
                window._availabilityFilter = value;
            }

            if (notify) {
                const ev = new CustomEvent('filter-changed', { detail: { scope: scope, filter: value } });
                document.dispatchEvent(ev);
                try { if (typeof window.renderRoomFacilityList === 'function') window.renderRoomFacilityList(value); } catch (e) { }
                try { if (typeof window.renderRoomsGrid === 'function') window.renderRoomsGrid(value); } catch (e) { }
            }
        }
        function init() {
            const current = getFilter();
            window.FilterTypes = window.FilterTypes || {};
            window.FilterTypes.getFilter = function (targetScope = scope) {
                if (targetScope === scope) {
                    return getFilter();
                }
                try {
                    return localStorage.getItem('availabilityFilter_' + targetScope) || defaultFilter;
                } catch (e) {
                    return defaultFilter;
                }
            };
            window.FilterTypes.setFilter = function (value, targetScope = scope, notify = true) {
                if (targetScope === scope) {
                    setFilter(value, notify);
                    return;
                }
                try {
                    localStorage.setItem('availabilityFilter_' + targetScope, value);
                } catch (e) { }
                if (notify) {
                    document.dispatchEvent(new CustomEvent('filter-changed', { detail: { scope: targetScope, filter: value } }));
                }
            };
            window.FilterTypes[scope] = {
                getFilter: getFilter,
                setFilter: function (value, notify = true) { setFilter(value, notify); }
            };
            setActiveButton(current);
            if (scope === 'availability') {
                window._availabilityFilter = current;
            }
            group.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-filter]');
                if (!btn) return;
                const filter = btn.getAttribute('data-filter') || defaultFilter;
                setFilter(filter);
            });

            document.addEventListener('filter-changed', function (e) {
                const eventScope = e && e.detail ? (e.detail.scope || 'default') : 'default';
                if (eventScope !== scope) return;
                const selected = e && e.detail ? e.detail.filter : defaultFilter;
                setActiveButton(selected || defaultFilter);
            });

            document.addEventListener('filters-reset', function (e) {
                if (e.detail && e.detail.scope && e.detail.scope !== scope) return;
                setFilter(defaultFilter);
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
</script>