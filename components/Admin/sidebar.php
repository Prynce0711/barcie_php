<!-- Sidebar -->
<div class="sidebar">
  <h2>
    <i class="fas fa-hotel me-2"></i>
    <span>Hotel Admin</span>
  </h2>

  <div class="sidebar-nav">
    <a href="#dashboard-section" class="nav-link nav-link-custom active" data-section="dashboard-section"
      onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'dashboard-section') : true;">
      <i class="fas fa-tachometer-alt"></i>
      <span>Dashboard</span>
    </a>
    <a href="#calendar-section" class="nav-link nav-link-custom" data-section="calendar-section"
      onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'calendar-section') : true;">
      <i class="fas fa-calendar-check"></i>
      <span>Calendar & Items</span>
    </a>
    <a href="#rooms-section" class="nav-link nav-link-custom" data-section="rooms-section"
      onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'rooms-section') : true;">
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
          <a href="#payment-verification-section" class="nav-link" data-section="payment-verification-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'payment-verification-section') : true;">
            Booking Verification
          </a>
        </li>
        <li>
          <a href="#bookings-section" class="nav-link" data-section="bookings-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'bookings-section') : true;">
            Booking Management
          </a>
        </li>
        <li>
          <a href="#pencil-bookings-section" class="nav-link" data-section="pencil-bookings-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'pencil-bookings-section') : true;">
            Pencil Management
          </a>
        </li>
        <li>
          <a href="#discount-management-section" class="nav-link" data-section="discount-management-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'discount-management-section') : true;">
            Discount Management
          </a>
        </li>
        <li>
          <a href="#reports-section" class="nav-link" data-section="reports-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'reports-section') : true;">
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
          <a href="#news-section" class="nav-link" data-section="news-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'news-section') : true;">
            News and Updates
          </a>
        </li>
        <li>
          <a href="#partners-management-section" class="nav-link" data-section="partners-management-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'partners-management-section') : true;">
            Partners Management
          </a>
        </li>
        <li>
          <a href="#brochure-management-section" class="nav-link" data-section="brochure-management-section"
            onclick="return window.BarcieSidebarHandleClick ? window.BarcieSidebarHandleClick(event, 'brochure-management-section') : true;">
            Brochure Management
          </a>
        </li>



      </ul>
    </li>




    <style>
      /* .sidebar {
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
      }

      .sidebar-nav {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
      } */

      .sidebar {
        position: fixed;
        /* stick to left */
        height: 100vh;
        /* adjust as needed */

        overflow-y: auto;
        /* vertical scroll only */
        overflow-x: hidden;
        /* remove horizontal scroll */
      }

      .sidebar-nav {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        /* prevent bottom scroll */
      }

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

    <script>
      (function () {
        if (window.__BARCIE_SIDEBAR_BOUND__) {
          return;
        }
        window.__BARCIE_SIDEBAR_BOUND__ = true;

        function sidebarDebug(message, data) {
          try {
            console.log('[SidebarDebug]', message, data || '');
            const panel = document.getElementById('sidebar-debug-console-log');
            if (!panel) {
              return;
            }

            const line = document.createElement('div');
            line.style.padding = '2px 0';
            const suffix = data ? ' ' + JSON.stringify(data) : '';
            line.textContent = new Date().toLocaleTimeString() + ' - ' + message + suffix;
            panel.appendChild(line);
            panel.scrollTop = panel.scrollHeight;
          } catch (err) {
            console.warn('Sidebar debug log failed', err);
          }
        }

        function ensureSidebarDebugConsole() {
          if (document.getElementById('sidebar-debug-console')) {
            return;
          }

          const box = document.createElement('div');
          box.id = 'sidebar-debug-console';
          box.style.position = 'fixed';
          box.style.right = '12px';
          box.style.bottom = '12px';
          box.style.width = '360px';
          box.style.maxHeight = '200px';
          box.style.background = 'rgba(15,23,42,0.95)';
          box.style.color = '#e2e8f0';
          box.style.border = '1px solid #334155';
          box.style.borderRadius = '8px';
          box.style.padding = '8px';
          box.style.zIndex = '99999';
          box.style.fontSize = '11px';
          box.style.fontFamily = 'Consolas, monospace';



          const clearBtn = document.getElementById('sidebar-debug-clear');
          if (clearBtn) {
            clearBtn.addEventListener('click', function () {
              const panel = document.getElementById('sidebar-debug-console-log');
              if (panel) {
                panel.innerHTML = '';
              }
            });
          }
        }

        function showSectionById(sectionId) {
          if (!sectionId) {
            sidebarDebug('Rejected empty section id');
            return false;
          }

          const target = document.getElementById(sectionId);
          if (!target) {
            sidebarDebug('Target section not found', { sectionId: sectionId });
            return false;
          }

          sidebarDebug('Switching section', {
            sectionId: sectionId,
            totalSections: document.querySelectorAll('.content-section').length
          });

          document.querySelectorAll('.content-section').forEach(function (section) {
            section.classList.remove('active', 'd-block');
            section.classList.add('d-none');
            section.style.setProperty('display', 'none', 'important');
          });

          target.classList.remove('d-none');
          target.classList.add('active', 'd-block');
          target.style.setProperty('display', 'block', 'important');

          document.querySelectorAll('.sidebar .nav-link, .sidebar .nav-link-custom').forEach(function (link) {
            link.classList.remove('active');
          });

          const activeLink = document.querySelector(
            '.sidebar .nav-link[data-section="' + sectionId + '"]' +
            ', .sidebar .nav-link-custom[data-section="' + sectionId + '"]' +
            ', .sidebar .nav-link[href="#' + sectionId + '"]' +
            ', .sidebar .nav-link-custom[href="#' + sectionId + '"]'
          );

          if (activeLink) {
            activeLink.classList.add('active');
          }

          const preservedUrl = window.location.pathname + (window.location.search || '');
          history.replaceState(null, '', preservedUrl);

          sidebarDebug('Section switched successfully', { sectionId: sectionId });

          return true;
        }

        window.BarcieSidebarHandleClick = function (event, sectionId) {
          sidebarDebug('Inline click handler fired', { sectionId: sectionId });
          if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
          }
          return showSectionById(sectionId);
        };

        window.BarcieSidebarDebugNavigate = function (sectionId) {
          return showSectionById(sectionId);
        };

        ensureSidebarDebugConsole();
        sidebarDebug('Sidebar debug initialized');

        document.querySelectorAll('.dropdown-toggle').forEach(function (menu) {
          menu.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.parentElement;
            if (parent) {
              parent.classList.toggle('active');
            }
          });
        });

        document.addEventListener('click', function (e) {
          const link = e.target.closest('.sidebar a[href^="#"]');
          if (!link) {
            return;
          }

          if (link.classList.contains('dropdown-toggle')) {
            return;
          }

          const dataSection = (link.getAttribute('data-section') || '').trim();
          const href = (link.getAttribute('href') || '').trim();
          const sectionId = (dataSection || href.replace(/^#/, '')).trim();

          if (!sectionId) {
            return;
          }

          if (showSectionById(sectionId)) {
            e.preventDefault();
          }
        }, true);
      })();
    </script>



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
    <a href="index.php?view=logout" class="btn btn-danger w-100 d-flex align-items-center justify-content-center">
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