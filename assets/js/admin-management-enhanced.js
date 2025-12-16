/**
 * Enhanced Admin Management JavaScript
 * Includes search, filter, activity monitoring, and bulk operations
 * @version 2.0.0
 * @created 2025-12-12
 */

(function() {
  let adminsData = [];
  let filteredAdmins = [];
  let currentPage = 1;
  let itemsPerPage = 10;
  let heartbeatInterval = null;
  let refreshInterval = null;

  document.addEventListener('DOMContentLoaded', function() {
    initializeAdminManagement();
  });

  function initializeAdminManagement() {
    // Setup event listeners
    setupSearchAndFilter();
    
    // Check authentication when section becomes active
    const adminSection = document.getElementById('admin-management-section');
    if (adminSection) {
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.attributeName === 'class') {
            if (adminSection.classList.contains('active')) {
              checkAndHandleAccess();
            }
          }
        });
      });
      observer.observe(adminSection, { attributes: true });
    }
  }

  function setupSearchAndFilter() {
    // Search input
    const searchInput = document.getElementById('admin-search');
    if (searchInput) {
      searchInput.addEventListener('input', debounce(function() {
        applyFilters();
      }, 300));
    }

    // Role filter
    const roleFilter = document.getElementById('admin-role-filter');
    if (roleFilter) {
      roleFilter.addEventListener('change', applyFilters);
    }

    // Status filter
    const statusFilter = document.getElementById('admin-status-filter');
    if (statusFilter) {
      statusFilter.addEventListener('change', applyFilters);
    }
  }

  function checkAndHandleAccess() {
    const currentRole = (window.currentAdmin && window.currentAdmin.role) || 'staff';
    
    if (currentRole === 'staff') {
      document.getElementById('admin-management-content').classList.add('d-none');
      document.getElementById('admin-management-locked').classList.remove('d-none');
      showAccessDenied();
      return;
    }
    
    if (['admin', 'manager', 'super_admin'].includes(currentRole)) {
      document.getElementById('admin-management-content').classList.remove('d-none');
      document.getElementById('admin-management-locked').classList.add('d-none');
      loadAdmins();
      loadStatistics();
      
      // Start heartbeat to keep this admin marked as online
      startHeartbeat();
      
      // Auto-refresh statistics every 30 seconds
      refreshInterval = setInterval(function() {
        loadStatistics();
      }, 30000);
      
      // Auto-refresh admin list every 60 seconds to update online status
      setInterval(function() {
        loadAdmins(true); // Silent refresh
      }, 60000);
    }
  }
  
  function startHeartbeat() {
    // Clear any existing heartbeat
    if (heartbeatInterval) {
      clearInterval(heartbeatInterval);
    }
    
    // Send heartbeat every 30 seconds
    heartbeatInterval = setInterval(function() {
      fetch('api/admin_heartbeat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Heartbeat sent:', data.timestamp);
        }
      })
      .catch(error => {
        console.error('Heartbeat failed:', error);
      });
    }, 30000);
    
    // Send initial heartbeat immediately
    fetch('api/admin_heartbeat.php', { method: 'POST' });
  }
  
  function stopHeartbeat() {
    if (heartbeatInterval) {
      clearInterval(heartbeatInterval);
      heartbeatInterval = null;
    }
    if (refreshInterval) {
      clearInterval(refreshInterval);
      refreshInterval = null;
    }
  }

  function showAccessDenied() {
    const lockedSection = document.getElementById('admin-management-locked');
    if (lockedSection) {
      lockedSection.innerHTML = `
        <div class="row justify-content-center" style="min-height: 60vh;">
          <div class="col-md-6 d-flex align-items-center">
            <div class="text-center w-100">
              <div class="mb-4">
                <i class="fas fa-shield-alt fa-5x text-danger"></i>
              </div>
              <h2 class="mb-3"><i class="fas fa-user-shield me-2"></i>Role Management</h2>
              <p class="text-muted mb-4">Access Denied: Staff members cannot access role management.</p>
              <p class="text-muted small">Your role: <span class="badge bg-secondary">staff</span></p>
            </div>
          </div>
        </div>
      `;
    }
  }

  function loadAdmins(silent = false) {
    // Removed loading notification - loads silently by default
    
    fetch('api/admin_management_enhanced.php?action=list')
      .then(response => {
        if (!response.ok) throw new Error('HTTP error ' + response.status);
        return response.json();
      })
      .then(data => {
        console.log('Admin data received:', data);
        if (data.success) {
          adminsData = data.admins || [];
          filteredAdmins = [...adminsData];
          applyFilters();
        } else {
          showAdminAlert('danger', data.message || 'Failed to load admins');
        }
      })
      .catch(error => {
        console.error('Error loading admins:', error);
        showAdminAlert('danger', 'Error loading admins: ' + error.message);
      });
  }

  function loadStatistics() {
    // Statistics display removed - function kept for backward compatibility
    return;
  }

  function applyFilters() {
    const searchTerm = document.getElementById('admin-search')?.value.toLowerCase() || '';
    const roleFilter = document.getElementById('admin-role-filter')?.value || '';
    const statusFilter = document.getElementById('admin-status-filter')?.value || '';

    filteredAdmins = adminsData.filter(admin => {
      // Search filter
      if (searchTerm) {
        const searchableFields = [
          admin.username || '',
          admin.email || '',
          admin.full_name || ''
        ].map(f => f.toLowerCase());
        
        if (!searchableFields.some(field => field.includes(searchTerm))) {
          return false;
        }
      }

      // Role filter
      if (roleFilter && admin.role !== roleFilter) {
        return false;
      }

      // Status filter
      if (statusFilter) {
        if (statusFilter === 'active' && !admin.is_active) return false;
        if (statusFilter === 'inactive' && admin.is_active) return false;
        if (statusFilter === 'online' && !admin.is_currently_active) return false;
      }

      return true;
    });

    currentPage = 1;
    displayAdmins();
  }

  function displayAdmins() {
    const tbody = document.getElementById('adminsTableBody');
    if (!filteredAdmins || filteredAdmins.length === 0) {
      tbody.innerHTML = '<tr><td colspan="10" class="text-center">No administrators found</td></tr>';
      updateShowingInfo(0, 0);
      return;
    }

    const currentRole = (window.currentAdmin && window.currentAdmin.role) || 'staff';
    const currentId = (window.currentAdmin && window.currentAdmin.id) || 0;

    // Pagination
    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;
    const pageAdmins = filteredAdmins.slice(startIdx, endIdx);

    tbody.innerHTML = pageAdmins.map(admin => {
      const adminId = admin.id || 0;
      const username = escapeHtml(admin.username || '');
      const email = admin.email ? escapeHtml(admin.email) : '<span class="text-muted">N/A</span>';
      const fullName = admin.full_name ? escapeHtml(admin.full_name) : '<span class="text-muted">N/A</span>';
      const rawRole = (admin.role || 'staff').toString();
      
      const roleMap = {
        'super_admin': 'Super Admin',
        'admin': 'Admin',
        'manager': 'Manager',
        'staff': 'Staff'
      };
      const role = roleMap[rawRole] || rawRole;
      
      const accessLevel = admin.access_level || 'Unknown';
      const isActive = admin.is_active;
      const isOnline = admin.is_currently_active;
      const lastSeen = admin.last_seen || 'Unknown';
      
      // Role badge color
      const roleBadgeClass = role === 'Super Admin' ? 'bg-danger' : 
                             role === 'Manager' ? 'bg-warning text-dark' : 
                             (role === 'Staff' ? 'bg-secondary' : 'bg-primary');
      
      // Last activity - show "Online now" with green pulsing indicator if currently active
      let lastActivity;
      if (isOnline) {
        lastActivity = '<span class="text-success fw-bold"><i class="fas fa-circle me-1 pulse-icon" style="font-size: 0.6em;"></i>Online now</span>';
      } else if (admin.last_activity) {
        lastActivity = '<span class="text-muted"><i class="fas fa-circle me-1" style="font-size: 0.6em; opacity: 0.3;"></i>' + (lastSeen || timeAgo(admin.last_activity)) + '</span>';
      } else if (admin.last_login) {
        lastActivity = '<span class="text-muted"><i class="fas fa-circle me-1" style="font-size: 0.6em; opacity: 0.3;"></i>Last login: ' + timeAgo(admin.last_login) + '</span>';
      } else {
        lastActivity = '<span class="text-muted"><i class="fas fa-circle me-1" style="font-size: 0.6em; opacity: 0.3;"></i>Never active</span>';
      }
      
      // Determine permissions
      let canEdit = false;
      let canDelete = false;
      
      if (currentRole === 'super_admin' && currentId !== adminId) {
        canEdit = canDelete = true;
      } else if (currentRole === 'manager' && currentId !== adminId && rawRole !== 'super_admin') {
        canEdit = canDelete = true;
      } else if (currentRole === 'admin' && rawRole === 'staff') {
        canEdit = true;
      }
      
      const editBtn = canEdit ? 
        `<button class="btn btn-sm btn-primary me-1 edit-admin-btn" data-admin-id="${adminId}" title="Edit">
          <i class="fas fa-edit"></i>
        </button>` : '';
      
      const deleteBtn = canDelete ? 
        `<button class="btn btn-sm btn-danger me-1 delete-admin-btn" data-admin-id="${adminId}" data-admin-username="${username}" title="Delete">
          <i class="fas fa-trash"></i>
        </button>` : '';
      
      const viewBtn = `<button class="btn btn-sm btn-info view-admin-btn" data-admin-id="${adminId}" title="View Details">
        <i class="fas fa-eye"></i>
      </button>`;
      
      const noAccessBadge = (!canEdit && !canDelete) ? 
        '<span class="badge bg-secondary"><i class="fas fa-lock me-1"></i>No Access</span>' : '';
      
      return `
        <tr data-admin-id="${adminId}">
          <td>${adminId}</td>
          <td><i class="fas fa-user me-2"></i>${username}</td>
          <td>${email}</td>
          <td>${fullName}</td>
          <td><span class="badge ${roleBadgeClass}">${role}</span></td>
          <td><span class="badge bg-info">${accessLevel}</span></td>
          <td>${lastActivity}</td>
          <td class="text-nowrap">
            ${viewBtn}
            ${editBtn}
            ${deleteBtn}
            ${noAccessBadge}
          </td>
        </tr>
      `;
    }).join('');

    // Attach event listeners
    attachTableEventListeners();
    
    // Update pagination
    renderPagination();
    updateShowingInfo(startIdx + 1, Math.min(endIdx, filteredAdmins.length));
  }

  function attachTableEventListeners() {
    // Checkbox listeners
    document.querySelectorAll('.admin-checkbox').forEach(cb => {
      cb.addEventListener('change', function() {
        const adminId = parseInt(this.dataset.adminId);
        if (this.checked) {
          selectedAdmins.add(adminId);
          this.closest('tr').classList.add('admin-row-selected');
        } else {
          selectedAdmins.delete(adminId);
          this.closest('tr').classList.remove('admin-row-selected');
        }
        updateBulkActionsUI();
      });
    });

    // Edit buttons
    document.querySelectorAll('.edit-admin-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const adminId = parseInt(this.dataset.adminId);
        if (window.editAdmin) window.editAdmin(adminId);
      });
    });

    // Delete buttons
    document.querySelectorAll('.delete-admin-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const adminId = parseInt(this.dataset.adminId);
        const username = this.dataset.adminUsername;
        if (window.deleteAdmin) window.deleteAdmin(adminId, username);
      });
    });

    // View buttons
    document.querySelectorAll('.view-admin-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const adminId = parseInt(this.dataset.adminId);
        if (window.viewAdminDetails) window.viewAdminDetails(adminId);
      });
    });
  }

  function renderPagination() {
    const totalPages = Math.ceil(filteredAdmins.length / itemsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (!pagination || totalPages <= 1) {
      if (pagination) pagination.innerHTML = '';
      return;
    }

    let html = '';
    
    // Previous button
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a>
    </li>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
          <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
        </li>`;
      } else if (i === currentPage - 3 || i === currentPage + 3) {
        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
    }
    
    // Next button
    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a>
    </li>`;
    
    pagination.innerHTML = html;
  }

  function updateShowingInfo(start, end) {
    const info = document.getElementById('showing-info');
    if (info) {
      info.textContent = `Showing ${start} to ${end} of ${filteredAdmins.length} admins`;
    }
  }

  function escapeHtml(text) {
    fetch(`api/admin_management_enhanced.php?action=get&admin_id=${adminId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.admin) {
          showAdminDetailsModal(data.admin);
        } else {
          showToast('Failed to load admin details', 'error');
        }
      })
      .catch(error => {
        console.error('Error loading admin details:', error);
        showToast('Error loading admin details', 'error');
      });
  }

  function showAdminDetailsModal(admin) {
    // Create modal HTML
    const modalHTML = `
      <div class="modal fade" id="viewAdminModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-info text-white">
              <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Admin Details</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="text-muted">BASIC INFORMATION</h6>
                  <table class="table table-sm">
                    <tr><th>ID:</th><td>${admin.id}</td></tr>
                    <tr><th>Username:</th><td>${admin.username}</td></tr>
                    <tr><th>Email:</th><td>${admin.email || 'N/A'}</td></tr>
                    <tr><th>Full Name:</th><td>${admin.full_name || 'N/A'}</td></tr>
                    <tr><th>Phone:</th><td>${admin.phone_number || 'N/A'}</td></tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <h6 class="text-muted">ACCOUNT STATUS</h6>
                  <table class="table table-sm">
                    <tr>
                      <th>Role:</th>
                      <td><span class="badge bg-primary">${admin.role}</span></td>
                    </tr>
                    <tr>
                      <th>Status:</th>
                      <td>
                        <span class="badge ${admin.is_active ? 'bg-success' : 'bg-secondary'}">${admin.is_active ? 'Active' : 'Inactive'}</span>
                      </td>
                    </tr>
                    <tr>
                      <th>Online:</th>
                      <td>
                        ${admin.is_currently_active ? 
                          '<span class="badge bg-success status-online"><i class="fas fa-circle me-1 pulse-icon"></i>Online Now</span>' : 
                          '<span class="badge bg-secondary">Offline</span>'}
                      </td>
                    </tr>
                    <tr><th>Created:</th><td>${formatDateTime(admin.created_at)}</td></tr>
                    <tr><th>Last Login:</th><td>${admin.last_login ? formatDateTime(admin.last_login) : 'Never'}</td></tr>
                    <tr>
                      <th>Last Activity:</th>
                      <td>${admin.last_activity ? formatDateTime(admin.last_activity) : 'N/A'}</td>
                    </tr>
                  </table>
                </div>
              </div>
              
              <div class="mt-3">
                <h6 class="text-muted">RECENT ACTIVITY</h6>
                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Time</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${admin.recent_activity && admin.recent_activity.length > 0 ?
                        admin.recent_activity.map(act => `
                          <tr>
                            <td><span class="badge bg-secondary">${act.action_type}</span></td>
                            <td>${act.action_description || 'N/A'}</td>
                            <td>${formatDateTime(act.created_at)}</td>
                          </tr>
                        `).join('') :
                        '<tr><td colspan="3" class="text-center">No recent activity</td></tr>'
                      }
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // Remove existing modal
    const existingModal = document.getElementById('viewAdminModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewAdminModal'));
    modal.show();
    
    // Cleanup on hide
    document.getElementById('viewAdminModal').addEventListener('hidden.bs.modal', function() {
      this.remove();
    });
  }

  // Export to CSV
  window.exportToCSV = function() {
    const csvData = [
      ['ID', 'Username', 'Email', 'Full Name', 'Role', 'Status', 'Last Login'].join(','),
      ...filteredAdmins.map(admin => [
        admin.id,
        admin.username,
        admin.email || '',
        admin.full_name || '',
        admin.role,
        admin.is_active ? 'Active' : 'Inactive',
        admin.last_login || ''
      ].join(','))
    ].join('\n');
    
    const blob = new Blob([csvData], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `admins_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('Admin list exported successfully', 'success');
  };

  // Bulk actions
  window.bulkActivate = async function() {
    if (selectedAdmins.size === 0) return;
    
    const confirmed = await showConfirm(
      `Activate ${selectedAdmins.size} selected admin(s)?`,
      { title: 'Bulk Activate', confirmText: 'Activate', confirmClass: 'btn-success' }
    );
    
    if (!confirmed) return;
    
    // TODO: Implement bulk activate API call
    showToast('Bulk activation feature coming soon', 'info');
  };

  window.bulkDeactivate = async function() {
    if (selectedAdmins.size === 0) return;
    
    const confirmed = await showConfirm(
      `Deactivate ${selectedAdmins.size} selected admin(s)?`,
      { title: 'Bulk Deactivate', confirmText: 'Deactivate', confirmClass: 'btn-warning' }
    );
    
    if (!confirmed) return;
    
    // TODO: Implement bulk deactivate API call
    showToast('Bulk deactivation feature coming soon', 'info');
  };

  window.clearSelection = function() {
    // No longer needed - kept for backward compatibility
  };

  window.clearFilters = function() {
    document.getElementById('admin-search').value = '';
    document.getElementById('admin-role-filter').value = '';
    document.getElementById('admin-status-filter').value = '';
    applyFilters();
  };

  window.changePage = function(page) {
    const totalPages = Math.ceil(filteredAdmins.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayAdmins();
  };

  window.requestAdminAccess = function() {
    if (window.showAdminAuthModal) {
      window.showAdminAuthModal();
    }
  };

  window.loadAdmins = loadAdmins;

  // Utility functions
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function timeAgo(dateString) {
    const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
    
    const intervals = [
      { label: 'year', seconds: 31536000 },
      { label: 'month', seconds: 2592000 },
      { label: 'day', seconds: 86400 },
      { label: 'hour', seconds: 3600 },
      { label: 'minute', seconds: 60 },
      { label: 'second', seconds: 1 }
    ];
    
    for (const interval of intervals) {
      const count = Math.floor(seconds / interval.seconds);
      if (count >= 1) {
        return `${count} ${interval.label}${count !== 1 ? 's' : ''} ago`;
      }
    }
    
    return 'just now';
  }

  function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString();
  }

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

  // View admin details
  window.viewAdminDetails = function(adminId) {
    const admin = adminsData.find(a => a.id === adminId);
    if (!admin) {
      showToast('Admin not found', 'error');
      return;
    }

    const details = `
      <div class="row">
        <div class="col-md-6 mb-3"><strong>ID:</strong> ${admin.id}</div>
        <div class="col-md-6 mb-3"><strong>Username:</strong> ${admin.username}</div>
        <div class="col-md-6 mb-3"><strong>Email:</strong> ${admin.email || 'N/A'}</div>
        <div class="col-md-6 mb-3"><strong>Full Name:</strong> ${admin.full_name || 'N/A'}</div>
        <div class="col-md-6 mb-3"><strong>Phone Number:</strong> ${admin.phone_number || 'N/A'}</div>
        <div class="col-md-6 mb-3"><strong>Role:</strong> <span class="badge bg-primary">${admin.role || 'N/A'}</span></div>
        <div class="col-md-6 mb-3"><strong>Access Level:</strong> <span class="badge bg-info">${admin.access_level || 'N/A'}</span></div>
        <div class="col-md-6 mb-3"><strong>Last Seen:</strong> ${admin.last_seen || 'Never'}</div>
        <div class="col-md-6 mb-3"><strong>Last Login:</strong> ${admin.last_login ? new Date(admin.last_login).toLocaleString() : 'Never'}</div>
        <div class="col-md-6 mb-3"><strong>Last Activity:</strong> ${admin.last_activity ? new Date(admin.last_activity).toLocaleString() : 'Never'}</div>
        <div class="col-md-6 mb-3"><strong>Created:</strong> ${admin.created_at ? new Date(admin.created_at).toLocaleString() : 'N/A'}</div>
        <div class="col-md-6 mb-3"><strong>Status:</strong> ${admin.is_currently_active ? '<span class="badge bg-success"><i class="fas fa-circle pulse-icon me-1"></i>Online</span>' : '<span class="badge bg-secondary">Offline</span>'}</div>
      </div>
    `;

    const modalHtml = `
      <div class="modal fade" id="viewAdminDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-info text-white">
              <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Administrator Details: ${admin.username}</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">${details}</div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Close</button>
            </div>
          </div>
        </div>
      </div>
    `;

    const existingModal = document.getElementById('viewAdminDetailsModal');
    if (existingModal) existingModal.remove();
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('viewAdminDetailsModal'));
    modal.show();
    document.getElementById('viewAdminDetailsModal').addEventListener('hidden.bs.modal', function() {
      this.remove();
    });
  };
})();

