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
              <th>Created At</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="adminsTableBody">
            <tr>
              <td colspan="6" class="text-center">
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
    if (window.checkAdminVerification && window.checkAdminVerification()) {
      // Already verified
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
      tbody.innerHTML = '<tr><td colspan="6" class="text-center">No administrators found</td></tr>';
      return;
    }

    tbody.innerHTML = admins.map(admin => `
      <tr>
        <td>${admin.id}</td>
        <td><i class="fas fa-user me-2"></i>${escapeHtml(admin.username)}</td>
        <td>${admin.email ? escapeHtml(admin.email) : '<span class="text-muted">N/A</span>'}</td>
        <td>${admin.created_at ? formatDate(admin.created_at) : 'N/A'}</td>
        <td>${admin.last_login ? formatDate(admin.last_login) : '<span class="text-muted">Never</span>'}</td>
        <td>
          <button class="btn btn-sm btn-primary me-1" onclick="editAdmin(${admin.id})" title="Edit">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn btn-sm btn-danger" onclick="deleteAdmin(${admin.id}, '${escapeHtml(admin.username)}')" title="Delete">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
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
  window.deleteAdmin = function(adminId, username) {
    currentDeleteAdminId = adminId;
    document.getElementById('delete-admin-username').textContent = username;
    deleteAdminModal.show();
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
