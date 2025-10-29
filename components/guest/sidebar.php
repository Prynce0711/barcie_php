<nav class="sidebar-guest" aria-label="Guest portal navigation">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="h5 mb-0"><i class="fas fa-user-circle me-2" aria-hidden="true"></i>
      <span class="visually-hidden">Guest Portal:</span>
      <span class="d-none d-sm-inline">Guest Portal</span>
    </h2>
    <!-- Mobile collapse toggle -->
    <button type="button" class="btn btn-outline-light sidebar-toggle d-sm-none" aria-expanded="true" aria-label="Toggle guest navigation">
      <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
  </div>

  <ul class="list-unstyled mb-2">
    <li>
      <button type="button" class="btn btn-outline-light mb-2 text-start w-100 sidebar-btn" data-section="overview" aria-label="Overview" title="Overview">
        <i class="fas fa-home me-2" aria-hidden="true"></i>
        <span>Overview</span>
      </button>
    </li>
    <li>
      <button type="button" class="btn btn-outline-light mb-2 text-start w-100 sidebar-btn" data-section="availability" aria-label="Availability Calendar" title="Availability Calendar">
        <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
        <span>Availability Calendar</span>
      </button>
    </li>
    <li>
      <button type="button" class="btn btn-outline-light mb-2 text-start w-100 sidebar-btn" data-section="rooms" aria-label="Rooms and Facilities" title="Rooms & Facilities">
        <i class="fas fa-door-open me-2" aria-hidden="true"></i>
        <span>Rooms &amp; Facilities</span>
      </button>
    </li>
    <li>
      <button type="button" class="btn btn-outline-light mb-2 text-start w-100 sidebar-btn" data-section="booking" aria-label="Booking and Reservation" title="Booking & Reservation">
        <i class="fas fa-calendar-check me-2" aria-hidden="true"></i>
        <span>Booking &amp; Reservation</span>
      </button>
    </li>
    <li>
      <button type="button" class="btn btn-outline-light mb-2 text-start w-100 sidebar-btn" data-section="feedback" aria-label="Feedback" title="Feedback">
        <i class="fas fa-star me-2" aria-hidden="true"></i>
        <span>Feedback</span>
      </button>
    </li>
  </ul>

  <a href="index.php" class="btn btn-primary mt-3 text-start w-100">
    <i class="fas fa-home me-2" aria-hidden="true"></i>
    <span>Back to Home</span>
  </a>

  <!-- Small JS to handle the mobile toggle, keyboard and active state (non-intrusive) -->
  <script>
    (function(){
      try {
        var sidebar = document.querySelector('.sidebar-guest');
        if(!sidebar) return;
        var toggle = sidebar.querySelector('.sidebar-toggle');
        var buttons = sidebar.querySelectorAll('.sidebar-btn');

        // Mobile collapse toggle
        if(toggle){
          toggle.addEventListener('click', function(){
            var collapsed = sidebar.classList.toggle('collapsed');
            this.setAttribute('aria-expanded', (!collapsed).toString());
          });
        }

        // Click & keyboard support for sidebar buttons
        buttons.forEach(function(btn){
          // support Enter/Space activation
          btn.addEventListener('keydown', function(e){
            if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); this.click(); }
          });

          btn.addEventListener('click', function(){
            var sec = this.getAttribute('data-section');
            // toggle active visual state
            buttons.forEach(function(b){ b.classList.remove('active'); b.removeAttribute('aria-current'); });
            this.classList.add('active');
            this.setAttribute('aria-current', 'true');
            // call existing showSection if available
            if(typeof window.showSection === 'function'){
              window.showSection(sec);
            }
          });
        });

        // If a section is activated elsewhere via JS, allow external code to set active state
        window.__setGuestSidebarActive = function(section){
          if(!section) return;
          buttons.forEach(function(b){
            if(b.getAttribute('data-section') === section){ b.click(); }
          });
        };
      } catch(e){ console && console.error && console.error('sidebar init error', e); }
    })();
  </script>
</nav>
