<section id="overview" class="content-section">
  <!-- Hero Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-lg border-0 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <!-- Decorative Background Pattern -->
        <div class="position-absolute w-100 h-100" style="opacity: 0.05;">
          <i class="fas fa-hotel position-absolute" style="font-size: 15rem; top: -3rem; right: -3rem; transform: rotate(-15deg);"></i>
        </div>
        <div class="card-body p-5 text-center position-relative">
          <div class="text-white hero-content">
            <div class="mb-4">
              <i class="fas fa-hotel hero-icon" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-3 fw-bold mb-3 hero-title">Welcome to BarCIE International Center</h1>
            <p class="fs-5 mb-2"><i class="fas fa-university me-2"></i>La Consolacion University Philippines</p>
            <p class="fs-6 mb-4 opacity-90">Your Premier Hospitality Destination</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
              <span class="badge bg-white text-primary px-3 py-2 fs-6"><i class="fas fa-check-circle me-1"></i>Instant Booking</span>
              <span class="badge bg-white text-primary px-3 py-2 fs-6"><i class="fas fa-user-shield me-1"></i>No Account Needed</span>
              <span class="badge bg-white text-primary px-3 py-2 fs-6"><i class="fas fa-star me-1"></i>Premium Service</span>
            </div>
            <button class="btn btn-light btn-lg px-5 py-3 shadow-sm hero-btn" onclick="showSection('booking')">
              <i class="fas fa-calendar-check me-2"></i>Book Your Stay Now
              <i class="fas fa-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 5px solid #2a5298 !important; transition: all 0.3s ease;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 stat-icon" 
                 style="width: 70px; height: 70px; background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%); box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);">
              <i class="fas fa-bed fa-2x text-white"></i>
            </div>
            <div>
              <h2 class="fw-bold mb-0 counter" id="total-rooms" style="color: #2a5298; font-size: 2.5rem;">0</h2>
              <p class="text-muted mb-0 fw-semibold">Premium Rooms</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 5px solid #4a90e2 !important; transition: all 0.3s ease;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 stat-icon" 
                 style="width: 70px; height: 70px; background: linear-gradient(135deg, #4a90e2 0%, #2a5298 100%); box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);">
              <i class="fas fa-building fa-2x text-white"></i>
            </div>
            <div>
              <h2 class="fw-bold mb-0 counter" id="total-facilities" style="color: #4a90e2; font-size: 2.5rem;">0</h2>
              <p class="text-muted mb-0 fw-semibold">Modern Facilities</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100 stat-card" style="border-left: 5px solid #5cb85c !important; transition: all 0.3s ease;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 stat-icon" 
                 style="width: 70px; height: 70px; background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%); box-shadow: 0 4px 15px rgba(92, 184, 92, 0.3);">
              <i class="fas fa-check-circle fa-2x text-white"></i>
            </div>
            <div>
              <h2 class="fw-bold mb-0 counter" id="available-rooms" style="color: #5cb85c; font-size: 2.5rem;">0</h2>
              <p class="text-muted mb-0 fw-semibold">Available Now</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- How to Book Instructions -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-lg" style="border-top: 5px solid #2a5298;">
        <div class="card-header py-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6;">
          <div class="text-center">
            <i class="fas fa-book-open mb-2" style="font-size: 2.5rem; color: #2a5298;"></i>
            <h3 class="mb-1 fw-bold" style="color: #2a5298;">How to Book Your Stay</h3>
            <p class="text-muted mb-0">Follow these simple steps to reserve your accommodation</p>
          </div>
        </div>
        <div class="card-body p-5">
          <div class="row position-relative">
            <!-- Connection Line -->
            <div class="d-none d-lg-block position-absolute" style="top: 50px; left: 50%; width: 75%; height: 2px; background: linear-gradient(90deg, #2a5298 0%, #4a90e2 33%, #5cb85c 66%, #f0ad4e 100%); transform: translateX(-50%); z-index: 0;"></div>
            
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative" 
                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%); box-shadow: 0 5px 20px rgba(42, 82, 152, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">1</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #2a5298;"><i class="fas fa-bed me-2"></i>Browse Rooms</h5>
                <p class="text-muted small mb-3">Explore our premium accommodations with detailed amenities and pricing</p>
                <button class="btn btn-primary btn-sm px-4 shadow-sm" onclick="showSection('rooms')" style="background: #2a5298; border: none;">
                  <i class="fas fa-arrow-right me-1"></i>Browse Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative" 
                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #4a90e2 0%, #2a5298 100%); box-shadow: 0 5px 20px rgba(74, 144, 226, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">2</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #4a90e2;"><i class="fas fa-calendar-alt me-2"></i>Check Availability</h5>
                <p class="text-muted small mb-3">View real-time availability calendar for your preferred dates</p>
                <button class="btn btn-primary btn-sm px-4 shadow-sm" onclick="showSection('availability')" style="background: #4a90e2; border: none;">
                  <i class="fas fa-calendar me-1"></i>Check Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative" 
                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%); box-shadow: 0 5px 20px rgba(92, 184, 92, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">3</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #5cb85c;"><i class="fas fa-edit me-2"></i>Make Reservation</h5>
                <p class="text-muted small mb-3">Complete the booking form with your details and dates</p>
                <button class="btn btn-success btn-sm px-4 shadow-sm" onclick="showSection('booking')" style="background: #5cb85c; border: none;">
                  <i class="fas fa-calendar-check me-1"></i>Book Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative" 
                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%); box-shadow: 0 5px 20px rgba(240, 173, 78, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">4</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #f0ad4e;"><i class="fas fa-envelope-open-text me-2"></i>Confirmation</h5>
                <p class="text-muted small mb-3">Receive instant confirmation with receipt and payment details via email</p>
                <div class="badge bg-warning text-dark px-3 py-2">
                  <i class="fas fa-check-circle me-1"></i>Instant Confirmation
                </div>
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
            <span class="badge amenity-badge" style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i class="fas fa-wifi me-1"></i>Free WiFi</span>
            <span class="badge amenity-badge" style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i class="fas fa-car me-1"></i>Free Parking</span>
            <span class="badge amenity-badge" style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i class="fas fa-coffee me-1"></i>Breakfast</span>
            <span class="badge amenity-badge" style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i class="fas fa-concierge-bell me-1"></i>24/7 Service</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* Hero Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.hero-content {
  animation: fadeInUp 0.8s ease-out;
}

.hero-icon {
  animation: float 3s ease-in-out infinite;
}

.hero-title {
  animation: fadeInUp 0.8s ease-out 0.2s backwards;
}

.hero-btn {
  transition: all 0.3s ease;
  animation: fadeInUp 0.8s ease-out 0.4s backwards;
}

.hero-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
}

