<section id="availability" class="content-section">
  <h2>Room & Facility Availability</h2>

  <div class="row mb-4" id="availability-room-list-section">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">
              <i class="fas fa-list me-2"></i>Room & Facility Availability
            </h5>
            <small class="opacity-75">Browse available rooms and facilities. Click "View Calendar" to see specific availability dates.</small>
          </div>
          <div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Filter rooms or facilities" id="availabilityFilterBtns">
              <button type="button" class="btn btn-light btn-sm active" data-filter="all">All</button>
              <button type="button" class="btn btn-light btn-sm" data-filter="room">Rooms</button>
              <button type="button" class="btn btn-light btn-sm" data-filter="facility">Facilities</button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <?php include 'components/guest/sections/calendar_room_list.php'; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include 'components/guest/sections/availability_modal.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize room list on page load
  (function() {
    const sectionEl = document.getElementById('availability');
    if (!sectionEl) return;

    // Simple availability filter state (persisted)
    (function(){
      const stored = (typeof localStorage !== 'undefined') ? localStorage.getItem('availabilityFilter') : null;
      window._availabilityFilter = window._availabilityFilter || (stored || 'all');

      // Set active button according to stored filter
      const btnGroup = document.getElementById('availabilityFilterBtns');
      if (btnGroup) {
        const btn = btnGroup.querySelector(`button[data-filter="${window._availabilityFilter}"]`);
        if (btn) {
          btnGroup.querySelectorAll('button[data-filter]').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
        }
      }
    })();

    // Initialize room list when section becomes visible
    function initIfVisible() {
      const isVisible = sectionEl.offsetParent !== null && window.getComputedStyle(sectionEl).display !== 'none';
      if (isVisible && typeof window.renderRoomFacilityList === 'function') {
        try { window.renderRoomFacilityList(window._availabilityFilter); } catch (e) { window.renderRoomFacilityList(); }
      }
    }

    // Observe attribute changes (class/style) to know when section becomes visible
    const mo = new MutationObserver(() => {
      initIfVisible();
    });
    mo.observe(sectionEl, { attributes: true, attributeFilter: ['style', 'class'] });

    // Initialize on load
    setTimeout(initIfVisible, 100);
    
    // Wire up filter buttons
    const btnGroup = document.getElementById('availabilityFilterBtns');
    if (btnGroup) {
      btnGroup.addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-filter]');
        if (!btn) return;
        const filter = btn.getAttribute('data-filter') || 'all';
        // update active state
        btnGroup.querySelectorAll('button[data-filter]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        // store and re-render (persist)
        window._availabilityFilter = filter;
        try { if (typeof localStorage !== 'undefined') localStorage.setItem('availabilityFilter', filter); } catch(e) {}
        if (typeof window.renderRoomFacilityList === 'function') {
          try { window.renderRoomFacilityList(filter); } catch (e) { window.renderRoomFacilityList(); }
        }
      });
    }
  })();
});
</script>
