<?php
// guest.php
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Guest Page - BarCIE</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
</head>


<body>

  <!-- Sidebar -->
  <div id="sidebar" class="sidebar guest">
    <h2>Guest Panel</h2>
    <a onclick="showSection('home')">Home</a>
    <a onclick="showSection('schedule')">schedule</a>
    <a onclick="showSection('rooms-facilities')">Rooms</a>
    <a onclick="showSection('contacts')">Contacts</a>
    <a href = "index.php" >Go back to homepage</a>
  </div>
 

  <!-- Main Content -->
  <div class="main-content-guest">
    <!-- Header-->
    <header id="header" class="header">
      <h1>Welcome to BarCIE International Center</h1>
      <p>We are delighted to have you as our guest. Explore our rooms, facilities, and get in touch with us through
        this
        guest portal.</p>
    </header>

    <!-- Home / Reminder Section -->
    <div id="home" class="content-section active card">
      <h1>📝 Reminders</h1>
      <div class="reminder-container">
        <!-- Left: Reminder list -->
        <div class="reminder-left">
          <ul class="reminder-list">
            <li>✅ Please check room availability before booking.</li>
            <li>✅ Facilities are subject to reservation and approval.</li>
            <li>✅ Contact the office for inquiries during office hours.</li>
            <li>✅ Bring valid ID for verification upon check-in.</li>
            <li>✅ Keep the facilities clean and orderly after use.</li>
          </ul>
        </div>

        <!-- Right: Steps box -->
        <div class="reminder-right">
          <h3>STEPS TO BOOK</h3>
          <ul>
            <li>📅 Check availability of rooms and halls</li>
            <li>📝 Fill out the reservation form</li>
            <li>📧 Wait for email confirmation/approval</li>
            <li>📊 Track your request status from the dashboard</li>
          </ul>
        </div>
      </div>
    </div>





    <!-- Schedule Section -->
    <div id="schedule" class="content-section">
      <h1>Availability of Rooms & Facilities</h1>

      <!-- Dropdown for selecting room or facility -->
      <label for="placeSelect">Choose Room / Facility:</label>
      <select id="placeSelect">
        <!-- Rooms -->
        <optgroup label="Rooms">
          <option value="standard-room">Standard Room</option>
          <option value="deluxe-room">Deluxe Room</option>
          <option value="suite-room">Suite</option>
        </optgroup>

        <!-- Facilities -->
        <optgroup label="Facilities">
          <option value="main-hall">Main Function Hall</option>
          <option value="bayabas-hall">Bayabas Hall</option>
          <option value="langka-hall">Langka Hall</option>
          <option value="rambutan-hall">Rambutan Hall</option>
          <option value="main-lobby">Main Lobby</option>
          <option value="mini-pool">Mini Pool & Cottage</option>
        </optgroup>
      </select>

      <!-- Calendar -->
      <div id="calendar"></div>
    </div>




  
    <!-- Rooms & Facilities Section -->
<div id="rooms-facilities" class="content-section">
  <h1>Rooms & Facilities</h1>

  <!-- Radio buttons to filter -->
  <div class="filter-options">
    <label><input type="radio" name="typeFilter" value="room" checked> Rooms</label>
    <label><input type="radio" name="typeFilter" value="facility"> Facilities</label>
    <label><input type="radio" name="typeFilter" value="all"> Show All</label>
  </div>

  <div class="cards-grid">
    <!-- Rooms -->
    <div class="room-card type-room">
      <img src="images/standard.jpg" alt="Standard Room">
      <div class="room-info">
        <h3>Standard Room</h3>
        <p>₱1,200 / night</p>
        <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
      </div>
    </div>

    <div class="room-card type-room">
      <img src="images/deluxe.jpg" alt="Deluxe Room">
      <div class="room-info">
        <h3>Deluxe Room</h3>
        <p>₱2,000 / night</p>
        <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
      </div>
    </div>

    <div class="room-card type-room">
      <img src="images/suite.jpg" alt="Suite">
      <div class="room-info">
        <h3>Suite</h3>
        <p>₱3,500 / night</p>
        <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
      </div>
    </div>

    <!-- Facilities -->
    <div class="facility-card type-facility">
      <img src="images/main-hall.jpg" alt="Main Function Hall">
      <h3>Main Function Hall</h3>
      <p>Perfect for conferences, seminars, and social gatherings.<br>Capacity: 300 guests.</p>
      <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
    </div>

    <div class="facility-card type-facility">
      <img src="images/bayabas-hall.jpg" alt="Bayabas Hall">
      <h3>Bayabas Hall</h3>
      <p>Spacious hall ideal for medium-sized gatherings and celebrations.</p>
      <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
    </div>

    <div class="facility-card type-facility">
      <img src="images/langka-hall.jpg" alt="Langka Hall">
      <h3>Langka Hall</h3>
      <p>Designed for intimate events, meetings, or small group sessions.</p>
      <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
    </div>

    <div class="facility-card type-facility">
      <img src="images/rambutan-hall.jpg" alt="Rambutan Hall">
      <h3>Rambutan Hall</h3>
      <p>Great for training sessions, workshops, or private functions.</p>
      <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
    </div>

    <div class="facility-card type-facility">
      <img src="images/main-lobby.jpg" alt="Main Lobby">
      <h3>Main Lobby</h3>
      <p>Elegant reception area to welcome your guests in style.</p>
      <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
    </div>

    <div class="facility-card type-facility">
      <img src="images/pool-cottage.jpg" alt="Mini Pool & Cottage">
      <h3>Mini Pool & Cottage</h3>
      <p>Relaxing pool area with cottages, perfect for family bonding and leisure.</p>
      <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
    </div>
  </div>
