<!-- User Authentication Modal -->
<section id="user-auth" class="content-section hidden">
  <div class="fixed inset-0 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center" style="z-index: 1050; backdrop-filter: blur(5px);">
    <div class="glass-card p-4 shadow-lg" style="max-width: 450px; width: 90%; position: relative;">
      <button onclick="closeSection('user-auth')"
        class="btn-close btn-close-white position-absolute top-0 end-0 m-3" style="z-index: 10;"></button>

      <div class="text-center mb-4">
        <div class="bg-primary rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
          <i class="fas fa-user text-white" style="font-size: 24px;"></i>
        </div>
        <h2 class="h4 fw-bold text-white">Welcome to BarCIE</h2>
        <p class="text-white-50">Login or create a new account</p>
      </div>

      <!-- Tab Navigation -->
      <ul class="nav nav-pills nav-fill mb-4" id="authTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login-panel" 
                  type="button" role="tab" aria-controls="login-panel" aria-selected="true">
            Login
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="signup-tab" data-bs-toggle="pill" data-bs-target="#signup-panel" 
                  type="button" role="tab" aria-controls="signup-panel" aria-selected="false">
            Sign Up
          </button>
        </li>
      </ul>

      <div class="tab-content" id="authTabContent">
        <!-- Login Panel -->
        <div class="tab-pane fade show active" id="login-panel" role="tabpanel" aria-labelledby="login-tab">
          <div id="user-login-error" class="alert alert-danger d-none"></div>

          <form id="user-login-form">
            <input type="hidden" name="action" value="user_login">
            <div class="mb-3">
              <label for="login-email" class="form-label text-white">Email</label>
              <input type="email" id="login-email" name="email" placeholder="your@email.com" required
                class="form-control">
            </div>
            <div class="mb-3 position-relative">
              <label for="login-password" class="form-label text-white">Password</label>
              <input type="password" id="login-password" name="password" placeholder="••••••••" required
                class="form-control">
              <button type="button" id="toggleLoginPassword"
                class="position-absolute top-50 end-0 translate-middle-y me-3 btn btn-link text-white p-0" style="margin-top: 12px;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Login</button>
          </form>
        </div>

        <!-- Signup Panel -->
        <div class="tab-pane fade" id="signup-panel" role="tabpanel" aria-labelledby="signup-tab">
          <div id="user-signup-error" class="alert alert-danger d-none"></div>
          <div id="user-signup-success" class="alert alert-success d-none"></div>

          <form id="user-signup-form">
            <input type="hidden" name="action" value="user_signup">
            <div class="mb-3">
              <label for="signup-name" class="form-label text-white">Full Name</label>
              <input type="text" id="signup-name" name="name" placeholder="John Doe" required
                class="form-control">
            </div>
            <div class="mb-3">
              <label for="signup-email" class="form-label text-white">Email</label>
              <input type="email" id="signup-email" name="email" placeholder="your@email.com" required
                class="form-control">
            </div>
            <div class="mb-3 position-relative">
              <label for="signup-password" class="form-label text-white">Password</label>
              <input type="password" id="signup-password" name="password" placeholder="••••••••" required
                class="form-control" minlength="6">
              <button type="button" id="toggleSignupPassword"
                class="position-absolute top-50 end-0 translate-middle-y me-3 btn btn-link text-white p-0" style="margin-top: 12px;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <div class="mb-3 position-relative">
              <label for="signup-confirm-password" class="form-label text-white">Confirm Password</label>
              <input type="password" id="signup-confirm-password" name="confirm_password" placeholder="••••••••" required
                class="form-control" minlength="6">
              <button type="button" id="toggleSignupConfirmPassword"
                class="position-absolute top-50 end-0 translate-middle-y me-3 btn btn-link text-white p-0" style="margin-top: 12px;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Create Account</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
