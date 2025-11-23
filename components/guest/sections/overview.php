<section id="overview" class="content-section">
  <!-- Hero Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-lg border-0 overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <div class="card-body p-5 text-center">
          <div class="text-white">
            <i class="fas fa-hotel mb-3" style="font-size: 4rem; opacity: 0.9;"></i>
            <h1 class="display-3 fw-bold mb-3">Welcome to BarCIE International Center</h1>
            <p class="fs-5 mb-4 opacity-90">La Consolacion University Philippines - Your Premier Hospitality Destination</p>
            <button class="btn btn-light btn-lg px-5 py-3 shadow-sm" onclick="showSection('booking')">
              <i class="fas fa-calendar-check me-2"></i>Book Your Stay Now
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #2a5298 !important;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                 style="width: 60px; height: 60px; background-color: #2a5298;">
              <i class="fas fa-bed fa-2x text-white"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0" id="total-rooms" style="color: #2a5298;">0</h3>
              <p class="text-muted mb-0 small">Premium Rooms</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #4a90e2 !important;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                 style="width: 60px; height: 60px; background-color: #4a90e2;">
              <i class="fas fa-building fa-2x text-white"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0" id="total-facilities" style="color: #4a90e2;">0</h3>
              <p class="text-muted mb-0 small">Modern Facilities</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #5cb85c !important;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                 style="width: 60px; height: 60px; background-color: #5cb85c;">
              <i class="fas fa-check-circle fa-2x text-white"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0" id="available-rooms" style="color: #5cb85c;">0</h3>
              <p class="text-muted mb-0 small">Available Now</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- How to Book Instructions -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow">
        <div class="card-header bg-white py-3" style="border-bottom: 3px solid #2a5298;">
          <h3 class="mb-0 fw-bold" style="color: #2a5298;">
            <i class="fas fa-book me-2"></i>How to Book Your Stay
          </h3>
        </div>
        <div class="card-body p-4">
          <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; background-color: #2a5298;">
                  <span class="text-white fw-bold" style="font-size: 2rem;">1</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #2a5298;">Browse Rooms</h5>
                <p class="text-muted small">Go to "Rooms & Facilities" to view available accommodations and their amenities</p>
                <button class="btn btn-outline-primary btn-sm" onclick="showSection('rooms')">
                  <i class="fas fa-arrow-right me-1"></i>Browse Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; background-color: #4a90e2;">
                  <span class="text-white fw-bold" style="font-size: 2rem;">2</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #4a90e2;">Check Availability</h5>
                <p class="text-muted small">View the availability calendar to check if your preferred dates are available</p>
                <button class="btn btn-outline-primary btn-sm" onclick="showSection('availability')">
                  <i class="fas fa-calendar me-1"></i>Check Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; background-color: #5cb85c;">
                  <span class="text-white fw-bold" style="font-size: 2rem;">3</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #5cb85c;">Make Reservation</h5>
                <p class="text-muted small">Fill out the booking form with your details and select your check-in/check-out dates</p>
                <button class="btn btn-outline-success btn-sm" onclick="showSection('booking')">
                  <i class="fas fa-edit me-1"></i>Book Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 80px; height: 80px; background-color: #f0ad4e;">
                  <span class="text-white fw-bold" style="font-size: 2rem;">4</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #f0ad4e;">Confirmation</h5>
                <p class="text-muted small">Receive booking confirmation via email with your receipt number and payment details</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Important Information -->
  <div class="row">
    <div class="col-lg-8 mb-4">
      <div class="card border-0 shadow">
        <div class="card-header bg-white py-3" style="border-bottom: 3px solid #2a5298;">
          <h3 class="mb-0 fw-bold" style="color: #2a5298;">
            <i class="fas fa-info-circle me-2"></i>Important Information
          </h3>
        </div>
        <div class="card-body p-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-check-circle fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Non-Refundable Bookings</h6>
                  <p class="text-muted small mb-0">All bookings are non-refundable once confirmed. Please ensure your booking details are correct.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-clock fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Check-in: 2:00 PM | Check-out: 12:00 PM</h6>
                  <p class="text-muted small mb-0">Early check-in or late check-out may be available upon request and availability.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-id-card fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Valid ID Required</h6>
                  <p class="text-muted small mb-0">Please present a valid government-issued ID upon check-in for verification purposes.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-credit-card fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Payment Options</h6>
                  <p class="text-muted small mb-0">We accept bank transfers, GCash, and cash payments. Payment instructions will be sent via email.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-lg-4 mb-4">
      <div class="card border-0 shadow h-100">
        <div class="card-header bg-white py-3" style="border-bottom: 3px solid #2a5298;">
          <h3 class="mb-0 fw-bold" style="color: #2a5298;">
            <i class="fas fa-phone me-2"></i>Contact Us
          </h3>
        </div>
        <div class="card-body p-4">
          <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                   style="width: 45px; height: 45px; background-color: #2a5298;">
                <i class="fas fa-phone text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Phone</p>
                <p class="mb-0 fw-bold">+63 912 345 6789</p>
              </div>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                   style="width: 45px; height: 45px; background-color: #2a5298;">
                <i class="fas fa-envelope text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Email</p>
                <p class="mb-0 fw-bold">barcie@lcup.edu.ph</p>
              </div>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                   style="width: 45px; height: 45px; background-color: #2a5298;">
                <i class="fas fa-map-marker-alt text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Location</p>
                <p class="mb-0 fw-bold">La Consolacion University Philippines</p>
              </div>
            </div>
          </div>
          <hr>
          <h6 class="fw-bold mb-3" style="color: #2a5298;">Amenities</h6>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark border"><i class="fas fa-wifi me-1"></i>Free WiFi</span>
            <span class="badge bg-light text-dark border"><i class="fas fa-car me-1"></i>Free Parking</span>
            <span class="badge bg-light text-dark border"><i class="fas fa-coffee me-1"></i>Breakfast</span>
            <span class="badge bg-light text-dark border"><i class="fas fa-concierge-bell me-1"></i>24/7 Service</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
