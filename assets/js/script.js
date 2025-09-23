// Function to toggle the sidebar
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("active");
}

// Function to show a specific content section
function showSection(sectionId) {
  // Hide all sections first
  document.querySelectorAll(".content-section, .main-content-area, .content-background").forEach(sec => {
    sec.style.display = 'none';
  });

  // Show the requested section
  document.getElementById(sectionId).style.display = 'block';

  // Special case: "Get Started" shows the main content area
  if (sectionId === 'mainContent') {
    document.getElementById('mainContent').style.display = 'block';
  }
}

// Password visibility toggle
function togglePasswordVisibility() {
  const passwordInput = document.getElementById("password");
  const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
  passwordInput.setAttribute("type", type);
}

// Initialize FullCalendar on DOM content load
document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("calendar");
  
  // Only initialize the calendar if the element exists on the page
  if (calendarEl) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
      },
      events: [],
    });

    // Handle dropdown changes to load different events
    document.getElementById("placeSelect").addEventListener("change", function () {
      const place = this.value;
      calendar.removeAllEvents();

      // Sample events
      let events = [];
      if (place === "standard-room") {
        events = [{ title: "Booked: Guest Stay", start: "2023-09-22T14:00:00", end: "2023-09-24T10:00:00", color: "green" }];
      } else if (place === "deluxe-room") {
        events = [{ title: "Pending: Reservation", start: "2023-09-25", color: "orange" }];
      }
      
      events.forEach(event => calendar.addEvent(event));
    });

    calendar.render();
  }
});

// A self-executing function for browser-sync
(function () {
  try {
    const script = document.createElement("script");
    script.async = true;
    script.src = "http://HOST:3002/browser-sync/browser-sync-client.js?v=3.0.4".replace("HOST", location.hostname);
    if (document.body) {
      document.body.appendChild(script);
    } else if (document.head) {
      document.head.appendChild(script);
    }
  } catch (e) {
    console.error("Browsersync: could not append script tag", e);
  }
})();

 // Toggle Room/Facility section
  document.querySelectorAll('input[name="manageType"]').forEach(radio => {
    radio.addEventListener('change', function() {
      document.getElementById("room-form").style.display = this.value === "room" ? "block" : "none";
      document.getElementById("facility-form").style.display = this.value === "facility" ? "block" : "none";
    });
  });