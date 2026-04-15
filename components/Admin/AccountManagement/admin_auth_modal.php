<!-- Admin Management Authentication Modal -->
<div class="modal fade" id="adminAuthModal" tabindex="-1" aria-labelledby="adminAuthModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="adminAuthModalLabel">
          <i class="fas fa-lock me-2"></i>Authentication Required
        </h5>
      </div>
      <form id="adminAuthForm">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Please verify your credentials to access Admin Management
          </div>
          
          <div id="admin-auth-error" class="alert alert-danger d-none"></div>

          <div class="mb-3">
            <label for="auth-username" class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="auth-username" name="username" required autocomplete="username">
          </div>
          
          <div class="mb-3">
            <label for="auth-password" class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" class="form-control" id="auth-password" name="password" required autocomplete="current-password">
              <button class="btn btn-outline-secondary" type="button" id="toggleAuthPassword">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#dashboard-section" class="btn btn-secondary" onclick="closeAdminAuthModal()">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-check me-2"></i>Verify & Access
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Admin Management Authentication
(function() {
  let adminAuthModal;
  
  document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('adminAuthModal');
    if (modalElement) {
      adminAuthModal = new bootstrap.Modal(modalElement);
    }

    // Password toggle
    const toggleBtn = document.getElementById('toggleAuthPassword');
    const passwordInput = document.getElementById('auth-password');
    
    if (toggleBtn && passwordInput) {
      toggleBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          passwordInput.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    }

    // Form submission
    const authForm = document.getElementById('adminAuthForm');
    if (authForm) {
      authForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const errorDiv = document.getElementById('admin-auth-error');
        errorDiv.classList.add('d-none');
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
        submitBtn.disabled = true;

        const formData = new FormData(this);

        try {
          const response = await fetch('database/index.php?endpoint=admin_login', {
            method: 'POST',
            body: formData
          });

          const data = await response.json();

          if (data.success) {
            // Authentication successful
            sessionStorage.setItem('admin_management_verified', 'true');
            sessionStorage.setItem('admin_verified_at', Date.now());
            
            adminAuthModal.hide();
            this.reset();
            
            // Show the actual admin management content
            document.getElementById('admin-management-content').classList.remove('d-none');
            document.getElementById('admin-management-locked').classList.add('d-none');
            
            // Load admins
            if (typeof window.loadAdmins === 'function') {
              window.loadAdmins();
            }
            
            // Show success message
            if (typeof window.showAdminAlert === 'function') {
              window.showAdminAlert('success', 'Authentication successful! You now have access to Admin Management.');
            }
          } else {
            errorDiv.textContent = data.message || 'Invalid credentials';
            errorDiv.classList.remove('d-none');
          }
        } catch (error) {
          console.error('Auth error:', error);
          errorDiv.textContent = 'Authentication failed. Please try again.';
          errorDiv.classList.remove('d-none');
        } finally {
          submitBtn.innerHTML = originalHtml;
          submitBtn.disabled = false;
        }
      });
    }
  });

  // Make modal accessible globally
  window.showAdminAuthModal = function() {
    if (adminAuthModal) {
      document.getElementById('auth-username').value = '';
      document.getElementById('auth-password').value = '';
      document.getElementById('admin-auth-error').classList.add('d-none');
      adminAuthModal.show();
    }
  };

  window.closeAdminAuthModal = function() {
    if (adminAuthModal) {
      adminAuthModal.hide();
    }
  };

  // Check if verification has expired (1 hour)
  window.checkAdminVerification = function() {
    const verified = sessionStorage.getItem('admin_management_verified');
    const verifiedAt = parseInt(sessionStorage.getItem('admin_verified_at'));
    const oneHour = 60 * 60 * 1000;
    
    if (verified === 'true' && verifiedAt && (Date.now() - verifiedAt) < oneHour) {
      return true;
    }
    
    // Clear expired verification
    sessionStorage.removeItem('admin_management_verified');
    sessionStorage.removeItem('admin_verified_at');
    return false;
  };
})();
</script>

