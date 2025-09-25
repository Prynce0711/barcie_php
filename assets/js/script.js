// ==========================================
// SIDEBAR TOGGLE
// ==========================================
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("active");
}

// ==========================================
// SECTION DISPLAY HANDLING
// ==========================================
function showSection(sectionId) {
  // Hide all content sections first
  document.querySelectorAll(".content-section, .main-content-area, .content-background").forEach(sec => {
    sec.style.display = 'none';
  });

  // Show the requested section
  document.getElementById(sectionId).style.display = 'block';

  // Special case: "Get Started" section
  if (sectionId === 'mainContent') {
    document.getElementById('mainContent').style.display = 'block';
  }
}

// ==========================================
// PASSWORD VISIBILITY TOGGLE
// ==========================================
function togglePasswordVisibility() {
  const passwordInput = document.getElementById("password");
  const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
  passwordInput.setAttribute("type", type);
}

// ==========================================
// FULLCALENDAR INITIALIZATION
// ==========================================
document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("calendar");
  
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

    // Handle dropdown changes to load events
    document.getElementById("placeSelect").addEventListener("change", function () {
      const place = this.value;
      calendar.removeAllEvents();

      let events = [];
      if (place === "standard-room") {
        events = [
          { 
            title: "Booked: Guest Stay", 
            start: "2023-09-22T14:00:00", 
            end: "2023-09-24T10:00:00", 
            color: "green" 
          }
        ];
      } else if (place === "deluxe-room") {
        events = [
          { 
            title: "Pending: Reservation", 
            start: "2023-09-25", 
            color: "orange" 
          }
        ];
      }
      
      events.forEach(event => calendar.addEvent(event));
    });

    calendar.render();
  }
});

// ==========================================
// BROWSERSYNC (DEV ONLY)
// ==========================================
(function () {
  try {
    const script = document.createElement("script");
    script.async = true;
    script.src = "http://HOST:3002/browser-sync/browser-sync-client.js?v=3.0.4"
      .replace("HOST", location.hostname);

    if (document.body) {
      document.body.appendChild(script);
    } else if (document.head) {
      document.head.appendChild(script);
    }
  } catch (e) {
    console.error("Browsersync: could not append script tag", e);
  }
})();

// ==========================================
// ADMIN PANEL: MANAGE ROOM/FACILITY FORMS
// ==========================================
document.querySelectorAll('input[name="manageType"]').forEach(radio => {
  radio.addEventListener('change', function() {
    document.getElementById("room-form").style.display = this.value === "room" ? "block" : "none";
    document.getElementById("facility-form").style.display = this.value === "facility" ? "block" : "none";
  });
});

// ==========================================
// BOOKING FORM HANDLING
// ==========================================
function showBookingForm() {
  const bookingSection = document.getElementById('bookingFormSection');
  bookingSection.style.display = 'block';
  bookingSection.scrollIntoView({ behavior: 'smooth' }); // scroll smoothly
}

// Attach booking form display to all "Book Now" buttons
const bookNowButtons = document.querySelectorAll('.book-now-btn');
bookNowButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const bookingForm = document.getElementById('bookingFormSection');
    bookingForm.style.display = 'block';
    bookingForm.scrollIntoView({ behavior: 'smooth' });
  });
});

// ==========================================
// BOOKING TYPE: PENCIL vs RESERVATION
// ==========================================
document.querySelectorAll('input[name="bookingType"]').forEach(radio => {
  radio.addEventListener('change', function() {
    if (this.value === 'pencil') {
      document.getElementById('pencilFields').style.display = 'block';
      document.getElementById('reservationFields').style.display = 'none';
    } else {
      document.getElementById('pencilFields').style.display = 'none';
      document.getElementById('reservationFields').style.display = 'block';
    }
  });
});

// ==========================================
// ROOMS & FACILITIES FILTERING
// ==========================================
document.querySelectorAll('input[name="typeFilter"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const value = this.value;

    document.querySelectorAll('.type-room').forEach(el => {
      el.style.display = (value === 'room' || value === 'all') ? 'block' : 'none';
    });

    document.querySelectorAll('.type-facility').forEach(el => {
      el.style.display = (value === 'facility' || value === 'all') ? 'block' : 'none';
    });
  });
});

// Initialize filter on page load
document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('input[name="typeFilter"]:checked')
    .dispatchEvent(new Event('change'));
});

// ==========================================
// BOOKING FORM SUBMISSION
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
  const bookingForm = document.getElementById("bookingForm");

  if (bookingForm) {
    bookingForm.addEventListener("submit", function (e) {
      // Optional: Prevent empty submission
      const bookingType = document.querySelector('input[name="bookingType"]:checked');
      if (!bookingType) {
        e.preventDefault();
        alert("⚠️ Please select a booking type.");
        return;
      }

      // Show a temporary "Submitting..." message
      const submitBtn = bookingForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = "Submitting...";

      // Let the form submit normally (to PHP)
      // PHP will redirect back with ?success=1 or ?error=1
    });
  }
});
