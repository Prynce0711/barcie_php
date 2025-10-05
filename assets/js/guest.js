// Toggle for Booking Form
function toggleBookingForm() {
  const type = document.querySelector('input[name="bookingType"]:checked').value;
  document.getElementById('reservationForm').style.display = (type === 'reservation') ? 'block' : 'none';
  document.getElementById('pencilForm').style.display = (type === 'pencil') ? 'block' : 'none';
  
  // Generate new receipt number when switching to reservation form
  if (type === 'reservation') {
    generateReceiptNumber();
  }
}

function pencilReminder() {
  alert("Reminder: We only allow two (2) weeks to pencil book. If we have not heard back from you after two weeks, your pencil booking will become null and void and deleted from our system.");
  return true;
}

function reservationReminder() {
  alert("Reminder: We only alloW one (1) week to pencil book. If we have not heard back from you after one week, your reservation will become null and void and deleted from our system. CONFIRMED ROOM RESERVATION IS NON-REFUNDABLE.");
  return true;
}

// Generate Receipt Number from Database
async function generateReceiptNumber() {
  try {
    const response = await fetch('database/user_auth.php?action=get_receipt_no');
    const data = await response.json();
    
    if (data.success) {
      const receiptField = document.getElementById('receipt_no');
      if (receiptField) {
        receiptField.value = data.receipt_no;
      }
      console.log('Generated receipt number:', data.receipt_no);
    } else {
      console.error('Error generating receipt number:', data.error);
      // Fallback to simple number
      generateFallbackReceiptNumber();
    }
  } catch (error) {
    console.error('Network error:', error);
    // Fallback to simple number
    generateFallbackReceiptNumber();
  }
}

// Fallback receipt number generator
function generateFallbackReceiptNumber() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  
  const receiptNo = `BARCIE-${year}${month}${day}-${hours}${minutes}${seconds}`;
  
  const receiptField = document.getElementById('receipt_no');
  if (receiptField) {
    receiptField.value = receiptNo;
  }
}

// Initialize receipt number when page loads
document.addEventListener('DOMContentLoaded', function() {
  // Generate receipt number for reservation form when page loads
  generateReceiptNumber();
});




// ==========================================
// CARD FILTERING
// ==========================================   
  // Filter cards based on radio selection
  const radios = document.querySelectorAll('input[name="type"]');
  const cards = document.querySelectorAll('.card');

  radios.forEach(radio => {
    radio.addEventListener('change', () => {
      const selectedType = document.querySelector('input[name="type"]:checked').value;
      cards.forEach(card => {
        if (card.dataset.type === selectedType) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // Initialize: show only rooms by default
  window.addEventListener('DOMContentLoaded', () => {
    document.querySelector('input[name="type"]:checked').dispatchEvent(new Event('change'));
  });