/* Stat Cards Hover Effect */
.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
}

.stat-icon {
  transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
  transform: rotate(360deg) scale(1.1);
}

/* Booking Steps Animation */
.booking-step {
  transition: all 0.3s ease;
}

.booking-step:hover {
  transform: translateY(-10px);
}

.step-number {
  transition: all 0.3s ease;
}

.booking-step:hover .step-number {
  transform: scale(1.15);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
}

/* Amenity Badges */
.amenity-badge {
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 0.9rem;
  padding: 0.5rem 0.8rem;
}

.amenity-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);
  background-color: #2a5298 !important;
  color: white !important;
}

/* Info Section Hover */
.col-md-6:hover .fa-check-circle,
.col-md-6:hover .fa-clock,
.col-md-6:hover .fa-id-card,
.col-md-6:hover .fa-credit-card {
  animation: pulse 1s ease-in-out;
}

/* Contact Icons Pulse */
.rounded-circle:has(.fa-phone),
.rounded-circle:has(.fa-envelope),
.rounded-circle:has(.fa-map-marker-alt) {
  transition: all 0.3s ease;
}

.d-flex:has(.rounded-circle):hover .rounded-circle {
  transform: scale(1.1);
  box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem !important;
  }
  
  .step-number {
    width: 80px !important;
    height: 80px !important;
  }
  
  .step-number span {
    font-size: 2rem !important;
  }
}
</style>
