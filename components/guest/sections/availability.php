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

    // Initialize room list when section becomes visible
    function initIfVisible() {
      const isVisible = sectionEl.offsetParent !== null && window.getComputedStyle(sectionEl).display !== 'none';
      if (isVisible && typeof window.renderRoomFacilityList === 'function') {
        window.renderRoomFacilityList();
      }
    }

    // Observe attribute changes (class/style) to know when section becomes visible
    const mo = new MutationObserver(() => {
      initIfVisible();
    });
    mo.observe(sectionEl, { attributes: true, attributeFilter: ['style', 'class'] });

    // Initialize on load
    setTimeout(initIfVisible, 100);
  })();
});
</script>
