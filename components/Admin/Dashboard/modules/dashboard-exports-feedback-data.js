function initializeEditForms() {
  // Initialize edit form toggles with the correct selectors
  setupEditFormToggles();

  console.log("Edit forms initialized with proper event handlers");
}

// Function to refresh dashboard data
function refreshDashboardData() {
  // Reinitialize the charts with existing data
  initializeCharts();
}

// Global function exports (to maintain compatibility)
// Using try-catch to handle any reference errors gracefully
try {
  window.showSection = showSection;
  window.toggleDarkMode = toggleDarkMode;
  window.filterTable = filterTable;
  window.showToast = showToast;
  window.loadItems = loadItems;
  window.filterItems = filterItems;
  window.toggleBookingForm = toggleBookingForm;
  window.pencilReminder = pencilReminder;
  window.generateReceiptNumber = generateReceiptNumber;

  // Calendar & Items functions
  window.initializeCalendarNavigation = initializeCalendarNavigation;
  window.initializeRoomSearch = initializeRoomSearch;
  window.initializeRoomCalendar = initializeRoomCalendar;
  window.generateRoomEvents = generateRoomEvents;

  // Dashboard data function
  window.setDashboardData = setDashboardData;

  // Rooms management functions
  window.initializeRoomsFiltering = initializeRoomsFiltering;
  window.initializeRoomsSearch = initializeRoomsSearch;
  window.initializeEditForms = initializeEditForms;

  // Chart functions
  window.initializeCharts = initializeCharts;
  window.refreshDashboardData = refreshDashboardData;
} catch (error) {
  console.warn("Error assigning global functions:", error);
} // Feedback Management System
function initializeFeedbackManagement() {
  // Initialize feedback section when it becomes active
  document.addEventListener("sectionChanged", function (e) {
    if (e.detail.section === "feedback") {
      loadFeedbackData();
    }
  });

  // Load feedback data if feedback section is already active
  const feedbackSection = document.getElementById("feedback");
  if (feedbackSection && feedbackSection.classList.contains("active")) {
    loadFeedbackData();
  }
}

async function loadFeedbackData(limit = 50, offset = 0) {
  try {
    const endpoint = "database/user_auth.php";

    async function fetchJson(url) {
      const response = await fetch(url, { credentials: "same-origin" });
      const raw = await response.text();
      try {
        return JSON.parse(raw);
      } catch (err) {
        const preview = (raw || "").slice(0, 140).replace(/\s+/g, " ");
        throw new Error(
          "Invalid JSON response (" + response.status + "): " + preview,
        );
      }
    }

    // First initialize the feedback table
    await fetchJson(endpoint + "?action=init_feedback_table");

    // Then load the feedback data
    const data = await fetchJson(
      endpoint +
        "?action=get_feedback_data&limit=" +
        limit +
        "&offset=" +
        offset,
    );

    if (data.success) {
      updateFeedbackStats(data.stats);
      updateFeedbackTable(data.feedback);
      updateRatingChart(data.stats);
      updateFeedbackInsights(data.stats);
    } else {
      console.error("Error loading feedback data:", data.error);
      showFeedbackError(
        "Failed to load feedback data: " + (data.error || "Unknown error"),
      );
    }
  } catch (error) {
    console.error("Error fetching feedback data:", error);
    showFeedbackError("Network error while loading feedback");
  }
}

function updateFeedbackStats(stats) {
  document.getElementById("total-feedback").textContent =
    stats.total_feedback || 0;
  document.getElementById("avg-rating").textContent = parseFloat(
    stats.avg_rating || 0,
  ).toFixed(1);
  document.getElementById("five-star-count").textContent = stats.five_star || 0;
  document.getElementById("low-rating-count").textContent =
    parseInt(stats.one_star || 0) + parseInt(stats.two_star || 0);
}

function updateFeedbackTable(feedback) {
  const tbody = document.getElementById("feedback-tbody");

  if (!feedback || feedback.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center text-muted py-4">
          <i class="fas fa-comment-slash fa-2x mb-3 opacity-50"></i>
          <br>No feedback received yet
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = feedback
    .map((item) => {
      const stars = generateStarDisplay(item.rating);
      const date = new Date(item.created_at).toLocaleDateString();
      const message = item.message || "No additional comments";

      return `
      <tr>
        <td>
          <div class="d-flex align-items-center">
            <div class="avatar-circle bg-primary text-white me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.8rem;">
              ${item.username.charAt(0).toUpperCase()}
            </div>
            <div>
              <div class="fw-semibold">${escapeHtml(item.username)}</div>
              <small class="text-muted">${escapeHtml(item.email)}</small>
            </div>
          </div>
        </td>
        <td>
          <div class="d-flex align-items-center">
            <div class="star-display me-2">${stars}</div>
            <small class="text-muted">(${item.rating}/5)</small>
          </div>
        </td>
        <td>
          <div class="feedback-message" style="max-width: 300px;">
            ${escapeHtml(message)}
          </div>
        </td>
        <td>
          <small class="text-muted">${date}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary btn-sm" onclick="viewFeedbackDetails(${
              item.id
            })" title="View Details">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="respondToFeedback(${
              item.id
            })" title="Respond">
              <i class="fas fa-reply"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    })
    .join("");
}
