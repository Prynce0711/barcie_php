<!-- Enhanced Admin Management Section with Search, Filter, Activity Monitoring, and Real-Time Online Status -->

<!-- Add Online Status Styles -->
<link rel="stylesheet" href="Components/Admin/AccountManagement/admin-online-status.css">

<div id="admin-management-locked" class="container-fluid">
  <div class="row justify-content-center" style="min-height: 60vh;">
    <div class="col-md-6 d-flex align-items-center">
      <div class="text-center w-100">
        <div class="mb-4">
          <i class="fas fa-lock fa-5x text-warning"></i>
        </div>
        <h2 class="mb-3">
          <i class="fas fa-user-shield me-2"></i>Account Management
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
  <?php
  ob_start(); ?>
  <button class="btn btn-sm btn-light me-1" onclick="loadAdmins()" title="Refresh">
    <i class="fas fa-sync-alt me-1"></i>Refresh
  </button>
  <?php $addLabel = 'Add New Admin'; $addClass = 'btn-sm btn-light'; $addSize = ''; $addTarget = '#addAdminModal'; include __DIR__ . '/../../ActionButton/Add.php'; ?>
  <?php $sectionActions = ob_get_clean(); ?>
  <?php ob_start(); ?>
  <div class="d-flex align-items-center gap-2 flex-wrap py-1">
    <?php $searchScope = 'admins'; $searchPlaceholder = 'Search by username, email, or name...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
    <div class="vr d-none d-md-block" style="height:28px;"></div>
    <select class="form-select form-select-sm" id="admin-role-filter" style="width:auto; min-width:130px;">
      <option value="">All Roles</option>
      <option value="super_admin">Super Admin</option>
      <option value="manager">Manager</option>
      <option value="admin">Admin</option>
      <option value="staff">Staff</option>
    </select>
    <select class="form-select form-select-sm" id="admin-status-filter" style="width:auto; min-width:120px;">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
      <option value="online">Online Now</option>
    </select>
    <div class="ms-auto">
      <?php $resetScope = 'admins'; include __DIR__ . '/../../Filter/ResetFilter.php'; ?>
    </div>
  </div>
  <?php $sectionFilters = ob_get_clean(); ?>
  <?php
  $sectionTitle    = 'Account Management';
  $sectionIcon     = 'fa-user-shield';
  $sectionSubtitle = 'Manage administrator accounts and permissions';
  $sectionBadge    = '<span class="badge bg-success bg-opacity-75 ms-1"><i class="fas fa-check-circle me-1"></i>Verified</span>';
  include __DIR__ . '/../Shared/SectionHeader.php';
  ?>

  <!-- Alerts -->
  <div id="admin-alert" class="alert alert-dismissible fade d-none" role="alert">
    <span id="admin-alert-message"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <!-- Bridge: sync reusable components → existing admin filter logic -->
  <script>
  (function(){
    function sync(){
      var el=document.getElementById('admin-search');
      if(!el){el=document.createElement('input');el.type='hidden';el.id='admin-search';document.body.appendChild(el);}
      if(window.Searchbar&&window.Searchbar.admins) el.value=window.Searchbar.admins.getValue();
      el.dispatchEvent(new Event('input',{bubbles:true}));
    }
    document.addEventListener('search-changed', function(e){
      if(e.detail.scope!=='admins') return; sync();
    });
    document.addEventListener('filters-reset', function(e){
      if(e.detail&&e.detail.scope&&e.detail.scope!=='admins') return;
      var r=document.getElementById('admin-role-filter');if(r)r.value='';
      var s=document.getElementById('admin-status-filter');if(s)s.value='';
      if(typeof clearFilters==='function') clearFilters();
    });
  })();
  </script>

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
      <?php
      $tableId = 'adminsTable';
      $tableScope = 'admins';
      $tablePageSize = 10;
      $tableColumns = [
          ['label' => 'ID'],
          ['label' => '<i class="fas fa-user me-1"></i>Username'],
          ['label' => '<i class="fas fa-envelope me-1"></i>Email'],
          ['label' => '<i class="fas fa-id-badge me-1"></i>Full Name'],
          ['label' => '<i class="fas fa-shield-alt me-1"></i>Role'],
          ['label' => '<i class="fas fa-key me-1"></i>Access Level'],
          ['label' => '<i class="fas fa-clock me-1"></i>Last Seen'],
          ['label' => '<i class="fas fa-cogs me-1"></i>Actions'],
      ];
      include __DIR__ . '/../../Table/Table.php';
      ?>
            <tr>
              <td colspan="8" class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
      <?php $tableClose = true; include __DIR__ . '/../../Table/Table.php'; ?>
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

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0.5;
    }
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

<script src="Components/Admin/Shared/admin-badge-utils.js"></script>
<script src="Components/Admin/AccountManagement/admin-management-enhanced.js"></script>