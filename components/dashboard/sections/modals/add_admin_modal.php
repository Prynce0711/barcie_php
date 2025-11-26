<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addAdminModalLabel">
          <i class="fas fa-user-plus me-2"></i>Add New Administrator
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addAdminForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="add-username" class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add-username" name="username" required>
            <div class="form-text">Username must be unique</div>
          </div>
          <div class="mb-3">
            <label for="add-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="add-email" name="email">
            <div class="form-text">Optional - for contact purposes</div>
          </div>
          <div class="mb-3">
            <label for="add-password" class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" class="form-control" id="add-password" name="password" required>
              <button class="btn btn-outline-secondary" type="button" id="toggleAddPassword">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <div class="form-text">Minimum 6 characters recommended</div>
          </div>
          <div class="mb-3">
            <label for="add-confirm-password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="add-confirm-password" name="confirm_password" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Create Admin
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Add Admin Modal Logic
(function() {
  let addAdminModal;

  document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('addAdminModal');
    if (modalElement) {
      addAdminModal = new bootstrap.Modal(modalElement);
    }

    // Password toggle
    const toggleBtn = document.getElementById('toggleAddPassword');
    const passwordInput = document.getElementById('add-password');
    
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
    const form = document.getElementById('addAdminForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = document.getElementById('add-password').value;
        const confirmPassword = document.getElementById('add-confirm-password').value;
        
        if (password !== confirmPassword) {
          if (typeof window.showAdminAlert === 'function') {
            window.showAdminAlert('danger', 'Passwords do not match!');
          } else {
            showToast('Passwords do not match!', 'error');
          }
          return;
        }

        const formData = new FormData(this);
        formData.append('action', 'create');

        console.log('=== ADD ADMIN FORM SUBMISSION ===');
        console.log('Form data entries:');
        for (let [key, value] of formData.entries()) {
          if (key === 'password' || key === 'confirm_password') {
            console.log(key + ':', '[REDACTED - length=' + value.length + ']');
          } else {
            console.log(key + ':', value);
          }
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
        submitBtn.disabled = true;

        console.log('Sending POST request to: api/admin_management.php');

        fetch('api/admin_management.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          return response.text();
        })
        .then(text => {
          console.log('Raw response text:', text);
          let data;
          try {
            data = JSON.parse(text);
            console.log('Parsed JSON response:', data);
          } catch(e) {
            console.error('Failed to parse JSON:', e);
            console.error('Response was:', text);
            throw new Error('Invalid JSON response');
          }
          
          if (data.success) {
            console.log('✓ Admin created successfully! ID:', data.admin_id);
            if (typeof window.showAdminAlert === 'function') {
              window.showAdminAlert('success', 'Admin created successfully!');
            }
            addAdminModal.hide();
            form.reset();
            if (typeof window.loadAdmins === 'function') {
              window.loadAdmins();
            }
          } else {
            console.error('✗ Failed to create admin:', data.message);
            if (typeof window.showAdminAlert === 'function') {
              window.showAdminAlert('danger', data.message || 'Failed to create admin');
            } else {
              alert(data.message || 'Failed to create admin');
            }
          }
        })
        .catch(error => {
          console.error('✗ Fetch error:', error);
          if (typeof window.showAdminAlert === 'function') {
            window.showAdminAlert('danger', 'Error creating admin: ' + error.message);
          } else {
            showToast('Error creating admin', 'error');
          }
        })
        .finally(() => {
          submitBtn.innerHTML = originalHtml;
          submitBtn.disabled = false;
          console.log('=== FORM SUBMISSION COMPLETE ===');
        });
      });
    }
  });
})();
</script>
