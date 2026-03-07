(function () {
  'use strict';

  function ensureStyles() {
    if (document.getElementById('barcie-popup-styles')) return;

    var style = document.createElement('style');
    style.id = 'barcie-popup-styles';
    style.textContent = [
      '.barcie-popup-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px; }',
      '.barcie-popup-overlay[hidden] { display: none !important; }',
      '.barcie-popup-card { width: min(520px, 96vw); background: #fff; border-radius: 14px; box-shadow: 0 16px 40px rgba(0,0,0,.25); border: 1px solid #e5e7eb; overflow: hidden; animation: barciePopupIn .2s ease-out; }',
      '.barcie-popup-loading { width: min(360px, 92vw); text-align: center; padding: 24px; }',
      '.barcie-popup-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; }',
      '.barcie-popup-title { margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a; }',
      '.barcie-popup-body { padding: 18px 20px; }',
      '.barcie-popup-message { margin: 0; color: #334155; line-height: 1.5; }',
      '.barcie-popup-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px 18px; border-top: 1px solid #e5e7eb; }',
      '.barcie-popup-success .barcie-popup-header { background: #ecfdf3; }',
      '.barcie-popup-success .barcie-popup-title { color: #166534; }',
      '.barcie-popup-error .barcie-popup-header { background: #fef2f2; }',
      '.barcie-popup-error .barcie-popup-title { color: #991b1b; }',
      '.barcie-loading-spinner { width: 36px; height: 36px; margin: 0 auto 12px; border: 4px solid #dbeafe; border-top-color: #2563eb; border-radius: 999px; animation: barcieSpin .8s linear infinite; }',
      '.barcie-toast-list { position: fixed; top: 18px; right: 18px; z-index: 99998; display: flex; flex-direction: column; gap: 10px; max-width: 96vw; }',
      '.barcie-toast { min-width: 280px; max-width: 520px; background: #fff; border-left: 4px solid #0ea5e9; color: #0f172a; border-radius: 10px; box-shadow: 0 10px 24px rgba(0,0,0,.2); padding: 12px 14px; display: flex; align-items: start; gap: 10px; animation: barcieToastIn .22s ease-out; }',
      '.barcie-toast-success { border-left-color: #16a34a; background: #f0fdf4; }',
      '.barcie-toast-error { border-left-color: #dc2626; background: #fef2f2; }',
      '.barcie-toast-warning { border-left-color: #d97706; background: #fffbeb; }',
      '.barcie-toast-info { border-left-color: #0284c7; background: #f0f9ff; }',
      '.barcie-toast-close { margin-left: auto; border: 0; background: transparent; color: #475569; font-size: 20px; line-height: 1; cursor: pointer; }',
      '.barcie-no-scroll { overflow: hidden !important; }',
      '@keyframes barciePopupIn { from { opacity: 0; transform: translateY(-8px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }',
      '@keyframes barcieSpin { to { transform: rotate(360deg); } }',
      '@keyframes barcieToastIn { from { opacity: 0; transform: translateX(16px); } to { opacity: 1; transform: translateX(0); } }'
    ].join('');

    document.head.appendChild(style);
  }

  function setText(id, text, allowHtml) {
    var el = document.getElementById(id);
    if (!el) return;
    if (allowHtml) {
      el.innerHTML = text;
    } else {
      el.textContent = text;
    }
  }

  function showOverlay(id, lockScroll) {
    ensureStyles();
    var overlay = document.getElementById(id);
    if (!overlay) return null;
    overlay.hidden = false;
    overlay.setAttribute('aria-hidden', 'false');
    if (lockScroll) {
      document.body.classList.add('barcie-no-scroll');
    }
    return overlay;
  }

  function hideOverlay(id) {
    var overlay = document.getElementById(id);
    if (!overlay) return;
    overlay.hidden = true;
    overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('barcie-no-scroll');
  }

  function ensureToastHost() {
    ensureStyles();
    var host = document.getElementById('barcieToastList');
    if (!host) {
      host = document.createElement('div');
      host.id = 'barcieToastList';
      host.className = 'barcie-toast-list';
      document.body.appendChild(host);
    }
    return host;
  }

  function addDismissBehavior(overlay, onResolve) {
    if (!overlay) return;
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) {
        onResolve(false);
      }
    });
  }

  window.showSuccessPopup = function (message, options) {
    options = options || {};
    setText('barcieSuccessTitle', options.title || 'Success', false);
    setText('barcieSuccessMessage', message || 'Action completed successfully.', !!options.allowHtml);

    var overlay = showOverlay('barcieSuccessPopup', false);
    var okBtn = document.getElementById('barcieSuccessOk');

    var close = function () {
      hideOverlay('barcieSuccessPopup');
    };

    if (okBtn) {
      okBtn.textContent = options.buttonText || 'OK';
      okBtn.onclick = close;
    }

    addDismissBehavior(overlay, function () {
      close();
    });

    if (options.autoCloseMs && Number(options.autoCloseMs) > 0) {
      setTimeout(close, Number(options.autoCloseMs));
    }

    return { remove: close, close: close };
  };

  window.showErrorPopup = function (message, options) {
    options = options || {};
    setText('barcieErrorTitle', options.title || 'Error', false);
    setText('barcieErrorMessage', message || 'Something went wrong. Please try again.', !!options.allowHtml);

    var overlay = showOverlay('barcieErrorPopup', false);
    var okBtn = document.getElementById('barcieErrorOk');

    var close = function () {
      hideOverlay('barcieErrorPopup');
    };

    if (okBtn) {
      okBtn.textContent = options.buttonText || 'Close';
      okBtn.onclick = close;
    }

    addDismissBehavior(overlay, function () {
      close();
    });

    if (options.autoCloseMs && Number(options.autoCloseMs) > 0) {
      setTimeout(close, Number(options.autoCloseMs));
    }

    return { remove: close, close: close };
  };

  window.showLoadingPopup = function (message) {
    setText('barcieLoadingMessage', message || 'Processing request...', false);
    showOverlay('barcieLoadingPopup', true);

    var close = function () {
      hideOverlay('barcieLoadingPopup');
    };

    return {
      remove: close,
      close: close,
      update: function (nextMessage) {
        setText('barcieLoadingMessage', nextMessage || 'Processing request...', false);
      }
    };
  };

  window.hideLoadingPopup = function () {
    hideOverlay('barcieLoadingPopup');
  };

  window.showConfirm = function (message, options) {
    options = options || {};

    return new Promise(function (resolve) {
      setText('barcieConfirmTitle', options.title || 'Confirm Action', false);
      setText('barcieConfirmMessage', message || 'Are you sure you want to continue?', !!options.allowHtml);

      var overlay = showOverlay('barcieConfirmPopup', true);
      var okBtn = document.getElementById('barcieConfirmOk');
      var cancelBtn = document.getElementById('barcieConfirmCancel');

      if (okBtn) {
        okBtn.textContent = options.confirmText || 'Confirm';
        okBtn.classList.remove('btn-danger', 'btn-primary', 'btn-success', 'btn-warning');
        okBtn.classList.add((options.confirmClass || 'btn-primary').replace('.', '').trim());
      }

      if (cancelBtn) {
        cancelBtn.textContent = options.cancelText || 'Cancel';
      }

      var done = false;
      var finish = function (result) {
        if (done) return;
        done = true;
        hideOverlay('barcieConfirmPopup');
        resolve(!!result);
      };

      if (okBtn) okBtn.onclick = function () { finish(true); };
      if (cancelBtn) cancelBtn.onclick = function () { finish(false); };

      addDismissBehavior(overlay, finish);

      var escHandler = function (e) {
        if (e.key === 'Escape') {
          document.removeEventListener('keydown', escHandler);
          finish(false);
        }
      };
      document.addEventListener('keydown', escHandler);
    });
  };

  window.showConfirmModal = window.showConfirm;

  window.showToast = function (message, type, duration) {
    type = type || 'info';
    duration = typeof duration === 'number' ? duration : 4500;

    var normalized = type === 'danger' ? 'error' : type;
    if (normalized === 'success') {
      return window.showSuccessPopup(message, { title: 'Success', autoCloseMs: duration, allowHtml: true, buttonText: 'OK' });
    }
    if (normalized === 'error') {
      return window.showErrorPopup(message, { title: 'Error', autoCloseMs: duration, allowHtml: true, buttonText: 'Close' });
    }

    var host = ensureToastHost();
    var toast = document.createElement('div');
    var toastType = (normalized === 'warning' || normalized === 'info') ? normalized : 'info';
    toast.className = 'barcie-toast barcie-toast-' + toastType;

    var msg = document.createElement('div');
    msg.style.flex = '1';
    msg.innerHTML = String(message || 'Notification');

    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'barcie-toast-close';
    close.textContent = 'x';

    var remove = function () {
      if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
    };

    close.onclick = remove;

    toast.appendChild(msg);
    toast.appendChild(close);
    host.appendChild(toast);

    if (duration > 0) {
      setTimeout(remove, duration);
    }

    return { remove: remove, close: remove };
  };

  window.toast = window.showToast;

  window.showAdminAlert = function (typeOrMessage, messageOrType, duration) {
    if (typeof messageOrType === 'string' && /success|danger|error|warning|info/.test(messageOrType)) {
      return window.showToast(typeOrMessage, messageOrType, duration || 5000);
    }
    return window.showToast(typeOrMessage, messageOrType || 'info', duration || 5000);
  };

  var originalAlert = window.alert;
  window.alert = function (message) {
    try {
      window.showErrorPopup(String(message || ''), { title: 'Notice' });
    } catch (e) {
      originalAlert(message);
    }
  };
})();
