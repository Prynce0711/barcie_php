<!-- Enhanced Admin Management Section with Search, Filter, Activity Monitoring, and Real-Time Online Status -->

<!-- Add Online Status Styles -->
<link rel="stylesheet" href="assets/css/admin-online-status.css">

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
  <!-- Header with Statistics -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
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
    </div>
  </div>

  <!-- Alerts -->
  <div id="admin-alert" class="alert alert-dismissible fade d-none" role="alert">
    <span id="admin-alert-message"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>

  <!-- Search and Filter Section -->
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="admin-search" placeholder="Search by username, email, or name...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" id="admin-role-filter">
            <option value="">All Roles</option>
            <option value="super_admin">Super Admin</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" id="admin-status-filter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="online">Online Now</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
            <i class="fas fa-times me-1"></i>Clear
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Admins Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-users me-2"></i>Current Administrators</h5>
      <div>
        <button class="btn btn-sm btn-light" onclick="exportToCSV()">
          <i class="fas fa-download me-1"></i>Export CSV
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover" id="adminsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th><i class="fas fa-user me-1"></i>Username</th>
              <th><i class="fas fa-envelope me-1"></i>Email</th>
              <th><i class="fas fa-id-badge me-1"></i>Full Name</th>
              <th><i class="fas fa-shield-alt me-1"></i>Role</th>
              <th><i class="fas fa-key me-1"></i>Access Level</th>
              <th><i class="fas fa-clock me-1"></i>Last Seen</th>
              <th><i class="fas fa-cogs me-1"></i>Actions</th>
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
      
      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
          <span id="showing-info">Showing 0 of 0 admins</span>
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0" id="pagination">
            <!-- Pagination will be inserted here -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<style>
/* Enhanced Admin Management Styles */
#admin-management-section .card {
  border: none;
  border-radius: 8px;
}

#admin-management-section .table {
  font-size: 0.9rem;
}

#admin-management-section .badge {
  font-size: 0.75rem;
  padding: 0.35em 0.65em;
}

#admin-management-section .status-online {
  color: #28a745;
  animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

#admin-management-section .table tbody tr:hover {
  background-color: rgba(0, 123, 255, 0.05);
}

#admin-management-section .admin-row-selected {
  background-color: rgba(255, 193, 7, 0.1) !important;
}

#admin-management-section .admin-row-online {
  border-left: 3px solid #28a745;
}

/* Online status enhancements */
#admin-management-section .status-update-animation {
  transition: all 0.3s ease-in-out;
}

#admin-management-section .heartbeat-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: #28a745;
  border-radius: 50%;
  margin-right: 6px;
  animation: pulse-icon 2s ease-in-out infinite;
}
</style>

<script src="assets/js/admin-management-enhanced.js"></script>
