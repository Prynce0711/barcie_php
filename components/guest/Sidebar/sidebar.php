<style>
  /* Sidebar scoped styles */
  .sidebar-guest {
    width: 260px;
    background: linear-gradient(180deg, #07263f 0%, #0b3a5f 100%);
    border-right: 2px solid rgba(52, 152, 219, 0.6);
    box-shadow: 0 0 15px rgba(52, 152, 219, 0.3);
    transition: all 0.3s ease;
  }

  .sidebar-guest h2 {
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
  }

  .sidebar-guest .sidebar-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .sidebar-guest .sidebar-btn:hover {
    background: rgba(52, 152, 219, 0.4);
    border-color: rgba(52, 152, 219, 0.6);
  }

  .sidebar-guest .sidebar-btn.active {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-color: #3498db;
  }

  .sidebar-guest .sidebar-logout {
    background: rgba(231, 76, 60, 0.2);
    border: 1px solid rgba(231, 76, 60, 0.4);
    color: #ffcccb;
    white-space: nowrap;
  }

  .sidebar-guest .sidebar-logout:hover {
    background: rgba(231, 76, 60, 0.4);
    border-color: #e74c3c;
    color: #fff;
  }

  /* Mobile sidebar slide */
  @media (max-width: 768px) {
    .sidebar-guest {
      width: 280px;
      left: -280px;
      overflow-y: auto;
    }

    .sidebar-guest.open {
      left: 0;
    }
  }
</style>

<nav class="sidebar-guest fixed left-0 top-0 min-h-screen z-[1000] flex flex-col text-white py-[30px]"
  aria-label="Guest portal navigation">
  <div class="px-5 mb-6">
    <h2 class="text-xl font-semibold tracking-wide text-white text-center pb-4 mb-0"><i class="fas fa-user-circle me-2"
        aria-hidden="true"></i>
      <span class="visually-hidden">Guest Portal:</span>
      <span class="d-none d-sm-inline">Guest Portal</span>
    </h2>
  </div>

  <ul class="list-unstyled px-3 flex-grow">
    <li>
      <button type="button"
        class="sidebar-btn w-full my-2 py-3.5 px-[18px] rounded-lg text-base font-medium text-white text-left block cursor-pointer"
        data-section="overview" aria-label="Overview" title="Overview">
        <i class="fas fa-home me-2" aria-hidden="true"></i>
        <span>Overview</span>
      </button>
    </li>
    <li>
      <button type="button"
        class="sidebar-btn w-full my-2 py-3.5 px-[18px] rounded-lg text-base font-medium text-white text-left block cursor-pointer"
        data-section="availability" aria-label="Availability Calendar" title="Availability Calendar">
        <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
        <span>Availability Calendar</span>
      </button>
    </li>
    <li>
      <button type="button"
        class="sidebar-btn w-full my-2 py-3.5 px-[18px] rounded-lg text-base font-medium text-white text-left block cursor-pointer"
        data-section="rooms" aria-label="Rooms and Facilities" title="Rooms & Facilities">
        <i class="fas fa-door-open me-2" aria-hidden="true"></i>
        <span>Rooms &amp; Facilities</span>
      </button>
    </li>
    <li>
      <button type="button"
        class="sidebar-btn w-full my-2 py-3.5 px-[18px] rounded-lg text-base font-medium text-white text-left block cursor-pointer"
        data-section="booking" aria-label="Booking and Reservation" title="Booking & Reservation">
        <i class="fas fa-calendar-check me-2" aria-hidden="true"></i>
        <span>Booking &amp; Reservation</span>
      </button>
    </li>
  </ul>

  <div class="px-3 mt-auto mb-5">
    <a href="index.php"
      class="sidebar-logout w-full py-3.5 px-[18px] rounded-lg text-base font-medium text-left block no-underline">
      <i class="fas fa-home me-2" aria-hidden="true"></i>
      <span>Back to Home</span>
    </a>
  </div>

  <!-- Small JS to handle keyboard and active state (non-intrusive) -->
  <script>
    (function () {
      try {
        var sidebar = document.querySelector('.sidebar-guest');
        if (!sidebar) return;
        var buttons = sidebar.querySelectorAll('.sidebar-btn');

        // Click & keyboard support for sidebar buttons
        buttons.forEach(function (btn) {
          // support Enter/Space activation
          btn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
          });

          btn.addEventListener('click', function () {
            var sec = this.getAttribute('data-section');
            // toggle active visual state
            buttons.forEach(function (b) { b.classList.remove('active'); b.removeAttribute('aria-current'); });
            this.classList.add('active');
            this.setAttribute('aria-current', 'true');
            // call existing showSection if available
            if (typeof window.showSection === 'function') {
              window.showSection(sec, this, true);
            }
          });
        });

        // If a section is activated elsewhere via JS, allow external code to set active state
        window.__setGuestSidebarActive = function (section) {
          if (!section) return;
          buttons.forEach(function (b) {
            if (b.getAttribute('data-section') === section) { b.click(); }
          });
        };
      } catch (e) { console && console.error && console.error('sidebar init error', e); }
    })();
  </script>
</nav>