<!-- Admin Management Section with Authentication -->

<!-- Locked State (Before Authentication) -->
<div id="admin-management-locked" class="container-fluid">
  <div class="row justify-content-center" style="min-height: 60vh;">
    <div class="col-md-6 d-flex align-items-center">
      <div class="text-center w-100">
        <div class="mb-4">
          <i class="fas fa-lock fa-5x text-warning"></i>
        </div>
        <h2 class="mb-3">
          <i class="fas fa-user-shield me-2"></i>Admin Management
        </h2>
        <p class="text-muted mb-4">
          This section contains sensitive administrative functions. Please authenticate to continue.
        </p>
        <button class="btn btn-warning btn-lg" onclick="requestAdminAccess()">
          <i class="fas fa-key me-2"></i>Authenticate to Access
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Main Content (After Authentication) -->
<div id="admin-management-content" class="container-fluid d-none">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="dashboard-title">
      <i class="fas fa-user-shield me-2"></i>Admin Management
      <span class="badge bg-success ms-2">
        <i class="fas fa-check-circle me-1"></i>Verified
      </span>
    </h2>
    <div>
      <button class="btn btn-secondary me-2" onclick="loadAdmins()" title="Refresh">
        <i class="fas fa-sync-alt me-2"></i>Refresh
      </button>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
        <i class="fas fa-plus me-2"></i>Add New Admin
      </button>
    </div>
  </div>

  <!-- Alerts -->
  <div id="admin-alert" class="alert alert-dismissible fade d-none" role="alert">
    <span id="admin-alert-message"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>

  <!-- Admins Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="fas fa-users me-2"></i>Current Administrators</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover" id="adminsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Access Level</th>
              <th>Created At</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="adminsTableBody">
            <tr>
              <td colspan="8" class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Admin Management Main Logic
