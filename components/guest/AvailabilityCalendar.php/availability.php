<style>
  /* Availability scoped styles */
  .legend-color {
    width: 15px;
    height: 15px;
    border-radius: 3px;
    display: inline-block;
  }

  .availability-legend {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
  }

  @media (max-width: 768px) {
    #guestCalendar {
      min-height: 250px !important;
    }

    .availability-legend {
      margin-top: 1rem;
    }

    .fc-toolbar-title {
      font-size: 1.2em !important;
    }
  }
</style>

<section id="availability"
  class="content-section bg-white/95 border-2 border-[rgba(52,152,219,0.2)] p-[30px] mb-[30px] rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] relative z-[1]">
  <h2 class="mb-3">Room & Facility Availability</h2>

  <!-- Filters Bar -->
  <div class="card mb-3 border-0 bg-light">
    <div class="card-body py-2 px-3">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <?php include __DIR__ . '/../../Filter/FilterTypes.php'; ?>
        <div class="vr d-none d-md-block" style="height:28px;"></div>
        <?php $searchScope = 'availability'; $searchPlaceholder = 'Search rooms & facilities...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/calendar_room_list.php'; ?>

</section>
<?php include __DIR__ . '/availability_modal.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    (function () {
      const sectionEl = document.getElementById('availability');
      if (!sectionEl) return;

      function initIfVisible() {
        const isVisible = sectionEl.offsetParent !== null && window.getComputedStyle(sectionEl).display !== 'none';
        if (isVisible && typeof window.renderRoomFacilityList === 'function') {
          try { window.renderRoomFacilityList(window._availabilityFilter); } catch (e) { window.renderRoomFacilityList(); }
        }
      }

      const mo = new MutationObserver(() => {
        initIfVisible();
      });
      mo.observe(sectionEl, { attributes: true, attributeFilter: ['style', 'class'] });

      setTimeout(initIfVisible, 100);

    })();
  });
</script>