<!-- Sidebar -->
<div class="sidebar">
  <h2>
    <i class="fas fa-hotel me-2"></i>
    <span>Hotel Admin</span>
  </h2>

  <div class="sidebar-nav">
    <a href="#dashboard-section" class="nav-link nav-link-custom active" data-section="dashboard-section">
      <i class="fas fa-tachometer-alt"></i>
      <span>Dashboard</span>
    </a>
    <a href="#calendar-section" class="nav-link nav-link-custom" data-section="calendar-section">
      <i class="fas fa-calendar-check"></i>
      <span>Calendar & Items</span>
    </a>
    <a href="#rooms-section" class="nav-link nav-link-custom" data-section="rooms-section">
      <i class="fas fa-door-open"></i>
      <span>Rooms & Facilities</span>
    </a>
    <a href="#bookings-section" class="nav-link nav-link-custom" data-section="bookings-section">
      <i class="fas fa-calendar-alt"></i>
      <span>Bookings</span>
    </a>

    <a href="#payment-verification-section" class="nav-link nav-link-custom" data-section="payment-verification-section">
      <i class="fas fa-credit-card"></i>
      <span>Payment Verification</span>
    </a>

    <a href="#feedback-section" class="nav-link nav-link-custom" data-section="feedback-section">
      <i class="fas fa-comments"></i>
      <span>Feedback</span>
    </a>

    <a href="#news-section" class="nav-link nav-link-custom" data-section="news-section">
      <i class="fas fa-newspaper"></i>  
      <span>News & Updates</span>
    </a>

    <a href="#reports-section" class="nav-link nav-link-custom" data-section="reports-section">
      <i class="fas fa-chart-bar"></i>
      <span>Reports & Analytics</span>
    </a>

    <a href="#admin-management-section" class="nav-link nav-link-custom manage-roles-link" data-section="admin-management-section">
      <i class="fas fa-user-shield"></i>
      <span>Manage Roles</span>
    </a>
    
  </div>

  <div class="mt-auto pt-4">
    <a href="logout.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center">
      <i class="fas fa-sign-out-alt me-2"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<script>
  // Role-based navigation visibility based on permission table
  (function() {
    function applyRoleBasedNavigation() {
      const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';
      
      console.log('Applying navigation permissions for role:', role);
      
      // Manage Roles: Staff=Hidden (✗), Admin/Manager/Super Admin=Visible (✓ with different permissions)
      // Super Admin: Full access (manage all)
      // Manager: Can manage except super_admin
      // Admin: Can manage staff only (add, no delete)
      const manageRolesLink = document.querySelector('.manage-roles-link');
      if (manageRolesLink) {
        if (role === 'staff') {
          manageRolesLink.style.display = 'none';
          console.log('Hiding Manage Roles - staff has no access');
        } else {
          manageRolesLink.style.display = '';
          console.log('Showing Manage Roles for role:', role);
        }
      }
    }
    
    // Run immediately
    applyRoleBasedNavigation();
    
    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', applyRoleBasedNavigation);
    }
    
    // Re-run after a short delay to ensure currentAdmin is set
    setTimeout(applyRoleBasedNavigation, 100);
  })();
</script>