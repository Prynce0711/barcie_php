<!-- Admin Login Modal -->
<section id="admin-login" class="content-section hidden">
  <div class="fixed inset-0 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center" style="z-index: 1050; backdrop-filter: blur(5px);">
    <div class="glass-card p-4 shadow-lg" style="max-width: 400px; width: 90%; position: relative;">
      <button onclick="closeSection('admin-login')"
        class="btn-close btn-close-white position-absolute top-0 end-0 m-3" style="z-index: 10;"></button>

      <div class="text-center mb-4">
        <div class="bg-warning rounded-circle mx-auto mb-3" style="width: 60px; height: 60px;"></div>
        <h2 class="h4 fw-bold text-white">BarCIE Admin Login</h2>
        <p class="text-white-50">Access your unique admin portal</p>
      </div>

      <div id="admin-login-error" class="alert alert-danger d-none"></div>

      <form id="admin-login-form">
        <div class="mb-3">
          <label for="admin-username" class="form-label text-white">Username</label>
          <input type="text" id="admin-username" name="username" placeholder="admin" required
            class="form-control">
        </div>
        <div class="mb-3 position-relative">
          <label for="admin-password" class="form-label text-white">Password</label>
          <input type="password" id="admin-password" name="password" placeholder="••••••••" required
            class="form-control">
          <button type="button" id="toggleAdminPassword"
            class="position-absolute top-50 end-0 translate-middle-y me-3 btn btn-link text-white p-0" style="margin-top: 12px;">👁️</button>
        </div>
        <button type="submit" class="btn btn-primary-custom w-100">Sign In</button>
      </form>
    </div>
  </div>
</section>