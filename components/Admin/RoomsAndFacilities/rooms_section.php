<?php ob_start(); ?>
<div class="d-flex align-items-center gap-2 flex-wrap py-1">
  <?php $filterScope = 'rooms-admin';
  include __DIR__ . '/../../Filter/FilterTypes.php'; ?>
  <div class="vr d-none d-md-block" style="height:28px;"></div>
  <?php $searchScope = 'rooms';
  $searchPlaceholder = 'Search by name, room number, or description...';
  include __DIR__ . '/../../Filter/Searchbar.php'; ?>
  <div class="ms-auto d-flex align-items-center gap-2">
    <!-- Add New Button - Only for managers and super_admins -->
    <div id="add-room-button-container">
      <?php $addLabel = 'Add New';
      $addClass = 'btn-primary';
      $addSize = 'btn-sm';
      $addTarget = '#addItemModal';
      include __DIR__ . '/../../ActionButton/Add.php'; ?>
    </div>
  </div>
</div>
<?php $sectionFilters = ob_get_clean(); ?>
<?php
$sectionTitle = 'Rooms & Facilities Management';
$sectionIcon = 'fa-building';
$sectionSubtitle = 'Manage your property inventory and amenities';
include __DIR__ . '/../Shared/SectionHeader.php';
?>
<!-- Bridge: sync search component → existing search input -->
<script>
  (function () {
    function applyRoomsFilters() {
      var selectedType = 'all';
      if (window.FilterTypes && window.FilterTypes['rooms-admin'] && typeof window.FilterTypes['rooms-admin'].getFilter === 'function') {
        selectedType = window.FilterTypes['rooms-admin'].getFilter() || 'all';
      }

      var searchEl = document.getElementById('searchItems');
      var term = searchEl ? (searchEl.value || '').toLowerCase() : '';

      document.querySelectorAll('#items-container .item-card').forEach(function (card) {
        var type = (card.getAttribute('data-type') || '').toLowerCase();
        var hay = (card.getAttribute('data-searchable') || '').toLowerCase();
        var typeOk = selectedType === 'all' || type === selectedType;
        var searchOk = !term || hay.indexOf(term) !== -1;
        card.style.display = (typeOk && searchOk) ? '' : 'none';
      });

      try { if (typeof window.updateVisibleCounts === 'function') window.updateVisibleCounts(); } catch (e) { }
    }

    document.addEventListener('search-changed', function (e) {
      if (e.detail.scope !== 'rooms') return;
      var el = document.getElementById('searchItems');
      if (!el) { el = document.createElement('input'); el.type = 'hidden'; el.id = 'searchItems'; document.body.appendChild(el); }
      el.value = e.detail.value || '';
      applyRoomsFilters();
    });
    document.addEventListener('filter-changed', function (e) {
      if (e.detail.scope !== 'rooms-admin') return;
      var f = e.detail && e.detail.filter || 'all';
      var radios = document.querySelectorAll('input.type-filter[name="type_filter"]');
      radios.forEach(function (r) { if (r.value === f) r.checked = true; });
      applyRoomsFilters();
    });

    document.addEventListener('DOMContentLoaded', applyRoomsFilters);
  })();
</script>
<script>
  // Role-based access control for Rooms & Facilities
  (function () {
    const currentRole = (window.currentAdmin && window.currentAdmin.role) ? window.currentAdmin.role : 'staff';
    const addBtn = document.getElementById('add-room-button-container');
    if (addBtn && currentRole === 'staff') addBtn.style.display = 'none';
    function applyRoomsRoleRestrictions() {
      if (currentRole === 'staff') {
        document.querySelectorAll('.edit-toggle-btn, .delete-item-btn, [onclick*="deleteItem"]').forEach(btn => {
          btn.style.display = 'none';
        });
      }
    }
    applyRoomsRoleRestrictions();
    setTimeout(applyRoomsRoleRestrictions, 300);
    const itemsContainer = document.getElementById('items-container');
    if (itemsContainer) {
      const observer = new MutationObserver(applyRoomsRoleRestrictions);
      observer.observe(itemsContainer, { childList: true, subtree: true });
    }
  })();
</script>

<!-- Items Grid -->
<!-- Items Grid (wrapped to allow overlay spinner) -->
<div class="position-relative">
  <!-- Loading overlay (hidden by default) -->
  <div id="itemsLoadingOverlay" class="loading-overlay d-none" aria-hidden="true" role="status"
    aria-label="Loading items">
    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <div class="row" id="items-container">
    <?php include 'rooms_grid_content.php'; ?>
  </div>
</div>


<!-- Add New Item Modal is now included in the main layout (dashboard.php) to avoid stacking/overflow issues -->

<!-- Inline styles for the items loading overlay (kept small and local to this component) -->
<style>
  /* Ensure the wrapper is the positioning context */
  .position-relative {
    position: relative;
  }

  /* Full-area overlay that sits above the items grid */
  .loading-overlay {
    position: absolute;
    inset: 0;
    /* top:0; right:0; bottom:0; left:0 */
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.72);
    z-index: 1080;
    /* above most UI but below modals */
    backdrop-filter: blur(2px);
  }

  /* Utility to hide/show using bootstrap d-none class */
</style>

<!-- Small script to show the overlay briefly when switching type filters (All/Rooms/Facilities) -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('itemsLoadingOverlay');
    if (!overlay) return;

    // Show overlay early using capture so it appears before other handlers run
    document.querySelectorAll('.type-filter').forEach(radio => {
      radio.addEventListener('change', function () {
        // Show overlay
        overlay.classList.remove('d-none');
        overlay.setAttribute('aria-hidden', 'false');

        // Hide overlay shortly after other handlers complete.
        // Filtering in this section is synchronous, so a short timeout is sufficient.
        // If you later change to async loading, replace this with logic that hides
        // the overlay when the async request completes.
        requestAnimationFrame(() => {
          setTimeout(() => {
            overlay.classList.add('d-none');
            overlay.setAttribute('aria-hidden', 'true');
          }, 120);
        });
      }, true); // use capture to show immediately
    });
  });
</script>