function getStatusColor(status) {
  switch (status) {
    case "confirmed":
      return "success";
    case "pending":
      return "warning";
    case "cancelled":
      return "danger";
    default:
      return "secondary";
  }
}

// Enhanced Form Handling
function enhanceForms() {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    // Add Bootstrap validation
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          showToast("Please fill in all required fields correctly.", "warning");
        } else {
          showToast("Form submitted successfully!", "success");
        }
        form.classList.add("was-validated");
      },
      false,
    );

    // Real-time validation
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this);
      });

      input.addEventListener("input", function () {
        clearValidation(this);
      });
    });
  });
}

// Field Validation
function validateField(field) {
  if (field.checkValidity()) {
    field.classList.remove("is-invalid");
    field.classList.add("is-valid");
  } else {
    field.classList.remove("is-valid");
    field.classList.add("is-invalid");
  }
}

// Clear Validation
function clearValidation(field) {
  field.classList.remove("is-valid", "is-invalid");
}

// Enhanced Dark Mode (preserving original functionality)
function setupDarkMode() {
  // Apply saved theme
  const savedTheme = localStorage.getItem("theme") || "light";
  document.documentElement.setAttribute("data-bs-theme", savedTheme);

  // Also apply to body for backward compatibility
  if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
  }

  // Update toggle button
  const toggleBtn = document.querySelector(".dark-toggle");
  if (toggleBtn) {
    const icon = toggleBtn.querySelector("i");
    if (icon) {
      icon.className = savedTheme === "dark" ? "fas fa-sun" : "fas fa-moon";
    } else {
      toggleBtn.textContent = savedTheme === "dark" ? "â˜€ï¸" : "ðŸŒ™";
    }
  }
}

// Toggle Dark Mode (enhanced version)
function toggleDarkMode() {
  const currentTheme =
    document.documentElement.getAttribute("data-bs-theme") || "light";
  const newTheme = currentTheme === "dark" ? "light" : "dark";

  document.documentElement.setAttribute("data-bs-theme", newTheme);
  localStorage.setItem("theme", newTheme);

  // Also toggle body class for backward compatibility
  document.body.classList.toggle("dark-mode", newTheme === "dark");

  // Update button
  const toggleBtn = document.querySelector(".dark-toggle");
  if (toggleBtn) {
    const icon = toggleBtn.querySelector("i");
    if (icon) {
      icon.className = newTheme === "dark" ? "fas fa-sun" : "fas fa-moon";
    } else {
      toggleBtn.textContent = newTheme === "dark" ? "â˜€ï¸" : "ðŸŒ™";
    }
  }

  showToast(`Switched to ${newTheme} mode`, "info");
}

// Enhance Data Tables
function enhanceDataTables() {
  const tables = document.querySelectorAll(".table");

  tables.forEach((table) => {
    // Add Bootstrap classes
    table.classList.add("table-hover", "table-responsive");

    // Add search functionality for large tables
    if (table.querySelectorAll("tbody tr").length > 5) {
      addTableSearch(table);
    }

    // Add sorting functionality
    addTableSorting(table);
  });
}

// Add Table Search
function addTableSearch(table) {
  const tableContainer = table.closest(".card-body") || table.parentElement;
  if (tableContainer && !tableContainer.querySelector(".table-search")) {
    const searchHtml = `
            <div class="table-search mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search table..." 
                           onkeyup="filterTable(this, '${
                             table.id || "table-" + Date.now()
                           }')">
                </div>
            </div>
        `;
    tableContainer.insertAdjacentHTML("afterbegin", searchHtml);

    if (!table.id) {
      table.id = "table-" + Date.now();
    }
  }
}

// Filter Table
function filterTable(input, tableId) {
  const filter = input.value.toLowerCase();
  const table = document.getElementById(tableId);
  const rows = table.querySelectorAll("tbody tr");

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase();
    if (text.includes(filter)) {
      row.classList.remove("d-none");
    } else {
      row.classList.add("d-none");
    }
  });
}

// Add Table Sorting
