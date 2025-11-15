<section id="availability" class="content-section">
  <h2>Room & Facility Availability</h2>

  <div class="row mb-4" id="availability-calendar-section">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">
              <i class="fas fa-calendar-alt me-2"></i>Availability Calendar
            </h5>
            <small class="opacity-75">View room and facility availability for planning your stay</small>
          </div>
          <div class="d-flex align-items-center">
            <div id="guestCalendarControls" class="btn-group me-3" role="group" aria-label="Calendar controls">
              <button id="calPrev" type="button" class="btn btn-sm btn-light" title="Previous"><i class="fas fa-chevron-left"></i></button>
              <button id="calToday" type="button" class="btn btn-sm btn-light" title="Today">Today</button>
              <button id="calNext" type="button" class="btn btn-sm btn-light" title="Next"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="btn-group me-3" role="group" aria-label="View Toggle">
              <button id="btnCalendarView" type="button" class="btn btn-sm btn-outline-light active">
                <i class="fas fa-calendar-alt me-1"></i>Calendar View
              </button>
              <button id="btnRoomList" type="button" class="btn btn-sm btn-outline-light">
                <i class="fas fa-list me-1"></i>Room List
              </button>
            </div>

            <div class="ms-3 text-white-50" id="calendarTitle" style="min-width: 220px; text-align: right;"></div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-8">
              <div id="guestCalendar" style="min-height: 300px;"></div>
              <?php include 'components/guest/sections/calendar_room_list.php'; ?>
            </div>
            <div class="col-md-4">
              <div class="availability-legend">
                <h6 class="mb-3">Availability Legend</h6>
                <div class="d-flex align-items-center mb-2">
                  <div class="legend-color bg-success me-2"></div>
                  <small>Available</small>
                </div>
                <div class="d-flex align-items-center mb-2">
                  <div class="legend-color bg-warning me-2"></div>
                  <small>Pending Booking</small>
                </div>
                <div class="d-flex align-items-center mb-2">
                  <div class="legend-color bg-danger me-2"></div>
                  <small>Occupied</small>
                </div>
                <div class="d-flex align-items-center mb-3">
                  <div class="legend-color bg-info me-2"></div>
                  <small>Checked In</small>
                </div>
                <div class="availability-info mt-3">
                  <h6>ℹ️ Information</h6>
                  <small class="text-muted">
                    This calendar shows room/facility availability only.
                    Hover over events to see specific room details.
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include 'components/guest/sections/availability_modal.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  (function() {
    let calendarInitialized = false;
    let calendarInstance = null;
    
    // Calculate a sensible calendar height based on available viewport space
    function calcGuestCalendarHeight() {
      try {
        const availSection = document.getElementById('availability');
        const top = availSection ? availSection.getBoundingClientRect().top : 100;
        // Reserve space for header, footer, paddings. Clamp between 300 and 800px.
        const space = Math.floor(window.innerHeight - top - 140);
        return Math.max(300, Math.min(800, space));
      } catch (e) {
        return 400; // fallback
      }
    }

    async function fetchEvents(fetchInfo, successCallback, failureCallback) {
      try {
        const res = await fetch('api/availability.php');
        if (!res.ok) throw new Error('Network response not ok');
        const data = await res.json();
        if (Array.isArray(data)) return successCallback(data);
        if (data && data.success && Array.isArray(data.events)) return successCallback(data.events);
        return successCallback([]);
      } catch (err) {
        console.error('Calendar: fetch events error', err);
        failureCallback(err);
      }
    }

    function createCalendar() {
      const calendarEl = document.getElementById('guestCalendar');
      if (!calendarEl) return null;
      if (typeof FullCalendar === 'undefined') {
        console.warn('FullCalendar not loaded yet');
        return null;
      }

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        timeZone: 'Asia/Manila',
        headerToolbar: false, // use custom header controls in the card header
        height: calcGuestCalendarHeight(),
        events: fetchEvents,
        eventDataTransform: function(rawEvent) {
          try {
            const e = Object.assign({}, rawEvent);
            const props = e.extendedProps || {};
            let status = (props.booking_status || props.status || e.status || e.booking_status || '').toString().toLowerCase();
            const greenStatuses = ['available','free','vacant','open'];
            const redStatuses = ['booked','occupied','reserved','pending','unavailable'];
            // Only override if no explicit colors provided
            if (!e.backgroundColor && !e.color) {
              if (greenStatuses.indexOf(status) !== -1) {
                e.backgroundColor = '#d4edda';
                e.borderColor = '#c3e6cb';
                e.textColor = '#ffffff';
              } else if (redStatuses.indexOf(status) !== -1) {
                e.backgroundColor = '#f8d7da';
                e.borderColor = '#f5c6cb';
                e.textColor = '#ffffff';
              } else {
                const hint = (props.booking_status || props.booking || props.occupied || '').toString().toLowerCase();
                if (greenStatuses.indexOf(hint) !== -1) {
                  e.backgroundColor = '#d4edda';
                  e.borderColor = '#c3e6cb';
                  e.textColor = '#ffffff';
                } else if (redStatuses.indexOf(hint) !== -1) {
                  e.backgroundColor = '#f8d7da';
                  e.borderColor = '#f5c6cb';
                  e.textColor = '#ffffff';
                }
              }
            }
            return e;
          } catch (err) {
            return rawEvent;
          }
        },
        eventDisplay: 'block',
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        locale: 'en',
        firstDay: 1,
        nowIndicator: true,
        eventTextColor: '#ffffff',
        eventBorderColor: 'transparent',
        eventMouseEnter: function(info) {
          try {
            const { extendedProps } = info.event;
            const startDate = new Date(extendedProps.checkin_date || info.event.start);
            const endDate = new Date(extendedProps.checkout_date || info.event.end);
            const duration = extendedProps.duration_days || 1;
            const roomName = extendedProps.facility || 'Room/Facility';
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.innerHTML = `
              <strong><i class="fas fa-bed me-1"></i>${roomName}</strong><br>
              <small><i class="fas fa-calendar me-1"></i>Check-in: ${startDate.toLocaleDateString()}</small><br>
              <small><i class="fas fa-calendar-check me-1"></i>Check-out: ${endDate.toLocaleDateString()}</small><br>
              <small><i class="fas fa-clock me-1"></i>Duration: ${duration} day${duration > 1 ? 's' : ''}</small><br>
              <small><i class="fas fa-info-circle me-1"></i>Status: ${extendedProps.booking_status || 'Occupied'}</small>
            `;
            tooltip.style.cssText = 'position:absolute;background:#333;color:#fff;padding:8px 10px;border-radius:6px;font-size:12px;z-index:10000;pointer-events:none;';
            document.body.appendChild(tooltip);
            const rect = info.el.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            info.el.tooltip = tooltip;
          } catch (e) { console.error(e); }
        },
        eventMouseLeave: function(info) {
          if (info.el && info.el.tooltip) {
            document.body.removeChild(info.el.tooltip);
            info.el.tooltip = null;
          }
        },
        eventClick: function(info) {
          const { extendedProps } = info.event;
          const facility = extendedProps.facility || 'Room/Facility';
          const duration = extendedProps.duration_days || 1;
          const status = extendedProps.booking_status || 'occupied';
          const checkin = new Date(extendedProps.checkin_date || info.event.start).toLocaleDateString();
          const checkout = new Date(extendedProps.checkout_date || info.event.end).toLocaleDateString();
          const statusText = status === 'pending' ? 'has a pending booking' : 'is currently occupied';
          const message = `<strong>${facility}</strong> ${statusText} from <strong>${checkin}</strong> to <strong>${checkout}</strong> (${duration} day${duration > 1 ? 's' : ''}).`;
          if (typeof showDetailedToast === 'function') showDetailedToast(message, 'info', facility);
        }
        ,
        // show room/facility prominently in list views
        eventContent: function(arg) {
          try {
            const ext = arg.event.extendedProps || {};
            const title = arg.event.title || '';

            // prefer explicit facility name from event
            let facility = ext.facility || ext.room_name || ext.room || '';

            // If facility is missing, try to lookup from the rooms DOM by id
            if (!facility) {
              const possibleIds = [ext.room_id, ext.item_id, ext.id, ext.booking_item_id];
              for (let id of possibleIds) {
                if (!id) continue;
                // try multiple selectors where templates may set the id
                const selCandidates = [
                  `[data-room-id="${id}"] .room-title`,
                  `[data-item-id="${id}"] .room-title`,
                  `#cards-grid [data-room-id="${id}"] .room-title`,
                  `#cards-grid [data-item-id="${id}"] .room-title`
                ];
                for (let sel of selCandidates) {
                  const el = document.querySelector(sel);
                  if (el && el.textContent.trim()) {
                    facility = el.textContent.trim();
                    break;
                  }
                }
                if (facility) break;
              }
            }

            if (arg.view && arg.view.type && arg.view.type.startsWith('list')) {
              const display = facility ? `${facility} — ${title}` : title;
              const html = `<div class="fc-event-title"><strong>${display}</strong></div>`;
              return { html };
            }
            return null; // default rendering for other views
          } catch (e) { console.error(e); return null; }
        },
        datesSet: function(info) {
          const titleEl = document.getElementById('calendarTitle');
          if (titleEl) titleEl.textContent = info.view.title || '';
        }
      });

      // Ensure calendar weekday headers and event text inside main calendar are white for contrast
      const styleEl = document.createElement('style');
      styleEl.textContent = `
        /* Event text */
        #guestCalendar .fc .fc-event, #guestCalendar .fc .fc-event-main, #guestCalendar .fc .fc-event-title { color: #ffffff !important; }
        /* Weekday header labels only (Mon, Tue, Wed, ...). Do not change day numbers. */
        #guestCalendar .fc-col-header-cell .fc-col-header-cell-cushion, #guestCalendar .fc-col-header-cell { color: #ffffff !important; }
      `;
      document.head.appendChild(styleEl);

      return calendar;
    }

    function initIfVisible() {
      const section = document.getElementById('availability');
      if (!section) return;
      const rect = section.getBoundingClientRect();
      const isVisible = section.offsetParent !== null && window.getComputedStyle(section).display !== 'none';
      if (isVisible) {
        if (!calendarInitialized) {
          const cal = createCalendar();
          if (cal) {
            cal.render();
            window.guestCalendar = cal;
            calendarInstance = cal;
            calendarInitialized = true;
            // Ensure height is correct right after render
            try { calendarInstance.setOption('height', calcGuestCalendarHeight()); } catch(e) { /* ignore */ }
          }
        } else if (calendarInstance) {
          try { calendarInstance.updateSize(); } catch (e) { console.warn(e); }
        }
      }
    }

    // Observe attribute changes (class/style) to know when section becomes visible
    const sectionEl = document.getElementById('availability');
    if (sectionEl) {
      const mo = new MutationObserver(() => {
        initIfVisible();
      });
      mo.observe(sectionEl, { attributes: true, attributeFilter: ['style', 'class'] });
    }

    // Small delay to initialize if already visible on load
    setTimeout(initIfVisible, 60);

    // Expose function globally (so other scripts can trigger if needed)
    window.initializeGuestCalendar = function() {
      if (!calendarInitialized) initIfVisible();
      else if (window.guestCalendar) window.guestCalendar.updateSize();
    };
    
    // Wire up custom header controls once DOM is ready
    function wireCalendarControls() {
      const prev = document.getElementById('calPrev');
      const next = document.getElementById('calNext');
      const today = document.getElementById('calToday');

      function safeAction(fn) { try { if (window.guestCalendar) fn(window.guestCalendar); } catch (e) { console.error(e); } }

      if (prev) prev.addEventListener('click', () => safeAction(c => c.prev()));
      if (next) next.addEventListener('click', () => safeAction(c => c.next()));
      if (today) today.addEventListener('click', () => safeAction(c => { c.today(); const titleEl = document.getElementById('calendarTitle'); if (titleEl) titleEl.textContent = c.view.title; }));
    }

    // small delay to ensure controls exist before wiring
    setTimeout(wireCalendarControls, 100);

    // Keep calendar height responsive on window resize
    window.addEventListener('resize', function() {
      try {
        if (window.guestCalendar) {
          window.guestCalendar.setOption('height', calcGuestCalendarHeight());
          window.guestCalendar.updateSize();
        }
      } catch (e) { /* ignore */ }
    });

    // View Toggle Functions
    function switchToCalendarView() {
      document.getElementById('guestCalendar').style.display = 'block';
      document.getElementById('roomFacilityList').style.display = 'none';
      document.getElementById('guestCalendarControls').style.display = 'flex';
      document.getElementById('btnCalendarView').classList.add('active');
      document.getElementById('btnRoomList').classList.remove('active');
      
      if (window.guestCalendar) {
        try { window.guestCalendar.updateSize(); } catch(e){}
      }
    }

    function switchToRoomList() {
      document.getElementById('guestCalendar').style.display = 'none';
      document.getElementById('roomFacilityList').style.display = 'block';
      document.getElementById('guestCalendarControls').style.display = 'none';
      document.getElementById('btnCalendarView').classList.remove('active');
      document.getElementById('btnRoomList').classList.add('active');
      
      if (typeof window.renderRoomFacilityList === 'function') {
        window.renderRoomFacilityList();
      }
    }

    // Wire up view toggle buttons
    setTimeout(() => {
      const btnCalendarView = document.getElementById('btnCalendarView');
      const btnRoomList = document.getElementById('btnRoomList');
      
      if (btnCalendarView) {
        btnCalendarView.addEventListener('click', switchToCalendarView);
      }
      
      if (btnRoomList) {
        btnRoomList.addEventListener('click', switchToRoomList);
      }
    }, 150);

    // Expose functions globally
    window.switchToCalendarView = switchToCalendarView;
    window.switchToRoomList = switchToRoomList;
  })();
});
</script>
