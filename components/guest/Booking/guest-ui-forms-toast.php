<?php /* migrated from Components/Guest/js/guest-ui-forms-toast.js */ ?>
<script>
  function enhanceForms() {
    const forms = document.querySelectorAll("form");

    forms.forEach((form) => {
      // Add Bootstrap validation classes
      form.addEventListener(
        "submit",
        function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            showToast("Please fill in all required fields correctly.", "warning");
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
      field.classList.remove("is-invalid", "form-error");
      field.classList.add("is-valid", "form-success");
    } else {
      field.classList.remove("is-valid", "form-success");
      field.classList.add("is-invalid", "form-error");
    }
  }

  // Clear Validation
  function clearValidation(field) {
    field.classList.remove(
      "is-valid",
      "is-invalid",
      "form-success",
      "form-error",
    );
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
                <strong class="me-auto">Notification</strong>
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

  // Enhanced detailed toast for calendar events
  function showDetailedToast(
    message,
    type = "info",
    title = "Room/Facility Information",
  ) {
    const toastContainer = getOrCreateToastContainer();
    const toastId = "detailed-toast-" + Date.now();

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
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" style="max-width: 350px;">
            <div class="toast-header">
                <i class="fas ${iconClass} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" style="font-size: 0.9rem; line-height: 1.4;">
                ${message}
                <hr class="my-2 opacity-50">
                <div class="d-flex justify-content-between align-items-center">
                    <small><i class="fas fa-calendar-alt me-1"></i>Calendar View</small>
                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="toast">Got it</button>
                </div>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML("beforeend", toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: false }); // Don't auto-hide
    toast.show();

    // Remove element after it's hidden
    toastElement.addEventListener("hidden.bs.toast", function () {
      this.remove();
    });
  }

  // Global Sidebar Toggle Function

</script>