(function() {
  let currentDeleteAdminId = null;
  let deleteAdminModal;

  document.addEventListener('DOMContentLoaded', function() {
    // Initialize delete modal
    const deleteModalElement = document.getElementById('deleteAdminModal');
    if (deleteModalElement) {
      deleteAdminModal = new bootstrap.Modal(deleteModalElement);
    }

    // Check authentication when section becomes active
    const adminSection = document.getElementById('admin-management-section');
    if (adminSection) {
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.attributeName === 'class') {
            if (adminSection.classList.contains('active')) {
              console.log('Admin section became active');
              checkAndHandleAccess();
            }
          }
        });
      });
      observer.observe(adminSection, { attributes: true });
    }
  });

  // Check and handle access to admin management
  function checkAndHandleAccess() {
    // Only super_admin can access Admin Management
    const currentRole = (window.currentAdmin && window.currentAdmin.role) ? window.currentAdmin.role : 'staff';
    
    if (currentRole !== 'super_admin') {
      // Not super_admin - show access denied
      document.getElementById('admin-management-content').classList.add('d-none');
      document.getElementById('admin-management-locked').classList.remove('d-none');
      
      // Update locked message for non-super admins
      const lockedSection = document.getElementById('admin-management-locked');
      if (lockedSection) {
        lockedSection.innerHTML = `
          <div class="row justify-content-center" style="min-height: 60vh;">
            <div class="col-md-6 d-flex align-items-center">
              <div class="text-center w-100">
                <div class="mb-4">
                  <i class="fas fa-shield-alt fa-5x text-danger"></i>
                </div>
                <h2 class="mb-3">
                  <i class="fas fa-user-shield me-2"></i>Admin Management
                </h2>
                <p class="text-muted mb-4">
                  Access Denied: Only Super Administrators can manage admin accounts.
                </p>
                <p class="text-muted small">
                  Your role: <span class="badge bg-secondary">${currentRole}</span>
                </p>
              </div>
            </div>
          </div>
        `;
      }
      return;
    }
    
    if (window.checkAdminVerification && window.checkAdminVerification()) {
      // Super admin verified
      document.getElementById('admin-management-content').classList.remove('d-none');
      document.getElementById('admin-management-locked').classList.add('d-none');
      loadAdmins();
    } else {
      // Not verified - show locked state
      document.getElementById('admin-management-content').classList.add('d-none');
      document.getElementById('admin-management-locked').classList.remove('d-none');
    }
  }

  // Request admin access (show auth modal)
  window.requestAdminAccess = function() {
    if (window.showAdminAuthModal) {
      window.showAdminAuthModal();
    }
  };

  // Load all admins
  function loadAdmins() {
    console.log('Loading admins...');
    fetch('api/admin_management.php?action=list')
      .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          throw new Error('HTTP error ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Admin data received:', data);
        if (data.success) {
          displayAdmins(data.admins);
        } else {
          showAdminAlert('danger', data.message || 'Failed to load admins');
          const tbody = document.getElementById('adminsTableBody');
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${data.message || 'Failed to load admins'}</td></tr>`;
        }
      })
      .catch(error => {
        console.error('Error loading admins:', error);
        showAdminAlert('danger', 'Error loading admins: ' + error.message);
        const tbody = document.getElementById('adminsTableBody');
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error: ${error.message}</td></tr>`;
      });
  }

  // Display admins in table
  function displayAdmins(admins) {
    const tbody = document.getElementById('adminsTableBody');
    if (!admins || admins.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center">No administrators found</td></tr>';
      return;
    }

    tbody.innerHTML = admins.map(admin => {
      const adminId = admin.id || 0;
      const username = escapeHtml(admin.username || '');
      const email = admin.email ? escapeHtml(admin.email) : '<span class="text-muted">N/A</span>';
      const rawRole = (admin.role || 'admin').toString();
      const roleMap = {
        'super_admin': 'Super Admin',
        'admin': 'Admin',
        'manager': 'Manager',
        'staff': 'Staff'
      };
      const role = roleMap[rawRole] || rawRole;
      const accessLevel = admin.access_level || (rawRole === 'super_admin' || rawRole === 'manager' ? 'Full Access' : rawRole === 'admin' ? 'Manage Bookings & Payments' : 'View Only');
      const createdAt = admin.created_at ? formatDate(admin.created_at) : 'N/A';
      const lastLogin = admin.last_login ? formatDate(admin.last_login) : '<span class="text-muted">Never</span>';
      
      // Role badge color
      const roleBadgeClass = role === 'Super Admin' ? 'bg-danger' : 
                             role === 'Manager' ? 'bg-warning text-dark' : (role === 'Staff' ? 'bg-secondary' : 'bg-primary');
      
      // Access level badge - based on actual access level text
      const accessBadgeClass = accessLevel.includes('Full') ? 'bg-success' : 
                               accessLevel.includes('Manage') ? 'bg-info' : 'bg-secondary';      return `
        <tr data-admin-id="${adminId}">
          <td>${adminId}</td>
          <td><i class="fas fa-user me-2"></i>${username}</td>
          <td>${email}</td>
          <td><span class="badge ${roleBadgeClass}">${role}</span></td>
          <td><span class="badge ${accessBadgeClass}">${accessLevel}</span></td>
          <td>${createdAt}</td>
          <td>${lastLogin}</td>
          <td>
            <button class="btn btn-sm btn-primary me-1 edit-admin-btn" data-admin-id="${adminId}" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger delete-admin-btn" data-admin-id="${adminId}" data-admin-username="${username}" title="Delete">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
    }).join('');

    // Attach event listeners to edit buttons
    document.querySelectorAll('.edit-admin-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const adminId = parseInt(this.getAttribute('data-admin-id'));
        if (adminId && window.editAdmin) {
          window.editAdmin(adminId);
        }
      });
    });

    // Attach event listeners to delete buttons
    document.querySelectorAll('.delete-admin-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const adminId = parseInt(this.getAttribute('data-admin-id'));
        const username = this.getAttribute('data-admin-username');
        if (adminId && window.deleteAdmin) {
          window.deleteAdmin(adminId, username);
        }
      });
    });
  }

  // Confirm delete
  document.getElementById('confirmDeleteAdmin')?.addEventListener('click', function() {
    if (!currentDeleteAdminId) return;

    const submitBtn = this;
    const originalHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    submitBtn.disabled = true;

    fetch('api/admin_management.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `action=delete&admin_id=${currentDeleteAdminId}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAdminAlert('success', 'Admin deleted successfully!');
        deleteAdminModal.hide();
        currentDeleteAdminId = null;
        loadAdmins();
      } else {
        showAdminAlert('danger', data.message || 'Failed to delete admin');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAdminAlert('danger', 'Error deleting admin');
    })
    .finally(() => {
      submitBtn.innerHTML = originalHtml;
      submitBtn.disabled = false;
    });
  });

  // Make loadAdmins globally accessible
  window.loadAdmins = loadAdmins;

  // Global function to show delete confirmation
  window.deleteAdmin = async function(adminId, username) {
    console.log('Delete admin called:', adminId, username);
    
    // Use custom confirmation modal
    if (typeof showConfirm === 'function') {
      const confirmed = await showConfirm(
        `Are you sure you want to delete admin <strong>${username}</strong>? This action cannot be undone!`,
        { 
          title: 'Delete Administrator', 
          confirmText: 'Delete', 
          cancelText: 'Cancel',
          confirmClass: 'btn-danger' 
        }
      );
      
      if (!confirmed) return;
      
      // Proceed with deletion
      showToast('Deleting admin...', 'info', 2000);
      
      fetch('api/admin_management.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&admin_id=${adminId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Admin deleted successfully!', 'success');
          loadAdmins();
        } else {
          showToast(data.message || 'Failed to delete admin', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error deleting admin', 'error');
      });
    } else {
      // Fallback to modal if showConfirm not available
      currentDeleteAdminId = adminId;
      const usernameEl = document.getElementById('delete-admin-username');
      if (usernameEl) {
        usernameEl.textContent = username;
      }
      if (deleteAdminModal) {
        deleteAdminModal.show();
      }
    }
  };

  // Utility function for alerts (make it global)
  window.showAdminAlert = function(type, message) {
    const alert = document.getElementById('admin-alert');
    const alertMessage = document.getElementById('admin-alert-message');
    if (alert && alertMessage) {
      alert.className = `alert alert-${type} alert-dismissible fade show`;
      alertMessage.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
      alert.classList.remove('d-none');
      
      setTimeout(() => {
        alert.classList.add('d-none');
      }, 5000);
    }
  };

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
  }
})();
</script>
