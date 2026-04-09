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
    <!-- <a href="#bookings-section" class="nav-link nav-link-custom" data-section="bookings-section">
      <i class="fas fa-calendar-alt"></i>
      <span>Bookings</span>
    </a> -->

    <li class="sidebar-item">
      <a href="#" class="nav-link nav-link-custom dropdown-toggle">
        <i class="fas fa-calendar-alt"></i>
        <span>Bookings</span>
      </a>

      <ul class="submenu">
        <li>
          <a href="#payment-verification-section" class="nav-link">
            Booking Verification
          </a>
        </li>
        <li>
          <a href="#bookings-section" class="nav-link" data-section="bookings-section">
            Booking Management
          </a>
        </li>
        <li>
          <a href="#pencil-bookings-section" class="nav-link" data-section="pencil-bookings-section">
            Pencil Management
          </a>
        </li>
        <li>
          <a href="#discount-management-section" class="nav-link" data-section="discount-management-section">
            Discount Management
          </a>
        </li>
        <li>
          <a href="#reports-section" class="nav-link">
            <span>Reports & Analytics</span>
          </a>
        </li>
      </ul>
    </li>



    <li class="sidebar-item">
      <a href="#" class="nav-link nav-link-custom dropdown-toggle">
        <i class="fas fa-calendar-alt"></i>
        <span>Landing</span>
      </a>

      <ul class="submenu">
        <li>
          <a href="#news-section" class="nav-link">
            News and Updates
          </a>
        </li>
        <li>
          <a href="#partners-management-section" class="nav-link" data-section="partners-management-section">
            Partners Management
          </a>
        </li>
        <li>
          <a href="#brochure-management-section" class="nav-link" data-section="brochure-management-section">
            Brochure Management
          </a>
        </li>



      </ul>
    </li>




    <style>
      .sidebar-menu {
        list-style: none;
        padding: 0;
      }

      .sidebar-item {
        position: relative;
      }

      .nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
      }

      .submenu {
        list-style: none;
        padding-left: 25px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
      }

      .submenu li a {
        display: block;
        padding: 8px 10px;
        font-size: 14px;
      }

      .sidebar-item.active .submenu {
        max-height: 500px;
      }
    </style>

    <script> document.querySelectorAll('.dropdown-toggle').forEach(menu => {

        menu.addEventListener('click', function (e) {
          e.preventDefault();

          const parent = this.parentElement;

          parent.classList.toggle('active');

        });

      });</script>



    <a href="#feedback-section" class="nav-link nav-link-custom" data-section="feedback-section">
      <i class="fas fa-comments"></i>
      <span>Feedback</span>
    </a>


    <a href="#admin-management-section" class="nav-link nav-link-custom manage-roles-link"
      data-section="admin-management-section">
      <i class="fas fa-user-shield"></i>
      <span>Account Management</span>
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

  (function () {
    function applyRoleBasedNavigation() {
      const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';

      console.log('Applying navigation permissions for role:', role);

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


    applyRoleBasedNavigation();

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', applyRoleBasedNavigation);
    }


    setTimeout(applyRoleBasedNavigation, 100);
  })();
</script>

<script>
  // Navigation is handled centrally by assets/js/page-state-manager.js
</script>