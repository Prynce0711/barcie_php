// Authentication Functionality
// User Login/Signup and Admin Login

document.addEventListener('DOMContentLoaded', function() {
  // Initialize authentication functionality
  initializeUserAuth();
  initializeAdminAuth();
});

function initializeUserAuth() {
  // Login/Signup Toggle
  const loginForm = document.getElementById('user-login-form');
  const signupForm = document.getElementById('user-signup-form');

  if (loginForm && signupForm) {
    document.querySelectorAll('.login-link').forEach(el => el.addEventListener('click', e => {
      e.preventDefault();
      signupForm.classList.add('d-none');
      loginForm.classList.remove('d-none');
    }));

    document.querySelectorAll('.signup-link').forEach(el => el.addEventListener('click', e => {
      e.preventDefault();
      loginForm.classList.add('d-none');
      signupForm.classList.remove('d-none');
    }));
  }

  // Real-time Signup Validation
  setupSignupValidation();

  // User AJAX Login
  setupUserLogin();

  // User Signup Validation Before Submit
  setupSignupSubmission();
}

function setupSignupValidation() {
  const emailInput = document.getElementById('user-signup-email');
  const passwordInput = document.getElementById('user-signup-password');
  const confirmInput = document.getElementById('user-signup-confirm');
  const emailMsg = document.getElementById('email-msg');
  const passwordMsg = document.getElementById('password-msg');
  const confirmMsg = document.getElementById('confirm-msg');

  if (emailInput && emailMsg) {
    emailInput.addEventListener('input', () => {
      if (!/@gmail\.com$/.test(emailInput.value)) {
        emailMsg.textContent = "Email must end with @gmail.com";
        emailMsg.classList.remove('d-none');
      } else {
        emailMsg.classList.add('d-none');
      }
    });
  }

  if (passwordInput && passwordMsg && confirmInput && confirmMsg) {
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(val)) {
        passwordMsg.textContent = "Password must be 8+ chars, letters & numbers";
        passwordMsg.classList.remove('d-none');
      } else {
        passwordMsg.classList.add('d-none');
      }

      if (confirmInput.value && confirmInput.value !== val) {
        confirmMsg.textContent = "Passwords do not match";
        confirmMsg.classList.remove('d-none');
      } else {
        confirmMsg.classList.add('d-none');
      }
    });

    confirmInput.addEventListener('input', () => {
      if (confirmInput.value !== passwordInput.value) {
        confirmMsg.textContent = "Passwords do not match";
        confirmMsg.classList.remove('d-none');
      } else {
        confirmMsg.classList.add('d-none');
      }
    });
  }
}

function setupUserLogin() {
  const loginForm = document.getElementById('user-login-form');
  const loginErrorEl = document.getElementById('login-error');
  
  if (loginForm && loginErrorEl) {
    loginForm.addEventListener('submit', e => {
      e.preventDefault();
      loginErrorEl.classList.add('d-none');
      loginErrorEl.textContent = "";

      const submitBtn = e.target.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading-spinner mx-auto"></div>';
      submitBtn.disabled = true;

      const formData = new FormData(loginForm);
      fetch('src/database/user_auth.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
            setTimeout(() => {
              window.location.href = data.is_admin ? 'dashboard.php' : 'Guest.php';
            }, 1000);
          } else {
            loginErrorEl.textContent = data.error || "Login failed.";
            loginErrorEl.classList.remove('d-none');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
          }
        })
        .catch(() => {
          loginErrorEl.textContent = "An unexpected error occurred.";
          loginErrorEl.classList.remove('d-none');
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
    });
  }
}

function setupSignupSubmission() {
  const signupForm = document.getElementById('user-signup-form');
  const emailInput = document.getElementById('user-signup-email');
  const passwordInput = document.getElementById('user-signup-password');
  const confirmInput = document.getElementById('user-signup-confirm');

  if (signupForm && emailInput && passwordInput && confirmInput) {
    signupForm.addEventListener('submit', e => {
      const emailVal = emailInput.value;
      const passVal = passwordInput.value;
      const confirmVal = confirmInput.value;

      if (!/@gmail\.com$/.test(emailVal) ||
          !/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(passVal) ||
          passVal !== confirmVal) {
        e.preventDefault();
        alert("Please fix signup errors before submitting.");
      }
    });
  }
}

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