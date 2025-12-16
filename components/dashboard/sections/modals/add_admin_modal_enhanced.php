<!-- Enhanced Add Admin Modal with Validation -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Administrator</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="addAdminForm" novalidate>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add-username" class="form-label">Username <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                  <input type="text" class="form-control" id="add-username" name="username" required>
                  <div class="invalid-feedback">Please enter a username (min 3 characters)</div>
                  <div class="valid-feedback">Username is available</div>
                </div>
                <small class="text-muted">Minimum 3 characters, letters and numbers only</small>
              </div>
            </div>
            
            <div class="col-md-6">
              <label for="add-email" class="form-label">Email</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="add-email" name="email">
                <div class="invalid-feedback">Please enter a valid email address</div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add-password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  <input type="password" class="form-control" id="add-password" name="password" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('add-password')">
                    <i class="fas fa-eye"></i>
                  </button>
                  <div class="invalid-feedback">Password must be at least 8 characters</div>
                </div>
                <div class="password-strength mt-2">
                  <div class="progress" style="height: 5px;">
                    <div id="add-password-strength-bar" class="progress-bar" style="width: 0%"></div>
                  </div>
                  <small id="add-password-strength-text" class="text-muted"></small>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add-confirm-password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-lock"></i></span>
                  <input type="password" class="form-control" id="add-confirm-password" required>
                  <div class="invalid-feedback">Passwords do not match</div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="mb-3">
                <label for="add-full-name" class="form-label">Full Name</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                  <input type="text" class="form-control" id="add-full-name" name="full_name">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add-phone" class="form-label">Phone Number</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                  <input type="tel" class="form-control" id="add-phone" name="phone_number">
                  <div class="invalid-feedback">Please enter a valid phone number</div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="add-role" class="form-label">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="add-role" name="role" required>
                  <option value="staff">Staff (View Only)</option>
                  <option value="admin">Admin (Manage Bookings & Payments)</option>
                  <option value="manager">Manager (Full Access Except Super Admin Functions)</option>
                  <option value="super_admin">Super Admin (Full System Access)</option>
                </select>
                <small class="text-muted">Select appropriate access level</small>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label d-block">Account Status</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="add-is-active" name="is_active" checked>
                  <label class="form-check-label" for="add-is-active">
                    Active (User can log in)
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> The new admin will receive credentials via email if provided. Otherwise, share credentials securely.
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="addAdminSubmit">
            <i class="fas fa-save me-2"></i>Create Admin
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addAdminForm');
    const usernameInput = document.getElementById('add-username');
    const passwordInput = document.getElementById('add-password');
    const confirmPasswordInput = document.getElementById('add-confirm-password');
    const emailInput = document.getElementById('add-email');
    const phoneInput = document.getElementById('add-phone');

    // Username validation
    if (usernameInput) {
      usernameInput.addEventListener('input', debounce(function() {
        validateUsername(this);
      }, 500));
    }

    // Email validation
    if (emailInput) {
      emailInput.addEventListener('blur', function() {
        validateEmail(this);
      });
    }

    // Phone validation
    if (phoneInput) {
      phoneInput.addEventListener('input', function() {
        validatePhone(this);
      });
    }

    // Password strength checker
    if (passwordInput) {
      passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value, 'add');
      });
    }

    // Confirm password validation
    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener('input', function() {
        validatePasswordMatch('add');
      });
      passwordInput.addEventListener('input', function() {
        if (confirmPasswordInput.value) {
          validatePasswordMatch('add');
        }
      });
    }

    // Form submission
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
          e.stopPropagation();
          form.classList.add('was-validated');
          return;
        }

        // Additional validation
        if (!validatePasswordMatch('add')) {
          return;
        }

        submitAddAdminForm();
      });
    }
  });

  function validateUsername(input) {
    const username = input.value.trim();
    
    if (username.length < 3) {
      input.setCustomValidity('Username must be at least 3 characters');
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
      return false;
    }

    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
      input.setCustomValidity('Username can only contain letters, numbers, and underscores');
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
      return false;
    }

    // Check availability via AJAX
    fetch(`api/admin_management_enhanced.php?action=check_username&username=${encodeURIComponent(username)}`)
      .then(response => response.json())
      .then(data => {
        if (data.available) {
          input.setCustomValidity('');
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
        } else {
          input.setCustomValidity('Username already exists');
          input.classList.add('is-invalid');
          input.classList.remove('is-valid');
        }
      })
      .catch(() => {
        input.setCustomValidity('');
      });

    return true;
  }

  function validateEmail(input) {
    const email = input.value.trim();
    
    if (!email) return true; // Email is optional
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      input.setCustomValidity('Invalid email format');
      input.classList.add('is-invalid');
      return false;
    }
    
    input.setCustomValidity('');
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    return true;
  }

  function validatePhone(input) {
    const phone = input.value.trim();
    
    if (!phone) return true; // Phone is optional
    
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    if (!phoneRegex.test(phone) || phone.replace(/\D/g, '').length < 10) {
      input.setCustomValidity('Invalid phone number');
      input.classList.add('is-invalid');
      return false;
    }
    
    input.setCustomValidity('');
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    return true;
  }

  function checkPasswordStrength(password, prefix) {
    const strengthBar = document.getElementById(`${prefix}-password-strength-bar`);
    const strengthText = document.getElementById(`${prefix}-password-strength-text`);
    
    if (!strengthBar || !strengthText) return;
    
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    if (/\d/.test(password)) strength += 15;
    if (/[^a-zA-Z\d]/.test(password)) strength += 10;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 40) {
      strengthBar.className = 'progress-bar bg-danger';
      feedback = 'Weak password';
    } else if (strength < 70) {
      strengthBar.className = 'progress-bar bg-warning';
      feedback = 'Moderate password';
    } else {
      strengthBar.className = 'progress-bar bg-success';
      feedback = 'Strong password';
    }
    
    strengthText.textContent = feedback;
  }

  function validatePasswordMatch(prefix) {
    const password = document.getElementById(`${prefix}-password`).value;
    const confirmPassword = document.getElementById(`${prefix}-confirm-password`).value;
    const confirmInput = document.getElementById(`${prefix}-confirm-password`);
    
    if (password !== confirmPassword) {
      confirmInput.setCustomValidity('Passwords do not match');
      confirmInput.classList.add('is-invalid');
      return false;
    }
    
    confirmInput.setCustomValidity('');
    confirmInput.classList.remove('is-invalid');
    confirmInput.classList.add('is-valid');
    return true;
  }

  function submitAddAdminForm() {
    const form = document.getElementById('addAdminForm');
    const submitBtn = document.getElementById('addAdminSubmit');
    const originalHtml = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
    
    const formData = new FormData(form);
    formData.append('action', 'create');
    
    fetch('api/admin_management_enhanced.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('Admin created successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('addAdminModal')).hide();
        form.reset();
        form.classList.remove('was-validated');
        if (window.loadAdmins) window.loadAdmins();
      } else {
        showToast(data.message || 'Failed to create admin', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('Error creating admin', 'error');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHtml;
    });
  }

  window.togglePasswordVisibility = function(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  };

  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }
})();
</script>

<style>
.form-control.is-valid {
  border-color: #28a745;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(.375em + .1875rem) center;
  background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}

.form-control.is-invalid {
  border-color: #dc3545;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(.375em + .1875rem) center;
  background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}
</style>