</div>



    <!-- Booking Form Section (Hidden by default) -->
    <section id="bookingFormSection" class="content-section">
      <h2>Booking Form</h2>

      <!-- Booking Type -->
      <label>
        <input type="radio" name="bookingType" value="pencil" checked> Pencil Booking Slip (Function Room)
      </label>
      <label>
        <input type="radio" name="bookingType" value="reservation"> Reservation Form
      </label>

      <form id="bookingForm">
        <!-- Pencil Booking Fields -->
        <div id="pencilFields">
          <label>Date of Pencil: <input type="date" name="pencil_date" required></label><br>
          <label>Event Type: <input type="text" name="event_type" required></label><br>
          <label>Function Hall: <input type="text" name="function_hall" required></label><br>
          <label>Number of Pax: <input type="number" name="num_pax" required></label><br>
          <label>Date of Event: <input type="date" name="event_date" required></label><br>
          <label>Time of Event (From): <input type="time" name="time_from" required></label><br>
          <label>Time of Event (To): <input type="time" name="time_to" required></label><br>
          <label>Food Provider/Caterer: <input type="text" name="caterer"></label><br>
          <label>Contact Person: <input type="text" name="contact_person"></label><br>
          <label>Contact Numbers: <input type="text" name="contact_numbers"></label><br>
          <label>Company Affiliation (if any): <input type="text" name="company_affiliation"></label><br>
          <label>Company Contact Number: <input type="text" name="company_contact_number"></label><br>
          <label>Front Desk Officer: <input type="text" name="front_desk_officer"></label><br>
          <p><strong>Reminder:</strong> We only allow two (2) weeks to pencil book. If we have not heard back from you
            after two weeks, your pencil booking will become null and void and deleted from our system.</p>
        </div>

        <!-- Reservation Form Fields -->
        <div id="reservationFields" style="display:none;">
          <label>Date: <input type="date" name="res_date" required></label><br>
          <label>Guest Name: <input type="text" name="guest_name" required></label><br>
          <label>Contact Number: <input type="text" name="guest_contact" required></label><br>
          <label>Check-in Date & Time: <input type="datetime-local" name="check_in" required></label><br>
          <label>Check-out Date & Time: <input type="datetime-local" name="check_out" required></label><br>
          <label>Number of Occupants: <input type="number" name="num_occupants" required></label><br>
          <label>Company Affiliation (if any): <input type="text" name="company_affiliation"></label><br>
          <label>Company Contact Number: <input type="text" name="company_contact_number"></label><br>
          <label>Front Desk Officer: <input type="text" name="front_desk_officer"></label><br>
          <label>Official Receipt Number: <input type="text" name="official_receipt" value="0001" readonly></label><br>
          <label>Special Request: <textarea name="special_request"></textarea></label><br>
        </div>

        <button type="submit">Submit Booking</button>
      </form>
    </section>





    <!-- Contacts Section -->
    <div id="contacts" class="content-section ">
      <!-- Left Panel -->
      <div class="contact-info">
        <h2>Contact Information</h2>
        <p>We’d love to hear from you! Reach out through any of these channels.</p>

        <div class="info-item">
          <i class="fas fa-phone"></i>
          <span>(044) 931 8600</span>
        </div>
        <div class="info-item">
          <i class="fas fa-mobile-alt"></i>
          <span>0919 002 7151 / 0933 611 8059</span>
        </div>
        <div class="info-item">
          <i class="fas fa-envelope"></i>
          <span>laconsolacionu@lcup.edu.ph</span><br>
          <span>laconsolacionu@email.lcup.edu.ph</span>
        </div>
        <div class="info-item">
          <i class="fas fa-map-marker-alt"></i>
          <a href="https://www.google.com/maps/place/Barcie+International+Center/@14.8528398,120.8114192,15.4z"
            target="_blank">
            Main Campus - Valenzuela St., Capitol View Park Subdivision, Bulihan, City of Malolos, Bulacan 3000
          </a>
        </div>

        <div class="social-icons">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
      </div>

      <!-- Right Panel (Form) -->
      <div class="contact-form">
        <form>
          <div class="form-row">
            <input type="text" placeholder="First Name" required>
            <input type="text" placeholder="Last Name" required>
          </div>
          <div class="form-row">
            <input type="email" placeholder="Email" required>
            <input type="text" placeholder="Phone Number">
          </div>

          <label>Select Subject:</label>
          <div class="subjects">
            <label><input type="radio" name="subject" checked> General Inquiry</label>
            <label><input type="radio" name="subject"> Room Booking</label>
            <label><input type="radio" name="subject"> Facilities</label>
            <label><input type="radio" name="subject"> Others</label>
          </div>

          <textarea placeholder="Write your message.." rows="5"></textarea>

          <button type="submit" class="send-btn">Send Message</button>
        </form>
      </div>
    </div>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  <script src="/barcie_php/assets/js/script.js"></script>
</body>

<footer class="footer">
  <p>© BarCIE International Center 2025</p>
</footer>

</html>