(function () {
  "use strict";

  function ensureStyles() {
    if (document.getElementById("barcie-popup-styles")) return;

    var style = document.createElement("style");
    style.id = "barcie-popup-styles";
    style.textContent = [
      /* overlay */
      ".barcie-popup-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;transition:opacity .2s ease}",
      ".barcie-popup-overlay.barcie-show{opacity:1}",
      ".barcie-popup-overlay[hidden]{display:none!important}",

      /* card */
      ".barcie-popup-card{width:min(400px,92vw);background:#fff;border-radius:20px;box-shadow:0 24px 48px rgba(0,0,0,.18);overflow:hidden;text-align:center;padding:32px 28px 24px;transform:translateY(12px) scale(.96);transition:transform .25s cubic-bezier(.34,1.56,.64,1)}",
      ".barcie-popup-overlay.barcie-show .barcie-popup-card{transform:translateY(0) scale(1)}",
      ".barcie-popup-loading{width:min(320px,88vw);padding:36px 28px}",

      /* icon ring */
      ".barcie-popup-icon-ring{width:64px;height:64px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:24px}",
      ".barcie-popup-icon-confirm{background:rgba(59,130,246,.12);color:#3b82f6;border:2px solid rgba(59,130,246,.25)}",
      ".barcie-popup-icon-success{background:rgba(16,185,129,.12);color:#10b981;border:2px solid rgba(16,185,129,.25)}",
      ".barcie-popup-icon-error{background:rgba(239,68,68,.12);color:#ef4444;border:2px solid rgba(239,68,68,.25)}",

      /* heading & message */
      ".barcie-popup-heading{margin:0 0 8px;font-size:1.15rem;font-weight:700;color:#0f172a}",
      ".barcie-popup-message{margin:0 0 20px;color:#64748b;font-size:.925rem;line-height:1.55}",

      /* buttons */
      ".barcie-popup-actions{display:flex;gap:10px;justify-content:center}",
      ".barcie-btn{padding:9px 22px;border-radius:10px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;transition:all .15s ease;outline:none}",
      ".barcie-btn:focus-visible{box-shadow:0 0 0 3px rgba(59,130,246,.4)}",
      ".barcie-btn-primary{background:#3b82f6;color:#fff}",
      ".barcie-btn-primary:hover{background:#2563eb}",
      ".barcie-btn-success{background:#10b981;color:#fff}",
      ".barcie-btn-success:hover{background:#059669}",
      ".barcie-btn-danger{background:#ef4444;color:#fff}",
      ".barcie-btn-danger:hover{background:#dc2626}",
      ".barcie-btn-warning{background:#f59e0b;color:#fff}",
      ".barcie-btn-warning:hover{background:#d97706}",
      ".barcie-btn-ghost{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0}",
      ".barcie-btn-ghost:hover{background:#e2e8f0;color:#334155}",

      /* loading ring */
      ".barcie-loading-ring{width:56px;height:56px;margin:0 auto 18px}",
      ".barcie-loading-ring svg{width:100%;height:100%;animation:barcieSpin .9s linear infinite}",
      ".barcie-loading-ring circle{stroke:#3b82f6;stroke-dasharray:80,200;stroke-dashoffset:-10}",

      /* toast */
      ".barcie-toast-list{position:fixed;top:20px;right:20px;z-index:99998;display:flex;flex-direction:column;gap:10px;max-width:400px;pointer-events:none}",
      ".barcie-toast{pointer-events:auto;min-width:280px;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);padding:14px 16px;display:flex;align-items:start;gap:12px;transform:translateX(20px);opacity:0;transition:all .25s ease}",
      ".barcie-toast.barcie-toast-show{transform:translateX(0);opacity:1}",
      ".barcie-toast-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px}",
      ".barcie-toast-success .barcie-toast-icon{background:rgba(16,185,129,.12);color:#10b981}",
      ".barcie-toast-error .barcie-toast-icon{background:rgba(239,68,68,.12);color:#ef4444}",
      ".barcie-toast-warning .barcie-toast-icon{background:rgba(245,158,11,.12);color:#f59e0b}",
      ".barcie-toast-info .barcie-toast-icon{background:rgba(59,130,246,.12);color:#3b82f6}",
      ".barcie-toast-body{flex:1;min-width:0}",
      ".barcie-toast-title{font-weight:600;font-size:.85rem;color:#0f172a;margin:0 0 2px}",
      ".barcie-toast-text{font-size:.82rem;color:#64748b;margin:0;line-height:1.45;word-wrap:break-word}",
      ".barcie-toast-close{margin-left:auto;border:0;background:transparent;color:#94a3b8;font-size:16px;cursor:pointer;padding:0;line-height:1;flex-shrink:0;transition:color .15s ease}",
      ".barcie-toast-close:hover{color:#475569}",
      ".barcie-toast-bar{position:absolute;bottom:0;left:0;height:3px;border-radius:0 0 12px 12px;transition:width linear}",
      ".barcie-toast-success .barcie-toast-bar{background:#10b981}",
      ".barcie-toast-error .barcie-toast-bar{background:#ef4444}",
      ".barcie-toast-warning .barcie-toast-bar{background:#f59e0b}",
      ".barcie-toast-info .barcie-toast-bar{background:#3b82f6}",

      /* util */
      ".barcie-no-scroll{overflow:hidden!important}",
      "@keyframes barcieSpin{to{transform:rotate(360deg)}}",
    ].join("\n");

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
    overlay.setAttribute("aria-hidden", "false");
    // trigger reflow then add class for animation
    void overlay.offsetWidth;
    overlay.classList.add("barcie-show");
    if (lockScroll) {
      document.body.classList.add("barcie-no-scroll");
    }
    return overlay;
  }

  function hideOverlay(id) {
    var overlay = document.getElementById(id);
    if (!overlay) return;
    overlay.classList.remove("barcie-show");
    setTimeout(function () {
      overlay.hidden = true;
      overlay.setAttribute("aria-hidden", "true");
      document.body.classList.remove("barcie-no-scroll");
    }, 220);
  }

  function ensureToastHost() {
    ensureStyles();
    var host = document.getElementById("barcieToastList");
    if (!host) {
      host = document.createElement("div");
      host.id = "barcieToastList";
      host.className = "barcie-toast-list";
      document.body.appendChild(host);
    }
    return host;
  }

  function addDismissBehavior(overlay, onResolve) {
    if (!overlay) return;
    overlay.addEventListener("click", function (e) {
      if (e.target === overlay) {
        onResolve(false);
      }
    });
  }

  /* ─── Success Popup ─── */
  window.showSuccessPopup = function (message, options) {
    options = options || {};
    setText("barcieSuccessTitle", options.title || "Success", false);
    setText(
      "barcieSuccessMessage",
      message || "Action completed successfully.",
      !!options.allowHtml,
    );

    var overlay = showOverlay("barcieSuccessPopup", false);
    var okBtn = document.getElementById("barcieSuccessOk");

    var close = function () {
      hideOverlay("barcieSuccessPopup");
    };

    if (okBtn) {
      okBtn.textContent = options.buttonText || "OK";
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

  /* ─── Error Popup ─── */
  window.showErrorPopup = function (message, options) {
    options = options || {};
    setText("barcieErrorTitle", options.title || "Error", false);
    setText(
      "barcieErrorMessage",
      message || "Something went wrong. Please try again.",
      !!options.allowHtml,
    );

    var overlay = showOverlay("barcieErrorPopup", false);
    var okBtn = document.getElementById("barcieErrorOk");

    var close = function () {
      hideOverlay("barcieErrorPopup");
    };

    if (okBtn) {
      okBtn.textContent = options.buttonText || "Close";
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

  /* ─── Loading Popup ─── */
  window.showLoadingPopup = function (message) {
    setText("barcieLoadingMessage", message || "Processing request...", false);
    showOverlay("barcieLoadingPopup", true);

    var close = function () {
      hideOverlay("barcieLoadingPopup");
    };

    return {
      remove: close,
      close: close,
      update: function (nextMessage) {
        setText(
          "barcieLoadingMessage",
          nextMessage || "Processing request...",
          false,
        );
      },
    };
  };

  window.hideLoadingPopup = function () {
    hideOverlay("barcieLoadingPopup");
  };

  /* ─── Confirm Popup (returns Promise) ─── */
  window.showConfirm = function (message, options) {
    options = options || {};

    return new Promise(function (resolve) {
      setText("barcieConfirmTitle", options.title || "Confirm Action", false);
      setText(
        "barcieConfirmMessage",
        message || "Are you sure you want to continue?",
        !!options.allowHtml,
      );

      var overlay = showOverlay("barcieConfirmPopup", true);
      var okBtn = document.getElementById("barcieConfirmOk");
      var cancelBtn = document.getElementById("barcieConfirmCancel");

      if (okBtn) {
        okBtn.textContent = options.confirmText || "Confirm";
        // Map Bootstrap-style class names to barcie button classes
        var classMap = {
          "btn-danger": "barcie-btn-danger",
          "btn-primary": "barcie-btn-primary",
          "btn-success": "barcie-btn-success",
          "btn-warning": "barcie-btn-warning",
        };
        okBtn.className = "barcie-btn";
        var requestedClass = (options.confirmClass || "btn-primary")
          .replace(".", "")
          .trim();
        okBtn.classList.add(classMap[requestedClass] || "barcie-btn-primary");
      }

      if (cancelBtn) {
        cancelBtn.textContent = options.cancelText || "Cancel";
      }

      var done = false;
      var finish = function (result) {
        if (done) return;
        done = true;
        hideOverlay("barcieConfirmPopup");
        resolve(!!result);
      };

      if (okBtn)
        okBtn.onclick = function () {
          finish(true);
        };
      if (cancelBtn)
        cancelBtn.onclick = function () {
          finish(false);
        };

      addDismissBehavior(overlay, finish);

      var escHandler = function (e) {
        if (e.key === "Escape") {
          document.removeEventListener("keydown", escHandler);
          finish(false);
        }
      };
      document.addEventListener("keydown", escHandler);
    });
  };

  window.showConfirmModal = window.showConfirm;

  /* ─── Toast Notifications ─── */
  var toastIcons = {
    success: "fa-check",
    error: "fa-times",
    warning: "fa-exclamation",
    info: "fa-info",
  };

  var toastTitles = {
    success: "Success",
    error: "Error",
    warning: "Warning",
    info: "Info",
  };

  window.showToast = function (message, type, duration) {
    type = type || "info";
    duration = typeof duration === "number" ? duration : 4500;

    var normalized = type === "danger" ? "error" : type;
    if (!toastIcons[normalized]) normalized = "info";

    var host = ensureToastHost();
    var toast = document.createElement("div");
    toast.className = "barcie-toast barcie-toast-" + normalized;
    toast.style.position = "relative";
    toast.style.overflow = "hidden";

    toast.innerHTML =
      '<div class="barcie-toast-icon"><i class="fas ' +
      toastIcons[normalized] +
      '"></i></div>' +
      '<div class="barcie-toast-body">' +
      '<p class="barcie-toast-title">' +
      toastTitles[normalized] +
      "</p>" +
      '<p class="barcie-toast-text">' +
      String(message || "Notification") +
      "</p>" +
      "</div>" +
      '<button type="button" class="barcie-toast-close" aria-label="Close">&times;</button>' +
      '<div class="barcie-toast-bar" style="width:100%"></div>';

    host.appendChild(toast);

    // Animate in
    requestAnimationFrame(function () {
      toast.classList.add("barcie-toast-show");
    });

    // Progress bar
    var bar = toast.querySelector(".barcie-toast-bar");
    if (bar && duration > 0) {
      bar.style.transitionDuration = duration + "ms";
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          bar.style.width = "0%";
        });
      });
    }

    var remove = function () {
      toast.classList.remove("barcie-toast-show");
      setTimeout(function () {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 250);
    };

    toast.querySelector(".barcie-toast-close").onclick = remove;

    if (duration > 0) {
      setTimeout(remove, duration);
    }

    return { remove: remove, close: remove };
  };

  window.toast = window.showToast;

  window.showAdminAlert = function (typeOrMessage, messageOrType, duration) {
    if (
      typeof messageOrType === "string" &&
      /success|danger|error|warning|info/.test(messageOrType)
    ) {
      return window.showToast(typeOrMessage, messageOrType, duration || 5000);
    }
    return window.showToast(
      typeOrMessage,
      messageOrType || "info",
      duration || 5000,
    );
  };

  var originalAlert = window.alert;
  window.alert = function (message) {
    try {
      window.showErrorPopup(String(message || ""), { title: "Notice" });
    } catch (e) {
      originalAlert(message);
    }
  };
})();
