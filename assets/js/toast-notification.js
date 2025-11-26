/**
 * Toast Notification System
 * Replaces alert() with elegant toast notifications
 */

(function() {
  'use strict';

  // Create toast container if it doesn't exist
  function ensureToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
      `;
      document.body.appendChild(container);
    }
    return container;
  }

  /**
   * Show a toast notification
   * @param {string} message - The message to display
   * @param {string} type - Type: 'success', 'error', 'warning', 'info', 'danger'
   * @param {number} duration - Duration in milliseconds (default: 5000)
   */
  window.showToast = function(message, type = 'info', duration = 5000) {
    const container = ensureToastContainer();
    
    // Map 'danger' to 'error' for consistency
    if (type === 'danger') type = 'error';
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.style.cssText = `
      min-width: 300px;
      max-width: 500px;
      padding: 16px 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      display: flex;
      align-items: center;
      gap: 12px;
      pointer-events: auto;
      animation: slideIn 0.3s ease-out;
      border-left: 4px solid;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      font-size: 14px;
      line-height: 1.5;
    `;

    // Set colors based on type
    const colors = {
      success: { border: '#28a745', icon: '#28a745', bg: '#d4edda' },
      error: { border: '#dc3545', icon: '#dc3545', bg: '#f8d7da' },
      warning: { border: '#ffc107', icon: '#ffc107', bg: '#fff3cd' },
      info: { border: '#17a2b8', icon: '#17a2b8', bg: '#d1ecf1' }
    };
    
    const color = colors[type] || colors.info;
    toast.style.borderLeftColor = color.border;
    toast.style.backgroundColor = color.bg;

    // Icon
    const icons = {
      success: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
      error: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
      warning: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
      info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    };

    const iconWrapper = document.createElement('div');
    iconWrapper.style.cssText = `
      flex-shrink: 0;
      color: ${color.icon};
    `;
    iconWrapper.innerHTML = icons[type] || icons.info;

    // Message
    const messageEl = document.createElement('div');
    messageEl.style.cssText = `
      flex: 1;
      color: #333;
      word-break: break-word;
    `;
    messageEl.textContent = message;

    // Close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
      background: none;
      border: none;
      font-size: 24px;
      line-height: 1;
      color: #666;
      cursor: pointer;
      padding: 0;
      margin-left: 8px;
      flex-shrink: 0;
      transition: color 0.2s;
    `;
    closeBtn.onmouseover = () => closeBtn.style.color = '#000';
    closeBtn.onmouseout = () => closeBtn.style.color = '#666';
    closeBtn.onclick = () => removeToast(toast);

    toast.appendChild(iconWrapper);
    toast.appendChild(messageEl);
    toast.appendChild(closeBtn);

    // Add animation styles if not already added
    if (!document.getElementById('toast-styles')) {
      const style = document.createElement('style');
      style.id = 'toast-styles';
      style.textContent = `
        @keyframes slideIn {
          from {
            transform: translateX(400px);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
        @keyframes slideOut {
          from {
            transform: translateX(0);
            opacity: 1;
          }
          to {
            transform: translateX(400px);
            opacity: 0;
          }
        }
        .toast-notification.removing {
          animation: slideOut 0.3s ease-out forwards;
        }
      `;
      document.head.appendChild(style);
    }

    container.appendChild(toast);

    // Auto remove after duration
    if (duration > 0) {
      setTimeout(() => removeToast(toast), duration);
    }

    return toast;
  };

  function removeToast(toast) {
    if (!toast || !toast.parentElement) return;
    
    toast.classList.add('removing');
    setTimeout(() => {
      if (toast.parentElement) {
        toast.parentElement.removeChild(toast);
      }
    }, 300);
  }

  // Alias for compatibility
  window.toast = window.showToast;

})();
