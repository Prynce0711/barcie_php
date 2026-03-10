function showSection(sectionId) {
  console.log("ðŸ”„ showSection called with:", sectionId);

  // Hide all sections - use both CSS classes and display property for consistency
  const allSections = document.querySelectorAll(".content-section");
  console.log("ðŸ“¦ Found sections:", allSections.length);

  if (allSections.length === 0) {
    console.error(
      "âŒ NO SECTIONS FOUND! Check if .content-section elements exist in DOM",
    );
    return;
  }

  allSections.forEach((section, index) => {
    console.log(
      "  Hiding section " + (index + 1) + ": " + (section.id || "NO ID"),
    );
    // Remove all display classes first
    section.classList.remove("d-block", "active");
    section.classList.add("d-none");
    section.style.display = "none";
  });

  // Show target section
  const targetSection = document.getElementById(sectionId);
  console.log("ðŸŽ¯ Target section element:", targetSection);

  if (targetSection) {
    console.log("âœ… Showing target section:", sectionId);
    // Remove d-none first, then add d-block
    targetSection.classList.remove("d-none");
    targetSection.classList.add("d-block", "active");
    targetSection.style.display = "block";

    console.log("  - Classes after:", targetSection.className);
    console.log("  - Display style:", targetSection.style.display);

    // Add animation
    targetSection.style.animation = "slideInFromRight 0.5s ease";

    // Store the active section
    localStorage.setItem("activeSection", sectionId);

    // Dispatch custom event for section change
    document.dispatchEvent(
      new CustomEvent("sectionChanged", {
        detail: { section: sectionId },
      }),
    );

    // Re-initialize edit forms when rooms section is shown
    if (sectionId === "rooms-section") {
      setTimeout(() => {
        // Only re-initialize if not already done
        if (!window.roomsInitialized) {
          initializeEditForms();
          initializeRoomsFiltering();
          initializeRoomsSearch();
          window.roomsInitialized = true;
          console.log("Rooms functionality fully initialized");
        } else {
          // Just refresh edit forms
          initializeEditForms();
          console.log("Edit forms refreshed for rooms section");
        }
      }, 100);
    }
  } else {
    console.error("Target section not found:", sectionId);
  }

  // Close mobile menu if open
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".mobile-menu-toggle");
  if (
    window.innerWidth <= 992 &&
    sidebar &&
    sidebar.classList.contains("open")
  ) {
    sidebar.classList.remove("open");
    if (toggleBtn) {
      toggleBtn.querySelector("i").className = "fas fa-bars";
    }
  }
}

// Enhanced Calendar Initialization
function initializeCalendar() {
  const calendarEl = document.getElementById("calendar");
  if (calendarEl && typeof FullCalendar !== "undefined") {
    // Check if bookingEvents is available
    const events = window.bookingEvents || [];

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      // Ensure dashboard calendar uses Philippine time
      timeZone: "Asia/Manila",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
      },
      events: events,
      eventClick: function (info) {
        showBookingDetails(info.event);
      },
      eventDidMount: function (info) {
        // Add Bootstrap classes based on event status
        const status = info.event.extendedProps?.status;
        if (status === "confirmed") {
          info.el.classList.add("bg-success");
        } else if (status === "pending") {
          info.el.classList.add("bg-warning");
        } else if (status === "cancelled") {
          info.el.classList.add("bg-danger");
        }
      },
      height: "auto",
      responsive: true,
    });

    calendar.render();
  }

  // Dashboard Calendar (smaller version)
}

// Show Booking Details in Modal
function showBookingDetails(event) {
  const modalHtml = `
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingModalLabel">
                            <i class="fas fa-calendar-check me-2"></i>Booking Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-4"><strong>Title:</strong></div>
                            <div class="col-sm-8">${event.title}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Start:</strong></div>
                            <div class="col-sm-8">${event.start.toLocaleDateString()}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>End:</strong></div>
                            <div class="col-sm-8">${
                              event.end ? event.end.toLocaleDateString() : "N/A"
                            }</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-${getStatusColor(
                                  event.extendedProps?.status,
                                )}">
                                    ${event.extendedProps?.status || "Unknown"}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

  // Remove existing modal if present
  const existingModal = document.getElementById("bookingModal");
  if (existingModal) {
    existingModal.remove();
  }

  // Add new modal to body
  document.body.insertAdjacentHTML("beforeend", modalHtml);

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById("bookingModal"));
  modal.show();
}

// Get Status Color for Badge
