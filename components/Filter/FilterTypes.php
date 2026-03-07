<div id="filterTypesWrapper">
    <div class="btn-group btn-group-sm" role="group" aria-label="Filter items" id="filterTypesBtns">
        <button type="button" class="btn btn-light btn-sm" data-filter="all">All</button>
        <button type="button" class="btn btn-light btn-sm" data-filter="room">Rooms</button>
        <button type="button" class="btn btn-light btn-sm" data-filter="facility">Facilities</button>
    </div>
</div>

<script>
    (function () {
        const STORAGE_KEY = 'availabilityFilter';
        const wrapperId = 'filterTypesBtns';
        const defaultFilter = 'all';

        function readStored() {
            try { return (typeof localStorage !== 'undefined') ? localStorage.getItem(STORAGE_KEY) : null; } catch (e) { return null; }
        }
        function writeStored(value) {
            try { if (typeof localStorage !== 'undefined') localStorage.setItem(STORAGE_KEY, value); } catch (e) { }
        }

        function setActiveButton(filter) {
            const group = document.getElementById(wrapperId);
            if (!group) return;
            group.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            const btn = group.querySelector(`[data-filter="${filter}"]`);
            if (btn) btn.classList.add('active');
        }

        function getFilter() {
            const stored = readStored();
            return stored || defaultFilter;
        }

        function setFilter(value, notify = true) {
            window._availabilityFilter = value;
            setActiveButton(value);
            writeStored(value);
            if (notify) {
                const ev = new CustomEvent('filter-changed', { detail: { filter: value } });
                document.dispatchEvent(ev);
                // Compatibility: call known global render functions if present
                try { if (typeof window.renderRoomFacilityList === 'function') window.renderRoomFacilityList(value); } catch (e) { }
                try { if (typeof window.renderRoomsGrid === 'function') window.renderRoomsGrid(value); } catch (e) { }
            }
        }

        function init() {
            const current = getFilter();
            // expose API
            window.FilterTypes = window.FilterTypes || {};
            window.FilterTypes.getFilter = getFilter;
            window.FilterTypes.setFilter = setFilter;

            setActiveButton(current);
            // set global variable for backward-compat
            window._availabilityFilter = current;

            const group = document.getElementById(wrapperId);
            if (!group) return;

            group.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-filter]');
                if (!btn) return;
                const filter = btn.getAttribute('data-filter') || defaultFilter;
                setFilter(filter);
            });
        }

        // Initialize when DOM ready (if included early)
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
</script>