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

  function normalizeNoticeType(type) {
    const normalized = String(type || "info").toLowerCase();
    if (normalized === "danger") return "error";
    if (["success", "error", "warning", "info"].includes(normalized)) {
      return normalized;
    }
    return "info";
  }

  function clearLegacyToastContainer() {
    const container = document.getElementById("toast-container");
    if (container) {
      container.remove();
    }
  }

  // Popup-based notifications (replaces upper-right toast notifications)
  function showToast(message, type = "info", duration = 4500) {
    const normalizedType = normalizeNoticeType(type);
    const text = String(message || "Notification");

    clearLegacyToastContainer();

    if (normalizedType === "success" && typeof window.showSuccessPopup === "function") {
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

  // Detailed popup for calendar events (keeps existing API name for compatibility)
  function showDetailedToast(
    message,
    type = "info",
    title = "Room/Facility Information",
  ) {
    const normalizedType = normalizeNoticeType(type);
    const text = String(message || "Notification");

    clearLegacyToastContainer();

    if (normalizedType === "success" && typeof window.showSuccessPopup === "function") {
      return window.showSuccessPopup(text, {
        title,
        allowHtml: true,
      });
    }

    if (typeof window.showErrorPopup === "function") {
      return window.showErrorPopup(text, {
        title,
        allowHtml: true,
      });
    }

    alert(text);
    return null;
  }

  // Global Sidebar Toggle Function

</script>