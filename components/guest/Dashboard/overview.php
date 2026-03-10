<style>
  /* Overview scoped styles */
  .hero-carousel-wrapper {
    min-height: 500px;
  }

  .hero-slide {
    height: 100%;
    background-size: cover;
    background-position: center;
  }

  .hero-btn:hover {
    transform: translateY(-3px) scale(1.05);
    filter: brightness(1.1);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3) !important;
  }

  .booking-step {
    transition: transform 0.3s ease;
  }

  .booking-step:hover {
    transform: translateY(-5px);
  }

  /* Notification styles */
  .notification {
    padding: 15px 20px;
    border-radius: 12px;
    margin: 15px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    position: relative;
    overflow: hidden;
  }

  .notification::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: currentColor;
  }

  .notification.success {
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(39, 174, 96, 0.05));
    border: 2px solid rgba(46, 204, 113, 0.3);
    color: #27ae60;
  }

  .notification.error {
    background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), rgba(192, 57, 43, 0.05));
    border: 2px solid rgba(231, 76, 60, 0.3);
    color: #e74c3c;
  }

  .notification.info {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(41, 128, 185, 0.05));
    border: 2px solid rgba(52, 152, 219, 0.3);
    color: #3498db;
  }

  .notification.warning {
    background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), rgba(230, 126, 34, 0.05));
    border: 2px solid rgba(243, 156, 18, 0.3);
    color: #f39c12;
  }

  /* Header */
  header {
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid rgba(52, 152, 219, 0.3);
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  header h1 {
    font-size: 2.2rem;
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 8px;
  }

  header p {
    color: #546e7a;
    font-weight: 500;
    font-size: 1rem;
    margin: 0;
  }

  /* Detail item */
  .detail-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
  }

  .detail-item:last-child {
    border-bottom: none;
  }

  @media (max-width: 768px) {
    header {
      padding: 25px 20px;
      margin-top: 60px;
    }

    header h1 {
      font-size: 2rem;
    }
  }

  @media (max-width: 480px) {
    header {
      padding: 20px 15px;
      margin-top: 60px;
    }

    header h1 {
      font-size: 1.7rem;
    }
  }
</style>

