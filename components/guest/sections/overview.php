<header class="mb-4">
  <div class="row">
    <div class="col-12">
      <h1 class="display-6 text-center mb-3">Welcome to BarCIE International Center</h1>
      <p class="lead text-center text-muted">Explore our rooms and facilities, make bookings without any account required!</p>
    </div>
  </div>
</header>

<section id="overview" class="content-section">
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h3 class="card-title mb-2">Welcome!</h3>
              <p class="card-text mb-0">Explore our facilities, make instant bookings, and discover everything BarCIE International Center has to offer. No account required!</p>
            </div>
            <div class="col-md-4 text-center">
              <i class="fas fa-hotel fa-3x opacity-75"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <div class="text-primary mb-3">
            <i class="fas fa-bed fa-2x"></i>
          </div>
          <h4 class="card-title text-primary" id="total-rooms">0</h4>
          <p class="card-text text-muted">Total Rooms</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <div class="text-success mb-3">
            <i class="fas fa-building fa-2x"></i>
          </div>
          <h4 class="card-title text-success" id="total-facilities">0</h4>
          <p class="card-text text-muted">Facilities</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
      <div class="card text-center h-100 available-now-card" style="cursor: pointer;" onclick="scrollToAvailability()" title="Click to view availability calendar">
        <div class="card-body">
          <div class="text-info mb-3">
            <i class="fas fa-check-circle fa-2x"></i>
          </div>
          <h4 class="card-title text-info" id="available-rooms">0</h4>
          <p class="card-text text-muted">Available Now</p>
         
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
              <button class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="showSection('rooms')">
                <i class="fas fa-search fa-3x mb-3"></i>
                <span class="fw-bold">Browse Rooms</span>
                <small class="text-muted mt-1">Explore our accommodations</small>
              </button>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
              <button class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="showSection('booking')">
                <i class="fas fa-plus-circle fa-3x mb-3"></i>
                <span class="fw-bold">Make Booking</span>
                <small class="text-muted mt-1">Reserve your stay today</small>
              </button>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
              <button class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="showSection('availability')" aria-label="Check Availability">
                <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                <span class="fw-bold">Check Availability</span>
                <small class="text-muted mt-1">View availability calendar</small>
              </button>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
              <button class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4" onclick="showSection('feedback')">
                <i class="fas fa-star fa-3x mb-3"></i>
                <span class="fw-bold">Give Feedback</span>
                <small class="text-muted mt-1">Share your experience</small>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-star me-2"></i>Featured Rooms & Facilities</h5>
        </div>
        <div class="card-body">
          <div id="featured-items" class="row"></div>
          <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="showSection('rooms')">
              View All Rooms & Facilities <i class="fas fa-arrow-right ms-1"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <h6 class="text-primary"><i class="fas fa-clock me-1"></i> Check-in Time</h6>
            <p class="mb-0 text-muted">2:00 PM onwards</p>
          </div>
          <div class="mb-3">
            <h6 class="text-primary"><i class="fas fa-clock me-1"></i> Check-out Time</h6>
            <p class="mb-0 text-muted">12:00 PM</p>
          </div>
          <div class="mb-3">
            <h6 class="text-primary"><i class="fas fa-phone me-1"></i> Contact</h6>
            <p class="mb-0 text-muted">+63 912 345 6789</p>
          </div>
          <div class="mb-3">
            <h6 class="text-primary"><i class="fas fa-wifi me-1"></i> WiFi</h6>
            <p class="mb-0 text-muted">Free High-Speed Internet</p>
          </div>
          <div>
            <h6 class="text-primary"><i class="fas fa-car me-1"></i> Parking</h6>
            <p class="mb-0 text-muted">Complimentary Parking</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
