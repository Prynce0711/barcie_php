function setDashboardData(events, monthlyData, statusData, stats) {
  window.dashboardData = {
    bookingEvents: events || [],
    monthlyBookingsData: monthlyData || [],
    statusDistributionData: statusData || {},
    dashboardStats: stats || {},
  };

  // Make variables globally accessible for chart functions
  window.bookingEvents = events || [];
  window.monthlyBookingsData = monthlyData || [];
  window.statusDistributionData = statusData || {};
  window.dashboardStats = stats || {};

  // Wait for DOM to be ready before initializing charts
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        initializeCharts();
      }, 100);
    });
  } else {
    // DOM is already ready, initialize charts immediately
    setTimeout(() => {
      initializeCharts();
    }, 100);
  }
}

// Refresh chart with different timeframes (using existing database data for now)
function refreshChart(timeframe, event) {
  // Update active button
  const buttons = document.querySelectorAll(".btn-group .btn");
  buttons.forEach((btn) => btn.classList.remove("active"));
  if (event && event.target) {
    event.target.classList.add("active");
  }

  // Note: Since we removed API calls, we're using the existing monthly data
  // In a real application, you could reload the page with a timeframe parameter
  // or implement server-side filtering

  if (timeframe === "7days" || timeframe === "30days") {
    // For demo purposes, show a subset of data for shorter timeframes
    let filteredData = window.monthlyBookingsData;
    if (timeframe === "7days") {
      filteredData = window.monthlyBookingsData.slice(-7);
    } else if (timeframe === "30days") {
      filteredData = window.monthlyBookingsData.slice(-5);
    }

    // Update the chart with filtered data
    if (window.bookingsChartInstance) {
      window.bookingsChartInstance.data.labels = filteredData.map(
        (item) => item.month,
      );
      window.bookingsChartInstance.data.datasets[0].data = filteredData.map(
        (item) => item.count,
      );
      window.bookingsChartInstance.update();
    }
  } else if (window.bookingsChartInstance) {
    window.bookingsChartInstance.data.labels = window.monthlyBookingsData.map(
      (item) => item.month,
    );
    window.bookingsChartInstance.data.datasets[0].data =
      window.monthlyBookingsData.map((item) => item.count);
    window.bookingsChartInstance.update();
  }

  showToast(`Chart updated to show ${timeframe} view`, "info");
}

// Initialize Rooms Filtering Function
function initializeRoomsFiltering() {
  // Filter buttons functionality
  const filterButtons = document.querySelectorAll(".filter-btn");
  const roomCards = document.querySelectorAll(".item-card");

  if (filterButtons.length > 0) {
    filterButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const filterType = this.dataset.filter;

        // Update active button
        filterButtons.forEach((btn) => btn.classList.remove("active"));
        this.classList.add("active");

        // Filter rooms/facilities
        roomCards.forEach((card) => {
          const cardType = card.dataset.type;

          if (filterType === "all" || cardType === filterType) {
            card.style.display = "block";
            card.classList.remove("d-none");
          } else {
            card.style.display = "none";
            card.classList.add("d-none");
          }
        });
      });
    });
  }

  // Type filter radio buttons (if they exist)
  const typeFilters = document.querySelectorAll(
    'input[name="type_filter"], .type-filter',
  );
  if (typeFilters.length > 0) {
    typeFilters.forEach((filter) => {
      filter.addEventListener("change", function () {
        const selectedType = this.value;

        // Update counts for each type
        updateTypeCounts();

        roomCards.forEach((card) => {
          const cardType = card.dataset.type;

          if (selectedType === "all" || cardType === selectedType) {
            card.style.display = "block";
            card.classList.remove("d-none");
          } else {
            card.style.display = "none";
            card.classList.add("d-none");
          }
        });
      });
    });
  }

  // Update type counts
  updateTypeCounts();
}

// Helper function to update type counts
function updateTypeCounts() {
  const allItems = document.querySelectorAll(".item-card");
  const roomItems = document.querySelectorAll('.item-card[data-type="room"]');
  const facilityItems = document.querySelectorAll(
    '.item-card[data-type="facility"]',
  );

  // Update count badges
  const allCount = document.querySelector('.type-count[data-type="all"]');
  const roomCount = document.querySelector('.type-count[data-type="room"]');
  const facilityCount = document.querySelector(
    '.type-count[data-type="facility"]',
  );

  if (allCount) allCount.textContent = allItems.length;
  if (roomCount) roomCount.textContent = roomItems.length;
  if (facilityCount) facilityCount.textContent = facilityItems.length;
}

// Initialize Rooms Search Function
function initializeRoomsSearch() {
  const searchInput = document.querySelector(
    "#searchItems, #rooms-search, .rooms-search-input",
  );
  const roomCards = document.querySelectorAll(".item-card");

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase().trim();

      roomCards.forEach((card) => {
        const title = card.querySelector(".card-title");
        const description = card.querySelector(".card-text");
        const searchableData = card.dataset.searchable || "";

        let cardText = searchableData;
        if (title) {
          cardText += " " + title.textContent.toLowerCase();
        }
        if (description) {
          cardText += " " + description.textContent.toLowerCase();
        }

        if (searchTerm === "" || cardText.includes(searchTerm)) {
          card.style.display = "block";
          card.classList.remove("d-none");
        } else {
          card.style.display = "none";
          card.classList.add("d-none");
        }
      });
    });
  }
}

// Initialize Edit Forms Function
