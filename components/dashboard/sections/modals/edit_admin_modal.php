<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="editAdminModalLabel">
          <i class="fas fa-user-edit me-2"></i>Edit Administrator
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editAdminForm">
        <input type="hidden" id="edit-admin-id" name="admin_id">
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit-username" class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit-username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="edit-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="edit-email" name="email">
          </div>
          <div class="mb-3">
            <label for="edit-password" class="form-label">New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="edit-password" name="password">
              <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            <div class="form-text">Leave blank to keep current password</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Admin
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Edit Admin Modal Logic
(function() {
  let editAdminModal;

  document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('editAdminModal');
    if (modalElement) {
      editAdminModal = new bootstrap.Modal(modalElement);
    }

    // Password toggle
    const toggleBtn = document.getElementById('toggleEditPassword');
    const passwordInput = document.getElementById('edit-password');
    
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
    const form = document.getElementById('editAdminForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'update');

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
        submitBtn.disabled = true;

        fetch('api/admin_management.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (typeof window.showAdminAlert === 'function') {
              window.showAdminAlert('success', 'Admin updated successfully!');
            }
            editAdminModal.hide();
            if (typeof window.loadAdmins === 'function') {
              window.loadAdmins();
            }
          } else {
            if (typeof window.showAdminAlert === 'function') {
              window.showAdminAlert('danger', data.message || 'Failed to update admin');
            } else {
              alert(data.message || 'Failed to update admin');
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          if (typeof window.showAdminAlert === 'function') {
            window.showAdminAlert('danger', 'Error updating admin');
          } else {
            showToast('Error updating admin', 'error');
          }
        })
        .finally(() => {
          submitBtn.innerHTML = originalHtml;
          submitBtn.disabled = false;
        });
      });
    }
  });

  // Global function to open edit modal with admin data
  window.editAdmin = function(adminId) {
    fetch(`api/admin_management.php?action=get&admin_id=${adminId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('edit-admin-id').value = data.admin.id;
          document.getElementById('edit-username').value = data.admin.username;
          document.getElementById('edit-email').value = data.admin.email || '';
          document.getElementById('edit-password').value = '';
          editAdminModal.show();
        } else {
          if (typeof window.showAdminAlert === 'function') {
            window.showAdminAlert('danger', 'Failed to load admin details');
          } else {
            showToast('Failed to load admin details', 'error');
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (typeof window.showAdminAlert === 'function') {
          window.showAdminAlert('danger', 'Error loading admin details');
        } else {
          showToast('Error loading admin details', 'error');
        }
      });
  };
})();
</script>
