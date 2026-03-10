function initializeCalendarNavigation() {
  const calendarViewBtn = document.getElementById("calendar-view-btn");
  const roomListBtn = document.getElementById("room-list-btn");
  const calendarContent = document.getElementById("calendar-view-content");
  const roomListContent = document.getElementById("room-list-content");

  if (calendarViewBtn && roomListBtn && calendarContent && roomListContent) {
    // Calendar View Button
    calendarViewBtn.addEventListener("click", function () {
      // Update button states
      calendarViewBtn.classList.add("active");
      roomListBtn.classList.remove("active");

      // Show/hide content
      calendarContent.style.display = "block";
      roomListContent.style.display = "none";

      // Re-render calendar if it exists
      if (window.roomCalendarInstance) {
        setTimeout(() => window.roomCalendarInstance.render(), 100);
      }
    });

    // Room List Button
    roomListBtn.addEventListener("click", function () {
      // Update button states
      roomListBtn.classList.add("active");
      calendarViewBtn.classList.remove("active");

      // Show/hide content
      calendarContent.style.display = "none";
      roomListContent.style.display = "block";
    });
  }
}

function initializeRoomSearch() {
  const searchInput = document.getElementById("room-search");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      const roomItems = document.querySelectorAll(".item-card, .room-item");

      roomItems.forEach((item) => {
        const roomName = item.getAttribute("data-room-name") || "";
        const roomNumber = item.getAttribute("data-room-number") || "";
        const itemType = item.getAttribute("data-item-type") || "";
        const text = item.textContent.toLowerCase();

        const matches =
          text.includes(searchTerm) ||
          roomName.includes(searchTerm) ||
          roomNumber.includes(searchTerm) ||
          itemType.includes(searchTerm);

        if (matches) {
          item.style.display = "";
          item.classList.remove("d-none");
        } else {
          item.style.display = "none";
          item.classList.add("d-none");
        }
      });
    });
  }
}

function initializeRoomCalendar() {
  const calendarEl = document.getElementById("roomCalendar");
  if (!calendarEl || typeof FullCalendar === "undefined") {
    return;
  }

  // Generate room events based on current booking data
  const roomEvents = generateRoomEvents();

  window.roomCalendarInstance = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    // Ensure room calendar uses Philippine time
    timeZone: "Asia/Manila",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    events: roomEvents,
    eventDisplay: "block",
    dayMaxEvents: true,
    height: "auto",
    aspectRatio: 1.8,
    eventOverlap: false,
    slotEventOverlap: false,
    displayEventTime: true,
    displayEventEnd: true,
    nowIndicator: true,
    businessHours: {
      daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
      startTime: "08:00",
      endTime: "20:00",
    },
    eventClick: function (info) {
      const { title } = info.event;
      const { itemName, roomNumber, guest, status, checkIn, checkOut } =
        info.event.extendedProps;

      let details = `${title}<br><br>`;
      if (itemName) {
        details += `Item: ${itemName}<br>`;
      }
      if (roomNumber) {
        details += `Room #: ${roomNumber}<br>`;
      }
      if (guest) {
        details += `Guest: ${guest}<br>`;
      }
      if (status) {
        details += `Status: ${status}<br>`;
      }
      if (checkIn) {
        details += `Check-in: ${checkIn}<br>`;
      }
      if (checkOut) {
        details += `Check-out: ${checkOut}<br>`;
      }

      showToast(details, "info");
    },
    dateClick: function (info) {
      // Date click handler can be added here if needed
    },
    eventDidMount: function (info) {
      // Add tooltips or additional styling if needed
      info.el.title = info.event.title;
    },
  });

  window.roomCalendarInstance.render();
}

function generateRoomEvents() {
  // Events will be populated by PHP in the dashboard.php script
  if (typeof window.roomEvents !== "undefined") {
    return window.roomEvents;
  }

  return [];
}

// Global sidebar toggle function
window.toggleSidebar = function () {
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".mobile-menu-toggle");

  if (sidebar) {
    sidebar.classList.toggle("open");

    if (toggleBtn) {
      const icon = toggleBtn.querySelector("i");
      if (sidebar.classList.contains("open")) {
        icon.className = "fas fa-times";
      } else {
        icon.className = "fas fa-bars";
      }
    }
  }
};

// Promise-based confirmation modal helper
