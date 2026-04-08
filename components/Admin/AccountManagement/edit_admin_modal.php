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
            <label for="edit-full-name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="edit-full-name" name="full_name">
          </div>
          <div class="mb-3">
            <label for="edit-phone" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="edit-phone" name="phone_number">
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
          <div class="mb-3" id="edit-role-group" style="display: none;">
            <label for="edit-role" class="form-label">Role</label>
            <select id="edit-role" name="role" class="form-select">
              <!-- Options will be populated based on current user role -->
            </select>
            <div class="form-text" id="edit-role-help">Change role (you cannot change your own role)</div>
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
  (function () {
    let editAdminModal;

    document.addEventListener('DOMContentLoaded', function () {
      const modalElement = document.getElementById('editAdminModal');
      if (modalElement) {
        editAdminModal = new bootstrap.Modal(modalElement);
      }

      // Password toggle
      const toggleBtn = document.getElementById('toggleEditPassword');
      const passwordInput = document.getElementById('edit-password');

      if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function () {
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
        form.addEventListener('submit', function (e) {
          e.preventDefault();

          const formData = new FormData(this);
          formData.append('action', 'update');

          // If role field is hidden or not allowed, remove it from submission
          const roleGroup = document.getElementById('edit-role-group');
          if (!roleGroup || roleGroup.style.display === 'none') {
            formData.delete('role');
          }

          const submitBtn = this.querySelector('button[type="submit"]');
          const originalHtml = submitBtn.innerHTML;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
          submitBtn.disabled = true;

          fetch('api/AdminManagementEnhanced.php', {
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
                  window.showToast(data.message || 'Failed to update admin', 'error');
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
    window.editAdmin = function (adminId) {
      fetch(`api/AdminManagementEnhanced.php?action=get&admin_id=${adminId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('edit-admin-id').value = data.admin.id;
            document.getElementById('edit-username').value = data.admin.username;
            document.getElementById('edit-email').value = data.admin.email || '';
            document.getElementById('edit-full-name').value = data.admin.full_name || '';
            document.getElementById('edit-phone').value = data.admin.phone_number || '';
            document.getElementById('edit-password').value = '';
            // Set role in modal if available
            try {
              const editRoleEl = document.getElementById('edit-role');
              const editRoleGroup = document.getElementById('edit-role-group');
              const editRoleHelp = document.getElementById('edit-role-help');
              const targetRole = data.admin.role || 'staff';

              // Show role selector only if current user has permission
              const currentRole = (window.currentAdmin && window.currentAdmin.role) ? window.currentAdmin.role : 'staff';
              const currentId = (window.currentAdmin && window.currentAdmin.id) ? window.currentAdmin.id : 0;
              console.log('Edit Admin Modal - Current role:', currentRole, 'Current ID:', currentId, 'Editing ID:', data.admin.id, 'Target role:', targetRole);

              // Cannot edit own account or if no permission
              if (currentId === data.admin.id) {
                if (editRoleGroup) editRoleGroup.style.display = 'none';
                console.log('✗ Cannot edit own role');
              }
              // Super Admin: can edit all roles
              else if (currentRole === 'super_admin') {
                if (editRoleGroup) editRoleGroup.style.display = '';
                if (editRoleEl) {
                  editRoleEl.innerHTML = `
                  <option value="staff">Staff</option>
                  <option value="admin">Admin</option>
                  <option value="manager">Manager</option>
                  <option value="super_admin">Super Admin</option>
                `;
                  editRoleEl.value = targetRole;
                }
                if (editRoleHelp) editRoleHelp.textContent = 'Super Admin can change any role';
                console.log('✓ Super Admin: Can edit all roles');
              }
              // Manager: can edit staff and admin (cannot edit super_admin)
              else if (currentRole === 'manager' && targetRole !== 'super_admin') {
                if (editRoleGroup) editRoleGroup.style.display = '';
                if (editRoleEl) {
                  editRoleEl.innerHTML = `
                  <option value="staff">Staff</option>
                  <option value="admin">Admin</option>
                `;
                  editRoleEl.value = targetRole;
                }
                if (editRoleHelp) editRoleHelp.textContent = 'Manager can edit Staff and Admin roles';
                console.log('✓ Manager: Can edit staff and admin');
              }
              // Admin: can only view staff (no role change - edit is for other fields only)
              else if (currentRole === 'admin' && targetRole === 'staff') {
                if (editRoleGroup) editRoleGroup.style.display = 'none';
                console.log('✓ Admin: Can edit staff details (no role change)');
              }
              // No permission
              else {
                if (editRoleGroup) editRoleGroup.style.display = 'none';
                console.log('✗ No permission to edit this user');
              }
            } catch (e) {
              console.error('Error setting role field:', e);
            }
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