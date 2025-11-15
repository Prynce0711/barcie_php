<!-- Availability Modal (room/facility-specific calendar) -->
<div class="modal fade" id="roomCalendarModal" tabindex="-1" aria-labelledby="roomCalendarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width:720px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roomCalendarModalLabel">Room Calendar</h5>
        <div class="btn-group btn-group-sm ms-3 me-2" role="group" aria-label="Modal navigation">
          <button type="button" class="btn btn-outline-secondary" title="Previous" onclick="if(window.roomCalendarPrev) roomCalendarPrev();">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button type="button" class="btn btn-outline-secondary" title="Today" onclick="if(window.roomCalendarToday) roomCalendarToday();">Today</button>
          <button type="button" class="btn btn-outline-secondary" title="Next" onclick="if(window.roomCalendarNext) roomCalendarNext();">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="roomCalendarInner" style="min-height: 220px; position:relative;">
          <div id="roomCalendarLegend" style="margin-bottom:8px;display:flex;gap:12px;align-items:center;">
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:14px;height:14px;background:#d4edda;border:1px solid #c3e6cb;border-radius:3px;"></div>
              <small class="text-muted">Available</small>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:14px;height:14px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:3px;"></div>
              <small class="text-muted">Booked / Reserved</small>
            </div>
          </div>
          <div id="roomCalendarMount"></div>
          <div id="roomCalendarLoading" style="position:absolute;left:0;right:0;top:0;bottom:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.85);z-index:50;">
            <div class="text-center text-muted">
              <div class="spinner-border text-secondary" role="status" style="width:2rem;height:2rem"></div>
              <div class="mt-2">Loading schedule…</div>
            </div>
          </div>
        </div>
        <style>
          /* Ensure calendar header and event text inside modal are readable (white) */
          #roomCalendarMount .fc-col-header-cell, #roomCalendarMount .fc-col-header-cell .fc-col-header-cell-cushion {
            color: #ffffff !important;
          }
          #roomCalendarMount .fc .fc-event, #roomCalendarMount .fc .fc-event-main, #roomCalendarMount .fc .fc-event-title {
            color: #ffffff !important;
          }
        </style>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  let roomCalendar = null;
  let currentItemId = null;

  function fetchAndFilterEvents(itemId, fetchInfo, successCallback, failureCallback) {
    // Try to fetch events for item from API with query param
    const url = 'api/availability.php' + (itemId ? ('?item_id=' + encodeURIComponent(itemId)) : '');
    fetch(url).then(r => r.json()).then(data => {
      let events = [];
      if (Array.isArray(data)) events = data;
      else if (data && data.success && Array.isArray(data.events)) events = data.events;
      else if (data && Array.isArray(data.data)) events = data.data;

      // If API didn't filter, apply client-side filter
      if (itemId) {
        events = events.filter(ev => {
          const p = ev.extendedProps || {};
          return p.item_id == itemId || p.room_id == itemId || ev.id == itemId || p.booking_item_id == itemId;
        });
      }

      successCallback(events);
    }).catch(err => {
      console.error('Room modal: failed fetch events', err);
      failureCallback(err);
    });
  }

    function calcCalendarHeight() {
      const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
      // Use ~50% of viewport but clamp between 240 and 520
      return Math.max(240, Math.min(520, Math.floor(vh * 0.5)));
    }

    function initRoomCalendar(itemId) {
    const container = document.getElementById('roomCalendarInner');
    if (!container) return;

    // destroy previous calendar
    if (roomCalendar) {
      try { roomCalendar.destroy(); } catch(e){}
      roomCalendar = null;
    }

    const mount = document.getElementById('roomCalendarMount');
    const loading = document.getElementById('roomCalendarLoading');
    if (!mount) return;

    if (typeof FullCalendar === 'undefined') {
      mount.innerHTML = '<div class="text-center text-muted p-3">FullCalendar not loaded</div>';
      if (loading) loading.style.display = 'none';
      return;
    }

    // Create and render calendar once so UI appears instantly
    if (!roomCalendar) {
      roomCalendar = new FullCalendar.Calendar(mount, {
        initialView: 'dayGridMonth',
        headerToolbar: false,
        timeZone: 'Asia/Manila',
        nowIndicator: true,
        eventDisplay: 'block',
        locale: 'en',
        firstDay: 1,
        height: calcCalendarHeight(),
        events: []
      });
      try { roomCalendar.render(); } catch(e) { console.warn('render error', e); }

      // keep calendar responsive while modal is open
      window.addEventListener('resize', function onRCResize() {
        try {
          if (roomCalendar) {
            roomCalendar.setOption('height', calcCalendarHeight());
            roomCalendar.updateSize();
          }
        } catch (e) { }
      });
    }

    // show loading overlay while fetching
    if (loading) loading.style.display = 'flex';

    // fetch events and populate calendar (non-blocking)
    fetchAndFilterEvents(itemId, null, function(events) {
      try {
        // remove existing events quickly
        try { roomCalendar.removeAllEvents(); } catch(e){}
        if (Array.isArray(events)) {
          for (const ev of events) {
            try {
              // clone event and apply color mapping based on booking status
              const e = Object.assign({}, ev);
              const props = e.extendedProps || {};
              let status = (props.booking_status || props.status || e.status || e.booking_status || '').toString().toLowerCase();

              const greenStatuses = ['available','free','vacant','open'];
              const redStatuses = ['booked','occupied','reserved','pending','unavailable'];

              // Only override colors if not already provided
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
                  // fallback: if booking_status absent, use booking/occupancy prop hints
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

              roomCalendar.addEvent(e);
            } catch(e) { /* ignore malformed event */ }
          }
        }
      } catch(e) { console.error(e); }
      if (loading) loading.style.display = 'none';
    }, function(err) {
      if (loading) loading.style.display = 'none';
    });
  }

  window.openRoomCalendarModal = function(itemId, itemName) {
    currentItemId = itemId;
    const modalEl = document.getElementById('roomCalendarModal');
    if (!modalEl) return;
    const titleEl = modalEl.querySelector('.modal-title');
    if (titleEl) titleEl.textContent = (itemName ? itemName : 'Room') + ' Schedule';

    // initialize calendar for this item
    initRoomCalendar(itemId);

    // show modal
    const bsModal = new bootstrap.Modal(modalEl);
    // ensure FullCalendar recalculates layout once the modal is visible
    modalEl.addEventListener('shown.bs.modal', function onShown() {
      try {
        if (roomCalendar) {
          // recompute height and re-render/update size
          try { roomCalendar.setOption('height', calcCalendarHeight()); } catch(e){}
          try { roomCalendar.render(); } catch(e){}
          try { roomCalendar.updateSize(); } catch(e){}
        }
      } catch (e) { console.error(e); }
      // remove listener (one-time)
      modalEl.removeEventListener('shown.bs.modal', onShown);
    });
    bsModal.show();
  };

  // Expose small control functions for the modal calendar
  window.roomCalendarPrev = function() { try { if (roomCalendar) roomCalendar.prev(); } catch(e){} };
  window.roomCalendarToday = function() { try { if (roomCalendar) roomCalendar.today(); } catch(e){} };
  window.roomCalendarNext = function() { try { if (roomCalendar) roomCalendar.next(); } catch(e){} };
})();
</script>
