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
  document.querySelectorAll(".content-section, .main-content-area, .content-background")
    .forEach(sec => sec.style.display = 'none');

  document.getElementById(sectionId).style.display = 'block';

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

    const placeSelect = document.getElementById("placeSelect");
    if (placeSelect) {
      placeSelect.addEventListener("change", function () {
        const place = this.value;
        calendar.removeAllEvents();

        let events = [];
        if (place === "standard-room") {
          events = [
            { title: "Booked: Guest Stay", start: "2023-09-22T14:00:00", end: "2023-09-24T10:00:00", color: "green" }
          ];
        } else if (place === "deluxe-room") {
          events = [
            { title: "Pending: Reservation", start: "2023-09-25", color: "orange" }
          ];
        }
        events.forEach(event => calendar.addEvent(event));
      });
    }

    calendar.render();
  }

  // ==========================================
  // ADMIN PANEL: MANAGE ROOM/FACILITY FORMS
  // ==========================================
  document.querySelectorAll('input[name="manageType"]').forEach(radio => {
    radio.addEventListener('change', function () {
      document.getElementById("room-form").style.display = this.value === "room" ? "block" : "none";
      document.getElementById("facility-form").style.display = this.value === "facility" ? "block" : "none";
    });
  });

  // ==========================================
  // LOGIN / SIGNUP TOGGLE (FIXED)
  // ==========================================
  const showSignup = document.getElementById('show-signup');
  const showLogin = document.getElementById('show-login');
  const loginForm = document.getElementById('login-form');
  const signupForm = document.getElementById('signup-form');

  if (showSignup && showLogin && loginForm && signupForm) {
    showSignup.addEventListener('click', () => {
      loginForm.style.display = 'none';
      signupForm.style.display = 'block';
    });

    showLogin.addEventListener('click', () => {
      signupForm.style.display = 'none';
      loginForm.style.display = 'block';
    });
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

    (document.body || document.head).appendChild(script);
  } catch (e) {
    console.error("Browsersync: could not append script tag", e);
  }
})();
