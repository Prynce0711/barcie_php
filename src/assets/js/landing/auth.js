// Authentication Functionality
// User Login/Signup and Admin Login

document.addEventListener('DOMContentLoaded', function() {
  // Only initialize admin auth on the landing page. Guest/user login/signup
  // UI and handlers have been removed per project requirements.
  initializeAdminAuth();
});

function initializeAdminAuth() {
  // Admin password toggle
  const toggleAdminPasswordBtn = document.getElementById('toggleAdminPassword');
  const adminPasswordInput = document.getElementById('admin-password');

  if (toggleAdminPasswordBtn && adminPasswordInput) {
    toggleAdminPasswordBtn.addEventListener('click', () => {
      const icon = toggleAdminPasswordBtn;
      if (adminPasswordInput.type === 'password') {
        adminPasswordInput.type = 'text';
        icon.textContent = '🙈';
      } else {
        adminPasswordInput.type = 'password';
        toggleAdminPasswordBtn.textContent = '👁️';
      }
    });
  }

  // Admin AJAX Login
  setupAdminLogin();
}

function setupAdminLogin() {
  const adminForm = document.getElementById('admin-login-form');
  const adminError = document.getElementById('admin-login-error');

  if (adminForm && adminError) {
    adminForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      adminError.classList.add('d-none');
      adminError.textContent = '';

      const submitBtn = e.target.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading-spinner mx-auto"></div>';
      submitBtn.disabled = true;

      const formData = new FormData(adminForm);

      // USE DEBUG VERSION - Change to 'admin_login.php' for production
      const loginUrl = 'src/database/admin_login_debug.php';
      console.log('🔍 Sending login request to:', loginUrl);

      try {
        const res = await fetch(loginUrl, { method: 'POST', body: formData });
        const data = await res.json();
        
        console.log('📥 Response received:', data);

        if (data.success) {
          console.log('✅ Login successful!');
          submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
          setTimeout(() => {
            window.location.href = 'dashboard.php';
          }, 1000);
        } else {
          console.error('❌ Login failed:', data.message);
          if (data.debug) {
            console.log('🔍 Debug info:', data.debug);
          }
          adminError.textContent = data.message || 'Login failed.';
          adminError.classList.remove('d-none');
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }
      } catch (error) {
        console.error('❌ Fetch error:', error);
        adminError.textContent = 'Something went wrong. Try again.';
        adminError.classList.remove('d-none');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }
    });
  }
}