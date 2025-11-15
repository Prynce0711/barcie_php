<!-- Room Calendar Modal -->
<div class="modal fade" id="roomCalendarModal" tabindex="-1" aria-labelledby="roomCalendarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roomCalendarModalLabel">Room Calendar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="roomCalendarInner" style="min-height: 500px;"></div>
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

  function initRoomCalendar(itemId) {
    const container = document.getElementById('roomCalendarInner');
    if (!container) return;

    // destroy previous calendar
    if (roomCalendar) {
      try { roomCalendar.destroy(); } catch(e){}
      roomCalendar = null;
    }

    if (typeof FullCalendar === 'undefined') {
      container.innerHTML = '<div class="text-center text-muted p-5">FullCalendar not loaded</div>';
      return;
    }

    roomCalendar = new FullCalendar.Calendar(container, {
      initialView: 'dayGridMonth',
      headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
      events: function(fetchInfo, successCallback, failureCallback) {
        fetchAndFilterEvents(itemId, fetchInfo, successCallback, failureCallback);
      },
      timeZone: 'Asia/Manila',
      nowIndicator: true,
      eventDisplay: 'block',
      locale: 'en',
      firstDay: 1,
      height: 600
    });

    roomCalendar.render();
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
    bsModal.show();
  };
})();
</script>
