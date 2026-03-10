function addTableSorting(table) {
  const headers = table.querySelectorAll("th");
  headers.forEach((header, index) => {
    if (!header.classList.contains("no-sort")) {
      header.style.cursor = "pointer";
      header.innerHTML += ' <i class="fas fa-sort ms-1"></i>';
      header.addEventListener("click", () => sortTable(table, index));
    }
  });
}

// Sort Table
function sortTable(table, columnIndex) {
  const tbody = table.querySelector("tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));
  const isAscending = table.dataset.sortOrder !== "asc";

  rows.sort((a, b) => {
    const aText = a.cells[columnIndex].textContent.trim();
    const bText = b.cells[columnIndex].textContent.trim();

    if (isNaN(aText) || isNaN(bText)) {
      return isAscending
        ? aText.localeCompare(bText)
        : bText.localeCompare(aText);
    } else {
      return isAscending
        ? Number(aText) - Number(bText)
        : Number(bText) - Number(aText);
    }
  });

  rows.forEach((row) => tbody.appendChild(row));
  table.dataset.sortOrder = isAscending ? "asc" : "desc";

  // Update sort icon
  const headers = table.querySelectorAll("th");
  headers.forEach((header, index) => {
    const icon = header.querySelector("i");
    if (icon) {
      if (index === columnIndex) {
        icon.className = isAscending
          ? "fas fa-sort-up ms-1"
          : "fas fa-sort-down ms-1";
      } else {
        icon.className = "fas fa-sort ms-1";
      }
    }
  });
}

// Enhanced Toast Notifications
function showToast(message, type = "info") {
  const toastContainer = getOrCreateToastContainer();
  const toastId = "toast-" + Date.now();

  let bgClass, iconClass;
  switch (type) {
    case "success":
      bgClass = "bg-success";
      iconClass = "fa-check-circle";
      break;
    case "warning":
      bgClass = "bg-warning";
      iconClass = "fa-exclamation-triangle";
      break;
    case "error":
    case "danger":
      bgClass = "bg-danger";
      iconClass = "fa-times-circle";
      break;
    default:
      bgClass = "bg-info";
      iconClass = "fa-info-circle";
  }

  const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas ${iconClass} me-2"></i>
                <strong class="me-auto">Dashboard</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML("beforeend", toastHtml);
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement);
  toast.show();

  // Remove element after it's hidden
  toastElement.addEventListener("hidden.bs.toast", function () {
    this.remove();
  });
}

// Get or Create Toast Container
function getOrCreateToastContainer() {
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container position-fixed top-0 end-0 p-3";
    container.style.zIndex = "9999";
    document.body.appendChild(container);
  }
  return container;
}

// Item Management Functions
async function loadItems() {
  try {
    const res = await fetch("database/fetch_items.php");
    const items = await res.json();
    const container = document.getElementById("cards-grid");
    if (container) {
      container.innerHTML = "";

      // Create counters object to track counts
      const counters = {
        room: 0,
        facility: 0,
      };

      items.forEach((item) => {
        // Increment counter based on item type
        counters[item.item_type] = (counters[item.item_type] || 0) + 1;

        const card = document.createElement("div");
        card.classList.add("card", "shadow-sm");
        card.dataset.type = item.item_type;
        card.innerHTML = `
          <div class="card-body">
            ${
              item.image
                ? `<img src="${item.image}" class="card-img-top mb-3" style="height:150px;object-fit:cover;">`
                : ""
            }
            <h5 class="card-title mb-3">${item.name}</h5>
            ${
              item.room_number
                ? `<p class="card-text mb-2"><i class="fas fa-door-open me-2"></i>Room Number: ${item.room_number}</p>`
                : ""
            }
            <p class="card-text mb-2">
              <i class="fas fa-users me-2"></i>Capacity: ${item.capacity} ${
                item.item_type === "room" ? "persons" : "people"
              }

            </p>
            <p class="card-text mb-2">
              <i class="fas fa-tag me-2"></i>Price: â‚±${item.price}${
                item.item_type === "room" ? "/night" : "/day"
              }
            </p>
            <p class="card-text text-muted small">${item.description}</p>
            <div class="mt-3 pt-3 border-top">
              <span class="badge ${
                item.item_type === "room" ? "bg-primary" : "bg-success"
              }">
                ${item.item_type.toUpperCase()}
              </span>
            </div>
          </div>
        `;
        container.appendChild(card);
      });

      // Update the counters in the UI
      document.querySelectorAll(".type-count").forEach((counter) => {
        const { type } = counter.dataset;
        if (type) {
          counter.textContent = counters[type] || 0;
        }
      });

      filterItems();
    }
  } catch (error) {
    console.error("Error loading items:", error);
    showToast("Error loading items", "error");
  }
}

// Filter Items by Type
