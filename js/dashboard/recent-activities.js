// recent-activities.js
// Handles fetching the recent activities fragment, debounce, auto-refresh, and toasts.
(function () {
  'use strict';

  var debounceMs = 800; // minimal interval between manual refreshes
  var _lastCall = 0;
  var _autoRefreshId = null;

  function joinPath(base, path) {
    if (!base) return path.startsWith('/') ? path : '/' + path;
    return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
  }

  function createToastContainer() {
    var id = 'barcie-toast-container';
    var container = document.getElementById(id);
    if (!container) {
      container = document.createElement('div');
      container.id = id;
      container.style.position = 'fixed';
      container.style.top = '1rem';
      container.style.right = '1rem';
      container.style.zIndex = 1060; // above modals
      document.body.appendChild(container);
    }
    return container;
  }

  function showToast(message, type) {
    // type: 'success' | 'danger' | 'info'
    var container = createToastContainer();
    var toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-' + (type || 'info') + ' border-0';
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.style.minWidth = '200px';
    toast.style.marginBottom = '0.5rem';

    toast.innerHTML = '<div class="d-flex"><div class="toast-body">' +
      (message || '') + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';

    container.appendChild(toast);
    // Use Bootstrap Toast if available
    try {
      if (window.bootstrap && window.bootstrap.Toast) {
        var bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 4000 });
        bsToast.show();
        // Remove element when hidden
        toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
      } else {
        // Fallback: remove after timeout
        setTimeout(function () { toast.remove(); }, 4000);
      }
    } catch (e) {
      console.warn('Toast show failed', e);
      setTimeout(function () { toast.remove(); }, 4000);
    }
  }

  function fetchFragment(url, onSuccess, onError) {
    fetch(url, { credentials: 'same-origin' })
      .then(function (resp) {
        if (!resp.ok) throw new Error('Network response was not ok');
        return resp.text();
      })
      .then(function (html) {
        onSuccess && onSuccess(html);
      })
      .catch(function (err) {
        onError && onError(err);
      });
  }

  function refreshRecentActivities(btn, options) {
    options = options || {};
    var now = Date.now();
    if (now - _lastCall < debounceMs) return; // debounce
    _lastCall = now;

    var button = btn || null;
    var originalHTML = null;
    if (button) {
      originalHTML = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Refreshing';
    }

    var base = window.BARCIE_BASE_PATH || '';
    var targetPath = 'components/dashboard/sections/recent_activities_fragment.php';
    var url = joinPath(base, targetPath);

    fetchFragment(url, function (html) {
      var container = document.getElementById('recent-activities-container');
      if (container) container.innerHTML = html;
      if (button) { button.disabled = false; button.innerHTML = originalHTML; }
      if (!options.silent) showToast('Recent activities updated', 'success');
    }, function (err) {
      console.error('Failed to refresh recent activities:', err);
      if (button) { button.disabled = false; button.innerHTML = originalHTML; }
      showToast('Failed to refresh recent activities', 'danger');
    });
  }

  function startRecentActivitiesAutoRefresh(intervalSeconds) {
    stopRecentActivitiesAutoRefresh();
    if (!intervalSeconds || isNaN(intervalSeconds) || intervalSeconds <= 0) return;
    _autoRefreshId = setInterval(function () {
      refreshRecentActivities(null, { silent: true });
    }, Math.max(1000, intervalSeconds * 1000));
  }

  function stopRecentActivitiesAutoRefresh() {
    if (_autoRefreshId) { clearInterval(_autoRefreshId); _autoRefreshId = null; }
  }

  // Expose API
  window.refreshRecentActivities = refreshRecentActivities;
  window.startRecentActivitiesAutoRefresh = startRecentActivitiesAutoRefresh;
  window.stopRecentActivitiesAutoRefresh = stopRecentActivitiesAutoRefresh;

})();
