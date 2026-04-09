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
  (function () {
    function normalizeSectionId(rawValue) {
      const value = (rawValue || '').replace(/^#/, '');
      if (!value) {
        return '';
      }

      if (value === 'Pencil-Bookings') {
        return 'pencil-bookings-section';
      }

      return value;
    }

    function getSectionIdFromLink(link) {
      if (!link) {
        return '';
      }

      const dataSection = link.getAttribute('data-section');
      if (dataSection) {
        return normalizeSectionId(dataSection);
      }

      const href = (link.getAttribute('href') || '').trim();
      if (href.startsWith('#') && href.length > 1) {
        return normalizeSectionId(href);
      }

      return '';
    }

    function setActiveSidebarLink(activeSectionId, preferredLink) {
      document.querySelectorAll('.sidebar .nav-link, .sidebar .nav-link-custom').forEach(function (link) {
        link.classList.remove('active');
      });

      if (preferredLink) {
        preferredLink.classList.add('active');
        return;
      }

      const matchedLink = document.querySelector(
        '.sidebar a[data-section="' + activeSectionId + '"]' +
        ', .sidebar a[href="#' + activeSectionId + '"]'
      );

      if (matchedLink) {
        matchedLink.classList.add('active');
      }
    }

    function fallbackShowSection(sectionId) {
      const resolvedSectionId = normalizeSectionId(sectionId);
      if (!resolvedSectionId) {
        return false;
      }

      const sections = document.querySelectorAll('.content-section');
      if (!sections.length) {
        return false;
      }

      const target = document.getElementById(resolvedSectionId);
      if (!target) {
        return false;
      }

      sections.forEach(function (section) {
        section.classList.remove('active', 'd-block');
        section.classList.add('d-none');
        section.style.display = 'none';
      });

      target.classList.remove('d-none');
      target.classList.add('active', 'd-block');
      target.style.display = 'block';

      localStorage.setItem('activeSection', resolvedSectionId);

      if (window.innerWidth <= 992) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
          sidebar.classList.remove('open');
        }

        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
          overlay.classList.remove('active', 'show');
        }
      }

      return true;
    }

    function openSection(sectionId, preferredLink) {
      const resolvedSectionId = normalizeSectionId(sectionId);
      if (!resolvedSectionId) {
        return false;
      }

      let opened = false;

      if (typeof window.showSection === 'function') {
        try {
          const result = window.showSection(resolvedSectionId);
          const targetSection = document.getElementById(resolvedSectionId);
          const targetIsVisible = !!targetSection && (
            targetSection.classList.contains('active') ||
            targetSection.classList.contains('d-block') ||
            targetSection.style.display === 'block'
          );

          opened = result === true || targetIsVisible;
        } catch (error) {
          console.warn('Primary showSection failed, using sidebar fallback.', error);
        }
      }

      if (!opened) {
        opened = fallbackShowSection(resolvedSectionId);
      }

      if (!opened) {
        return false;
      }

      setActiveSidebarLink(resolvedSectionId, preferredLink || null);

      if (window.location.hash.substring(1) !== resolvedSectionId) {
        try {
          history.replaceState(null, '', '#' + resolvedSectionId);
        } catch (error) {
          window.location.hash = resolvedSectionId;
        }
      }

      return true;
    }

    const sidebarRoot = document.querySelector('.sidebar');
    if (!sidebarRoot) {
      return;
    }

    sidebarRoot.addEventListener('click', function (event) {
      const link = event.target.closest('a.nav-link, a.nav-link-custom');
      if (!link || !sidebarRoot.contains(link)) {
        return;
      }

      const sectionId = getSectionIdFromLink(link);
      if (!sectionId) {
        return;
      }

      event.preventDefault();
      openSection(sectionId, link);
    });

    function initializeSectionFromState() {
      const fromHash = normalizeSectionId(window.location.hash.substring(1));
      const fromStorage = normalizeSectionId(localStorage.getItem('activeSection'));
      const initialSectionId = fromHash || fromStorage || 'dashboard-section';

      openSection(initialSectionId, null);
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initializeSectionFromState);
    } else {
      initializeSectionFromState();
    }

    window.addEventListener('hashchange', function () {
      const hashSectionId = normalizeSectionId(window.location.hash.substring(1));
      if (!hashSectionId) {
        return;
      }

      openSection(hashSectionId, null);
    });
  })();
</script>