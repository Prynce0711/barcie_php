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

function normalizeNoticeType(type) {
  const normalized = String(type || "info").toLowerCase();
  if (normalized === "danger") return "error";
  if (["success", "error", "warning", "info"].includes(normalized)) {
    return normalized;
  }
  return "info";
}

// Popup-based notifications (replaces upper-right toast notifications)
function showToast(message, type = "info", duration = 4500) {
  const normalizedType = normalizeNoticeType(type);
  const text = String(message || "Notification");

  const legacyContainer = document.getElementById("toast-container");
  if (legacyContainer) {
    legacyContainer.remove();
  }

  if (
    normalizedType === "success" &&
    typeof window.showSuccessPopup === "function"
  ) {
    return window.showSuccessPopup(text, {
      title: "Success",
      autoCloseMs: duration > 0 ? duration : undefined,
    });
  }

  if (typeof window.showErrorPopup === "function") {
    const titleMap = {
      error: "Error",
      warning: "Warning",
      info: "Notification",
    };
    return window.showErrorPopup(text, {
      title: titleMap[normalizedType] || "Notification",
      autoCloseMs: duration > 0 ? duration : undefined,
    });
  }

  alert(text);
  return null;
}

// Item Management Functions
async function loadItems() {
  try {
    const res = await fetch("database/index.php?endpoint=fetch_items");
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
              <i class="fas fa-tag me-2"></i>Price: ₱${item.price}${
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