<section id="overview"
  class="content-section bg-white/95 border-2 border-[rgba(52,152,219,0.2)] p-[30px] mb-[30px] rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] relative z-[1]">
  <!-- Hero Banner with Carousel Background -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-lg border-0 overflow-hidden position-relative hero-carousel-wrapper"
        style="min-height: 500px;">
        <!-- Background Carousel -->
        <div class="hero-carousel-container position-absolute w-100 h-100"
          style="background: linear-gradient(135deg, #0d1f3d 0%, #1a3a5c 100%);">
          <div class="hero-carousel d-flex" style="transition: transform 0.5s ease-in-out; opacity: 0.2;">
            <div class="hero-slide" style="background-image: url('public/images/Lobby/BarCIE-9.1.jpg');"></div>
            <div class="hero-slide" style="background-image: url('public/images/Lobby/BarCIE-8.jpg');"></div>
            <div class="hero-slide" style="background-image: url('public/images/Lobby/BarCIE-9.2.jpg');"></div>
            <div class="hero-slide" style="background-image: url('public/images/Lobby/BarCIE-1.jpg');"></div>
            <div class="hero-slide" style="background-image: url('public/images/Lobby/BarCIE-9.3.jpg');"></div>
          </div>
        </div>

        <!-- Content Over Carousel -->
        <div class="card-body py-5 px-4 text-center position-relative"
          style="z-index: 2; min-height: 500px; display: flex; align-items: center; justify-content: center;">
          <div class="hero-content" style="max-width: 900px;">
            <!-- BarCIE Logo -->
            <div class="mb-4 d-flex justify-content-center align-items-center">
              <img src="public/images/imageBg/barcie_logo.jpg" alt="BarCIE Logo" class="hero-logo"
                style="height: 120px; width: auto; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.5);">
            </div>

            <h1 class="display-2 fw-bold mb-4 hero-title"
              style="color: #FFFFFF; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); letter-spacing: -1px; line-height: 1.2;">
              Welcome to BarCIE International Center
            </h1>

            <p class="fs-3 mb-3 fw-semibold" style="color: #FFFFFF; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">
              <i class="fas fa-university me-2"></i>La Consolacion University Philippines
            </p>

            <p class="fs-5 mb-5 fw-light"
              style="color: #E8F4F8; text-shadow: 2px 2px 6px rgba(0,0,0,0.7); line-height: 1.6;">
              The Laboratory of the College of International Tourism and Hospitality Management
            </p>

            <button class="btn btn-lg px-5 py-4 shadow-lg hero-btn fw-bold" onclick="showSection('booking')"
              style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #1a3a5c; border: 3px solid #FFFFFF; border-radius: 50px; font-size: 1.2rem; transition: all 0.4s ease;">
              <i class="fas fa-calendar-check me-2"></i>Book Your Stay Now
              <i class="fas fa-arrow-right ms-2"></i>
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- How to Book Instructions -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-lg" style="border-top: 5px solid #2a5298;">
        <div class="card-header py-4"
          style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6;">
          <div class="text-center">
            <i class="fas fa-book-open mb-2" style="font-size: 2.5rem; color: #2a5298;"></i>
            <h3 class="mb-1 fw-bold" style="color: #2a5298;">How to Book Your Stay</h3>
            <p class="text-muted mb-0">Follow these simple steps to reserve your accommodation</p>
          </div>
        </div>
        <div class="card-body p-5">
          <div class="row position-relative">
            <!-- Connection Line -->
            <div class="d-none d-lg-block position-absolute"
              style="top: 50px; left: 50%; width: 75%; height: 2px; background: linear-gradient(90deg, #2a5298 0%, #4a90e2 33%, #5cb85c 66%, #f0ad4e 100%); transform: translateX(-50%); z-index: 0;">
            </div>

            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div
                  class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative"
                  style="width: 100px; height: 100px; background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%); box-shadow: 0 5px 20px rgba(42, 82, 152, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">1</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #2a5298;"><i class="fas fa-bed me-2"></i>Browse Rooms</h5>
                <p class="text-muted small mb-3">Explore our premium accommodations with detailed amenities and pricing
                </p>
                <button class="btn btn-primary btn-sm px-4 shadow-sm" onclick="showSection('rooms')"
                  style="background: #2a5298; border: none;">
                  <i class="fas fa-arrow-right me-1"></i>Browse Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div
                  class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative"
                  style="width: 100px; height: 100px; background: linear-gradient(135deg, #4a90e2 0%, #2a5298 100%); box-shadow: 0 5px 20px rgba(74, 144, 226, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">2</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #4a90e2;"><i class="fas fa-calendar-alt me-2"></i>Check
                  Availability</h5>
                <p class="text-muted small mb-3">View real-time availability calendar for your preferred dates</p>
                <button class="btn btn-primary btn-sm px-4 shadow-sm" onclick="showSection('availability')"
                  style="background: #4a90e2; border: none;">
                  <i class="fas fa-calendar me-1"></i>Check Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div
                  class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative"
                  style="width: 100px; height: 100px; background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%); box-shadow: 0 5px 20px rgba(92, 184, 92, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">3</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #5cb85c;"><i class="fas fa-edit me-2"></i>Make Reservation</h5>
                <p class="text-muted small mb-3">Complete the booking form with your details and dates</p>
                <button class="btn btn-success btn-sm px-4 shadow-sm" onclick="showSection('booking')"
                  style="background: #5cb85c; border: none;">
                  <i class="fas fa-calendar-check me-1"></i>Book Now
                </button>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 position-relative">
              <div class="text-center booking-step">
                <div
                  class="step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-3 position-relative"
                  style="width: 100px; height: 100px; background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%); box-shadow: 0 5px 20px rgba(240, 173, 78, 0.4); z-index: 1;">
                  <span class="text-white fw-bold" style="font-size: 2.5rem;">4</span>
                </div>
                <h5 class="fw-bold mb-2" style="color: #f0ad4e;"><i
                    class="fas fa-envelope-open-text me-2"></i>Confirmation</h5>
                <p class="text-muted small mb-3">Receive instant confirmation with receipt and payment details via email
                </p>
                <div class="badge bg-warning text-dark px-3 py-2">
                  <i class="fas fa-check-circle me-1"></i>Instant Confirmation
                </div>
              </div>
            </div>
          </div>

          <!-- Additional Step: Share Feedback -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="alert alert-info border-0 shadow-sm"
                style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196f3 !important;">
                <div class="d-flex align-items-start">
                  <div class="me-3" style="color: #1976d2;">
                    <i class="fas fa-star fa-2x"></i>
                  </div>
                  <div class="grow">
                    <h5 class="fw-bold mb-2" style="color: #1976d2;">
                      <i class="fas fa-plus-circle me-2"></i>After Your Stay: Share Your Experience
                    </h5>
                    <p class="mb-3">Help future guests by leaving a review! After your stay, you can rate and review
                      your room experience. Your feedback helps us maintain quality service and assists others in making
                      informed decisions.</p>
                    <div class="d-flex flex-wrap gap-2">
                      <span class="badge"
                        style="background-color: #1976d2; font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                        <i class="fas fa-star me-1"></i>Rate Your Stay
                      </span>
                      <span class="badge"
                        style="background-color: #1976d2; font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                        <i class="fas fa-comment me-1"></i>Share Your Thoughts
                      </span>
                      <span class="badge"
                        style="background-color: #1976d2; font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                        <i class="fas fa-user-secret me-1"></i>Anonymous Option Available
                      </span>
                      <span class="badge"
                        style="background-color: #f0ad4e; color: #333; font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                        <i class="fas fa-shield-alt me-1"></i>Admin Approval Required
                      </span>
                    </div>
                  </div>
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
                  <p class="text-muted small mb-0">All bookings are non-refundable once confirmed. Please ensure your
                    booking details are correct.</p>
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
                  <p class="text-muted small mb-0">Early check-in or late check-out may be available upon request and
                    availability.</p>
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
                  <p class="text-muted small mb-0">Please present a valid government-issued ID upon check-in for
                    verification purposes.</p>
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
                  <p class="text-muted small mb-0">We accept bank transfers (BDO / BPI) only. Payment instructions will
                    be sent via email.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-ban fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Cancellation Policy</h6>
                  <p class="text-muted small mb-0">Strict no-cancellation policy. No refunds or rescheduling. Please
                    confirm dates before booking.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-smoking-ban fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">No Smoking Policy</h6>
                  <p class="text-muted small mb-0">All rooms and indoor facilities are strictly non-smoking. Designated
                    smoking areas available.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-users fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Guest Capacity</h6>
                  <p class="text-muted small mb-0">Each room has a maximum occupancy. Extra guests may incur additional
                    charges.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-shield-alt fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Security Deposit</h6>
                  <p class="text-muted small mb-0">A refundable security deposit may be required upon check-in for
                    incidental charges.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-receipt fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Proof of Payment Required</h6>
                  <p class="text-muted small mb-0">Please present your payment confirmation and booking receipt upon
                    check-in.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-child fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Children & Pets</h6>
                  <p class="text-muted small mb-0">Children are welcome. Pets are not allowed unless specified. Please
                    inquire in advance.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Damage Liability</h6>
                  <p class="text-muted small mb-0">Guests are responsible for any damage to room property. Charges will
                    apply accordingly.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-start">
                <div class="me-3" style="color: #2a5298;">
                  <i class="fas fa-bell-slash fa-lg"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">Quiet Hours</h6>
                  <p class="text-muted small mb-0">Please observe quiet hours from 10:00 PM to 7:00 AM to respect other
                    guests.</p>
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
                <i class="fab fa-viber text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Viber</p>
                <p class="mb-0 fw-bold">0939 905 7425</p>
              </div>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 45px; height: 45px; background-color: #2a5298;">
                <i class="fas fa-phone text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Telephone</p>
                <p class="mb-0 fw-bold">044 791 7424 / 044 919 8410</p>
              </div>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 45px; height: 45px; background-color: #2a5298;">
                <i class="fas fa-envelope text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Email</p>
                <p class="mb-0 fw-bold">barcieinternationalcenter@gmail.com</p>
                <p class="mb-0 fw-bold">barcie@lcup.edu.ph</p>
              </div>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 45px; height: 45px; background-color: #2a5298;">
                <i class="fas fa-map-marker-alt text-white"></i>
              </div>
              <div>
                <p class="mb-0 small text-muted">Address</p>
                <p class="mb-0 fw-bold">Valenzuela St. Capitol View Park Subd. Brgy. Bulihan, City of Malolos, Bulacan
                  3000</p>
              </div>
            </div>
          </div>
          <hr>
          <h6 class="fw-bold mb-3" style="color: #2a5298;">Amenities</h6>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge amenity-badge"
              style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i
                class="fas fa-wifi me-1"></i>Free WiFi</span>
            <span class="badge amenity-badge"
              style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i
                class="fas fa-car me-1"></i>Free Parking</span>
            <span class="badge amenity-badge"
              style="background-color: #e3f2fd; color: #2a5298; border: 1px solid #2a5298;"><i
                class="fas fa-concierge-bell me-1"></i>24/7 Service</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  /* Hero Carousel Styles */
  .hero-carousel-wrapper {
    position: relative;
    overflow: hidden;
  }

  .hero-carousel-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
  }

  .hero-carousel {
    display: flex;
    width: 500%;
    height: 100%;
    transition: transform 0.8s ease-in-out;
    will-change: transform;
  }

  .hero-slide {
    flex-shrink: 0;
    width: 20%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  .carousel-indicators-custom {
    display: flex;
    gap: 10px;
    padding: 10px 20px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 20px;
    backdrop-filter: blur(10px);
  }

  .indicator-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .indicator-dot.active {
    background: white;
    width: 30px;
    border-radius: 6px;
  }

  .indicator-dot:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.2);
  }

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

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-30px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes pulse {

    0%,
    100% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.05);
    }
  }

  @keyframes float {

    0%,
    100% {
      transform: translateY(0);
    }

    50% {
      transform: translateY(-10px);
    }
  }

  @keyframes slideInLeft {
    from {
      opacity: 0;
      transform: translateX(-50px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  @keyframes slideInRight {
    from {
      opacity: 0;
      transform: translateX(50px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .hero-content {
    animation: fadeInUp 1s ease-out;
  }

  .hero-logo {
    animation: fadeInDown 1s ease-out;
  }

  .hero-icon {
    animation: float 3s ease-in-out infinite, fadeInDown 1s ease-out;
  }

  .hero-title {
    animation: fadeInUp 1s ease-out 0.3s backwards;
  }

  .hero-content p:nth-of-type(1) {
    animation: slideInLeft 1s ease-out 0.5s backwards;
  }

  .hero-content p:nth-of-type(2) {
    animation: slideInRight 1s ease-out 0.6s backwards;
  }

  .hero-content .badge {
    animation: fadeInUp 0.8s ease-out backwards;
  }

  .hero-content .badge:nth-child(1) {
    animation-delay: 0.7s;
  }

  .hero-content .badge:nth-child(2) {
    animation-delay: 0.8s;
  }

  .hero-content .badge:nth-child(3) {
    animation-delay: 0.9s;
  }

  .hero-btn {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    animation: fadeInUp 1s ease-out 1s backwards;
  }

  .hero-btn:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3) !important;
    background: linear-gradient(135deg, #FFA500 0%, #FFD700 100%) !important;
    border-color: #FFD700 !important;
  }

  .hero-badge:hover {
    transform: translateY(-3px) scale(1.05);
    transition: all 0.3s ease;
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

<script>
  // Hero Carousel Auto-play
  (function () {
    let currentSlide = 0;
    const totalSlides = 5;
    let autoPlayInterval;

    function switchSlide(slideIndex) {
      currentSlide = slideIndex;
      const carousel = document.querySelector('.hero-carousel');
      const offset = currentSlide * -20; // Each slide is 20% width
      carousel.style.transform = `translateX(${offset}%)`;

      // Update indicators
      document.querySelectorAll('.indicator-dot').forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
      });
    }

    function nextSlide() {
      currentSlide = (currentSlide + 1) % totalSlides;
      switchSlide(currentSlide);
    }

    function startAutoPlay() {
      autoPlayInterval = setInterval(nextSlide, 3000); // Change every 3 seconds
    }

    function stopAutoPlay() {
      if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
      }
    }

    function resetAutoPlay() {
      stopAutoPlay();
      startAutoPlay();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
      // Set up indicator click handlers
      document.querySelectorAll('.indicator-dot').forEach((dot, index) => {
        dot.addEventListener('click', function () {
          switchSlide(index);
          resetAutoPlay();
        });
      });

      // Pause on hover
      const carouselWrapper = document.querySelector('.hero-carousel-wrapper');
      if (carouselWrapper) {
        carouselWrapper.addEventListener('mouseenter', stopAutoPlay);
        carouselWrapper.addEventListener('mouseleave', startAutoPlay);
      }

      // Start auto-play
      startAutoPlay();
    });
  })();
</script>