// Toggle for Booking Form


    function toggleBookingForm() {
      const type = document.querySelector('input[name="bookingType"]:checked').value;
      document.getElementById('reservationForm').style.display = (type === 'reservation') ? 'block' : 'none';
      document.getElementById('pencilForm').style.display = (type === 'pencil') ? 'block' : 'none';
    }

    function pencilReminder() {
      alert("Reminder: We only allow two (2) weeks to pencil book. If we have not heard back from you after two weeks, your pencil booking will become null and void and deleted from our system.");
      return true;
    }

    function reservationReminder() {
      alert("Reminder: We only alloW one (1) week to pencil book. If we have not heard back from you after one week, your reservation will become null and void and deleted from our system. CONFIRMED ROOM RESERVATION IS NON-REFUNDABLE.");
      return true;
    }




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