<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/jpeg" href="assets/images/imageBg/barcie_logo.jpg">
  <link rel="shortcut icon" type="image/jpeg" href="assets/images/imageBg/barcie_logo.jpg">
  <link rel="apple-touch-icon" href="assets/images/imageBg/barcie_logo.jpg">
  <title>BarCIE Admin Login - Secure Access Portal</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    :root {
      --primary-color: #1e3c72;
      --secondary-color: #2a5298;
      --accent-color: #ffdd57;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
      border: 1px solid rgba(255, 255, 255, 0.18);
      max-width: 400px;
      width: 100%;
    }

    .logo-circle {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logo-circle img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }

    .login-title {
      color: white;
      text-align: center;
      margin-bottom: 10px;
      font-size: 1.5rem;
      font-weight: bold;
    }

    .login-subtitle {
      color: rgba(255, 255, 255, 0.7);
      text-align: center;
      margin-bottom: 30px;
      font-size: 0.9rem;
    }

    .form-label {
      color: white;
      font-weight: 500;
      margin-bottom: 8px;
    }

    .form-control {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      padding: 12px;
      border-radius: 10px;
      transition: all 0.3s ease;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.5);
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.25);
      border-color: var(--accent-color);
      color: white;
      box-shadow: 0 0 0 0.2rem rgba(255, 221, 87, 0.25);
    }

    .btn-login {
      background: var(--accent-color);
      color: var(--primary-color);
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-weight: bold;
      width: 100%;
      margin-top: 20px;
      transition: all 0.3s ease;
      font-size: 1rem;
    }

    .btn-login:hover {
      background: #ffd700;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 221, 87, 0.4);
    }

    .password-wrapper {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 12px;
      top: 38px;
      background: none;
      border: none;
      color: rgba(255, 255, 255, 0.6);
      cursor: pointer;
      font-size: 1.1rem;
      transition: color 0.3s ease;
      padding: 4px 8px;
      z-index: 10;
    }

    .password-toggle:hover {
      color: rgba(255, 255, 255, 0.9);
    }

    .form-control.with-icon {
      padding-right: 45px;
    }

    .alert {
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .back-link {
      text-align: center;
      margin-top: 20px;
    }

    .back-link a {
      color: var(--accent-color);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .back-link a:hover {
      color: #ffd700;
    }

    @media (max-width: 576px) {
      .login-container {
        padding: 30px 20px;
      }

      .login-title {
        font-size: 1.25rem;
      }
    }
  </style>
</head>

<body>

  <div class="login-container">
    <div class="logo-circle">
      <img src="assets/images/imageBg/barcie_logo.jpg" alt="BarCIE Logo">
    </div>
    <h1 class="login-title">BarCIE Admin Login</h1>
    <p class="login-subtitle">Access your unique admin portal</p>

    <div id="admin-login-error" class="alert alert-danger d-none"></div>

    <form id="admin-login-form">
      <div class="mb-3">
        <label for="admin-username" class="form-label">Username</label>
        <input type="text" id="admin-username" name="username" placeholder="admin" required class="form-control">
      </div>
      <div class="mb-3 password-wrapper">
        <label for="admin-password" class="form-label">Password</label>
        <input type="password" id="admin-password" name="password" placeholder="••••••••" required
          class="form-control with-icon">
        <button type="button" id="toggleAdminPassword" class="password-toggle">
          <i class="far fa-eye"></i>
        </button>
      </div>
      <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt me-2"></i>Sign In
      </button>
    </form>

    <div class="back-link">
      <a href="index.php">
        <i class="fas fa-arrow-left me-1"></i>Back to Home
      </a>
    </div>
  </div>

  <script>
    // Toggle password visibility
    document.getElementById('toggleAdminPassword').addEventListener('click', function () {
      const passwordField = document.getElementById('admin-password');
      const icon = this.querySelector('i');

      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });

    // Admin login form submission
    document.getElementById('admin-login-form').addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitButton = this.querySelector('button[type="submit"]');
      const errorDiv = document.getElementById('admin-login-error');

      // Disable submit button
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';

      fetch('database/admin_login.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Success - redirect to dashboard
            window.location.href = 'dashboard.php';
          } else {
            // Show error message
            errorDiv.textContent = data.message || 'Invalid username or password';
            errorDiv.classList.remove('d-none');

            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign In';

            // Hide error after 5 seconds
            setTimeout(() => {
              errorDiv.classList.add('d-none');
            }, 5000);
          }
        })
        .catch(error => {
          console.error('Login error:', error);
          errorDiv.textContent = 'An error occurred. Please try again.';
          errorDiv.classList.remove('d-none');

          // Re-enable submit button
          submitButton.disabled = false;
          submitButton.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign In';
        });
    });
  </script>

</body>

</html>