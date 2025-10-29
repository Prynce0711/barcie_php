// Calendar Section JavaScript
// Functions for calendar functionality - called by dashboard-bootstrap.js

// Don't auto-initialize - let dashboard-bootstrap.js handle it
// document.addEventListener('DOMContentLoaded', function () {
//   initializeRoomCalendar();
//   initializeCalendarNavigation();
//   initializeRoomSearch();
// });

function initializeRoomCalendar() {
  const calendarEl = document.getElementById('roomCalendar');
  if (!calendarEl) return;

  // Generate room events based on current booking data
  const roomEvents = window.roomEvents || [];

  window.calendarInstance = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: roomEvents,
    eventDisplay: 'block',
    dayMaxEvents: true,
    height: 'auto',
    aspectRatio: 1.8,
    eventOverlap: false,
    slotEventOverlap: false,
    displayEventTime: true,
    displayEventEnd: true,
    nowIndicator: true,
    businessHours: {
      daysOfWeek: [0,1,2,3,4,5,6],
      startTime: '08:00',
      endTime: '20:00',
    },
    eventClick: function (info) {
      const itemType = info.event.extendedProps.itemType || 'Item';
      const itemName = info.event.extendedProps.itemName || info.event.title;
      const roomNumber = info.event.extendedProps.roomNumber || '';
      const guest = info.event.extendedProps.guest || 'Unknown';
      const status = info.event.extendedProps.status || 'Unknown';
      const checkin = info.event.extendedProps.checkin || 'Unknown';
      const checkout = info.event.extendedProps.checkout || 'Unknown';
      const details = info.event.extendedProps.details || 'No details';

      // If bookingId is present, use the central booking details modal function if available
      const bookingId = info.event.extendedProps.bookingId || null;
      if (bookingId && typeof viewBookingDetails === 'function') {
        // reuse the admin booking details fetch/modal
        viewBookingDetails(bookingId);
        return;
      }

      // Otherwise show a modal with the event details (replaces alert())
      const roomInfo = roomNumber ? `<div><strong>Room Number:</strong> #${roomNumber}</div>` : '';
      const modalHTML = `
        <div class="modal fade" id="calendarEventInfoModal" tabindex="-1">
          <div class="modal-dialog modal-md">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">${itemType}: ${itemName}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                ${roomInfo}
                <div><strong>Guest:</strong> ${guest}</div>
                <div><strong>Status:</strong> ${status}</div>
                <div><strong>Check-in:</strong> ${checkin}</div>
                <div><strong>Check-out:</strong> ${checkout}</div>
                <hr />
                <div><strong>Details:</strong></div>
                <div>${details}</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      `;

      // Remove existing modal if any
      const existing = document.getElementById('calendarEventInfoModal');
      if (existing) existing.remove();
      document.body.insertAdjacentHTML('beforeend', modalHTML);
      const modalEl = document.getElementById('calendarEventInfoModal');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
      modalEl.addEventListener('hidden.bs.modal', function() { this.remove(); });
    },
    dateClick: function (info) {
      console.log('Date clicked:', info.dateStr);
    },
    eventDidMount: function (info) {
      if (!info.event.extendedProps.hasReservation) {
        info.el.style.opacity = '0.6';
      }
    }
  });

  window.calendarInstance.render();
}

function initializeCalendarNavigation() {
  // Calendar/Room list view toggle
  const calendarViewBtn = document.getElementById('calendar-view-btn');
  const roomListBtn = document.getElementById('room-list-btn');
  const calendarContent = document.getElementById('calendar-view-content');
  const roomListContent = document.getElementById('room-list-content');

  if (calendarViewBtn && roomListBtn) {
    calendarViewBtn.addEventListener('click', function() {
      // Show calendar view
      calendarContent.style.display = 'block';
      roomListContent.style.display = 'none';
      calendarViewBtn.classList.add('active');
      roomListBtn.classList.remove('active');
      
      // Re-render calendar after showing
      setTimeout(() => {
        if (window.calendarInstance) {
          window.calendarInstance.render();
        }
      }, 100);
    });

    roomListBtn.addEventListener('click', function() {
      // Show room list view
      calendarContent.style.display = 'none';
      roomListContent.style.display = 'block';
      roomListBtn.classList.add('active');
      calendarViewBtn.classList.remove('active');
    });
  }
}

function initializeRoomSearch() {
  const searchInput = document.getElementById('room-search');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const roomItems = document.querySelectorAll('.room-item');
      
      roomItems.forEach(function(item) {
        const roomName = item.getAttribute('data-room-name') || '';
        const roomNumber = item.getAttribute('data-room-number') || '';
        const itemType = item.getAttribute('data-item-type') || '';
        
        const searchableText = roomName + ' ' + roomNumber + ' ' + itemType;
        
        if (searchableText.includes(searchTerm)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  }
}