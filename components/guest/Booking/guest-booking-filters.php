<?php /* migrated from Components/Guest/js/guest-booking-filters.js */ ?>
<script>
  function toggleBookingForm() {
    const selectedType = document.querySelector(
      'input[name="bookingType"]:checked',
    );

    if (!selectedType) {
      console.warn("No booking type selected");
      return;
    }

    const type = selectedType.value;
    const reservationForm = document.getElementById("reservationForm");
    const pencilForm = document.getElementById("pencilForm");

    if (!reservationForm || !pencilForm) {
      console.warn("Booking forms not found");
      return;
    }

    reservationForm.style.display = type === "reservation" ? "block" : "none";
    pencilForm.style.display = type === "pencil" ? "block" : "none";

    // Generate new receipt number when switching to reservation form
    if (type === "reservation") {
      generateReceiptNumber();
    }

    // Trigger field lock check when switching forms (if function exists)
    if (typeof checkAndEnableFormFields === "function") {
      if (type === "reservation") {
        checkAndEnableFormFields("reservation");
      } else if (type === "pencil") {
        checkAndEnableFormFields("pencil");
      }
    }
  }

  // Setup Booking Forms
  function setupBookingForms() {
    const bookingTypeInputs = document.querySelectorAll(
      'input[name="bookingType"]',
    );

    bookingTypeInputs.forEach((input) => {
      input.addEventListener("change", function () {
        toggleBookingForm();
      });
    });
  }

  // Receipt Number Generation (from guest.js)
  async function generateReceiptNumber() {
    try {
      const response = await fetch("api/receipt.php");
      const data = await response.json();

      if (data && data.success && data.receipt_no) {
        const receiptField = document.getElementById("receipt_no");
        if (receiptField) {
          receiptField.value = data.receipt_no;
          receiptField.classList.add("is-valid");
        }
        console.log("Generated receipt number:", data.receipt_no);
      } else {
        console.error(
          "Error generating receipt number:",
          data && data.error ? data.error : data,
        );
        generateFallbackReceiptNumber();
      }
    } catch (error) {
      console.error("Network error:", error);
      generateFallbackReceiptNumber();
    }
  }

  // Fallback receipt number generator (from guest.js)
  function generateFallbackReceiptNumber() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");

    const receiptNo = `BARCIE-${year}${month}${day}-${hours}${minutes}${seconds}`;

    const receiptField = document.getElementById("receipt_no");
    if (receiptField) {
      receiptField.value = receiptNo;
      receiptField.classList.add("is-valid");
    }
  }

  // Initialize receipt number when page loads
  function initializeReceiptGeneration() {
    generateReceiptNumber();
  }

  // Rooms filter logic moved to Components/Guest/RoomsAndFacilities.php/rooms-filter.php

</script>