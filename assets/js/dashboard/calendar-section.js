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

      const roomInfo = roomNumber ? `\nRoom Number: #${roomNumber}` : '';
      alert(`${itemType}: ${itemName}${roomInfo}\nGuest: ${guest}\nStatus: ${status}\nCheck-in: ${checkin}\nCheck-out: ${checkout}\nBooking Details: ${details}`);
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