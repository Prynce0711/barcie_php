(function () {
  "use strict";

  function ensureStyles() {
    if (document.getElementById("barcie-popup-styles")) return;

    var style = document.createElement("style");
    style.id = "barcie-popup-styles";
    style.textContent = [
      /* overlay */
      ".barcie-popup-overlay{position:fixed;inset:0;background:rgba(2,6,23,.44);backdrop-filter:none;-webkit-backdrop-filter:none;z-index:99999;display:flex;align-items:center;justify-content:center;padding:24px;opacity:0;transition:opacity .22s ease}",
      ".barcie-popup-overlay.barcie-show{opacity:1}",
      ".barcie-popup-overlay[hidden]{display:none!important}",

      /* card */
      ".barcie-popup-card{position:relative;width:min(440px,92vw);background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);border:1px solid rgba(148,163,184,.28);border-radius:24px;box-shadow:0 30px 65px rgba(2,6,23,.34),inset 0 1px 0 rgba(255,255,255,.65);overflow:hidden;text-align:center;padding:36px 30px 26px;transform:translateY(16px) scale(.96);transition:transform .28s cubic-bezier(.2,.8,.2,1)}",
      ".barcie-popup-card:before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#3b82f6,#22c55e,#f59e0b)}",
      ".barcie-popup-overlay.barcie-show .barcie-popup-card{transform:translateY(0) scale(1)}",
      ".barcie-popup-loading{width:min(340px,90vw);padding:38px 28px 30px}",
      ".barcie-popup-success:before{background:linear-gradient(90deg,#34d399,#10b981)}",
      ".barcie-popup-error:before{background:linear-gradient(90deg,#fb7185,#ef4444)}",

      /* icon ring */
      ".barcie-popup-icon-ring{width:70px;height:70px;border-radius:50%;margin:2px auto 16px;display:flex;align-items:center;justify-content:center;font-size:26px;box-shadow:inset 0 0 0 1px rgba(255,255,255,.8),0 8px 20px rgba(15,23,42,.15)}",
      ".barcie-popup-icon-confirm{background:linear-gradient(135deg,rgba(59,130,246,.18),rgba(59,130,246,.08));color:#2563eb;border:1px solid rgba(59,130,246,.35)}",
      ".barcie-popup-icon-success{background:linear-gradient(135deg,rgba(16,185,129,.2),rgba(16,185,129,.08));color:#059669;border:1px solid rgba(16,185,129,.36)}",
      ".barcie-popup-icon-error{background:linear-gradient(135deg,rgba(239,68,68,.22),rgba(239,68,68,.09));color:#dc2626;border:1px solid rgba(239,68,68,.36)}",
      ".barcie-popup-icon-warning{background:linear-gradient(135deg,rgba(245,158,11,.22),rgba(245,158,11,.09));color:#b45309;border:1px solid rgba(245,158,11,.36)}",
      ".barcie-popup-icon-info{background:linear-gradient(135deg,rgba(59,130,246,.2),rgba(59,130,246,.08));color:#1d4ed8;border:1px solid rgba(59,130,246,.36)}",

      /* heading & message */
      ".barcie-popup-heading{margin:0 0 8px;font-size:1.22rem;font-weight:800;letter-spacing:.01em;color:#0f172a}",
      ".barcie-popup-message{margin:0 0 22px;color:#475569;font-size:.96rem;line-height:1.62}",

      /* buttons */
      ".barcie-popup-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}",
      ".barcie-btn{padding:10px 22px;border-radius:12px;font-size:.89rem;font-weight:700;border:1px solid transparent;cursor:pointer;transition:transform .16s ease,box-shadow .16s ease,background .16s ease,color .16s ease;outline:none}",
      ".barcie-btn:hover{transform:translateY(-1px)}",
      ".barcie-btn:active{transform:translateY(0)}",
      ".barcie-btn:focus-visible{box-shadow:0 0 0 4px rgba(59,130,246,.25)}",
      ".barcie-btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;box-shadow:0 10px 22px rgba(37,99,235,.28)}",
      ".barcie-btn-primary:hover{background:linear-gradient(135deg,#2563eb,#1d4ed8)}",
      ".barcie-btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 10px 22px rgba(5,150,105,.24)}",
      ".barcie-btn-success:hover{background:linear-gradient(135deg,#059669,#047857)}",
      ".barcie-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;box-shadow:0 10px 22px rgba(220,38,38,.24)}",
      ".barcie-btn-danger:hover{background:linear-gradient(135deg,#dc2626,#b91c1c)}",
      ".barcie-btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;box-shadow:0 10px 22px rgba(217,119,6,.24)}",
      ".barcie-btn-warning:hover{background:linear-gradient(135deg,#d97706,#b45309)}",
      ".barcie-btn-ghost{background:#f8fafc;color:#334155;border-color:#dbe2ea}",
      ".barcie-btn-ghost:hover{background:#eef2f7;color:#1e293b}",

      /* loading ring */
      ".barcie-loading-ring{width:58px;height:58px;margin:2px auto 18px}",
      ".barcie-loading-ring svg{width:100%;height:100%;animation:barcieSpin .85s linear infinite}",
      ".barcie-loading-ring circle{stroke:#2563eb;stroke-dasharray:84,200;stroke-dashoffset:-10}",

      /* non-blocking notifications */
      ".barcie-notice-host{position:fixed;top:16px;left:50%;transform:translateX(-50%);width:min(94vw,460px);display:flex;flex-direction:column;gap:10px;z-index:100001;pointer-events:none}",
      ".barcie-notice{pointer-events:auto;position:relative;display:flex;align-items:flex-start;gap:10px;background:#fff;border:1px solid rgba(148,163,184,.35);border-radius:14px;padding:12px 14px;box-shadow:0 14px 28px rgba(2,6,23,.18);opacity:0;transform:translateY(-10px) scale(.98);transition:transform .2s ease,opacity .2s ease}",
      ".barcie-notice:before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:14px 0 0 14px;background:#94a3b8}",
      ".barcie-notice-show{opacity:1;transform:translateY(0) scale(1)}",
      ".barcie-notice-hide{opacity:0;transform:translateY(-10px) scale(.98)}",
      ".barcie-notice-success:before{background:#10b981}",
      ".barcie-notice-error:before{background:#ef4444}",
      ".barcie-notice-warning:before{background:#f59e0b}",
      ".barcie-notice-info:before{background:#3b82f6}",
      ".barcie-notice-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex:0 0 30px;font-size:13px;margin-top:1px}",
      ".barcie-notice-success .barcie-notice-icon{background:rgba(16,185,129,.14);color:#047857}",
      ".barcie-notice-error .barcie-notice-icon{background:rgba(239,68,68,.14);color:#b91c1c}",
      ".barcie-notice-warning .barcie-notice-icon{background:rgba(245,158,11,.16);color:#b45309}",
      ".barcie-notice-info .barcie-notice-icon{background:rgba(59,130,246,.14);color:#1d4ed8}",
      ".barcie-notice-body{flex:1;min-width:0}",
      ".barcie-notice-title{margin:0 0 2px;font-size:.88rem;font-weight:700;color:#0f172a}",
      ".barcie-notice-message{margin:0;color:#475569;font-size:.84rem;line-height:1.45;word-break:break-word}",
      ".barcie-notice-close{border:0;background:transparent;color:#64748b;font-size:16px;line-height:1;padding:1px 0 0;cursor:pointer;flex:0 0 auto}",
      ".barcie-notice-close:hover{color:#334155}",

      /* util */
      ".barcie-no-scroll{overflow:hidden!important}",
      "@media (max-width:560px){.barcie-popup-overlay{padding:14px}.barcie-popup-card{border-radius:20px;padding:30px 20px 22px}.barcie-popup-icon-ring{width:64px;height:64px;font-size:23px}.barcie-popup-heading{font-size:1.1rem}.barcie-popup-message{font-size:.92rem}}",
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

    // Default success feedback should be non-blocking and auto-dismiss.
    if (options.modal !== true) {
      return showTransientNotice(
        message || "Action completed successfully.",
        "success",
        typeof options.autoCloseMs === "number" ? options.autoCloseMs : 3000,
        options.title || "Success",
      );
    }

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

    var normalizedVariant = normalizeNoticeType(options.variant || "error");
    var iconRing = document.querySelector(
      "#barcieErrorPopup .barcie-popup-icon-ring",
    );
    var icon = iconRing ? iconRing.querySelector("i") : null;

    if (iconRing) {
      iconRing.classList.remove(
        "barcie-popup-icon-error",
        "barcie-popup-icon-warning",
        "barcie-popup-icon-info",
      );
      if (normalizedVariant === "warning") {
        iconRing.classList.add("barcie-popup-icon-warning");
      } else if (normalizedVariant === "info") {
        iconRing.classList.add("barcie-popup-icon-info");
      } else {
        iconRing.classList.add("barcie-popup-icon-error");
      }
    }

    if (icon) {
      if (normalizedVariant === "warning") {
        icon.className = "fas fa-exclamation";
      } else if (normalizedVariant === "info") {
        icon.className = "fas fa-info";
      } else {
        icon.className = "fas fa-times";
      }
    }

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
      okBtn.classList.remove(
        "barcie-btn-danger",
        "barcie-btn-warning",
        "barcie-btn-primary",
      );
      if (normalizedVariant === "warning") {
        okBtn.classList.add("barcie-btn-warning");
      } else if (normalizedVariant === "info") {
        okBtn.classList.add("barcie-btn-primary");
      } else {
        okBtn.classList.add("barcie-btn-danger");
      }
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

  /* ─── Popup-based Notifications (replaces upper-right toasts) ─── */
  function normalizeNoticeType(type) {
    var normalized = String(type || "info").toLowerCase();
    if (normalized === "danger") normalized = "error";
    if (!/^(success|error|warning|info)$/.test(normalized)) {
      normalized = "info";
    }
    return normalized;
  }

  function removeLegacyToastHost() {
    var host = document.getElementById("barcieToastList");
    if (host && host.parentNode) {
      host.parentNode.removeChild(host);
    }
  }

  function ensureNoticeHost() {
    ensureStyles();
    var host = document.getElementById("barcieNoticeHost");
    if (!host) {
      host = document.createElement("div");
      host.id = "barcieNoticeHost";
      host.className = "barcie-notice-host";
      document.body.appendChild(host);
    }
    return host;
  }

  function getNoticeMeta(type) {
    var normalized = normalizeNoticeType(type);
    var map = {
      success: { icon: "fa-check", title: "Success" },
      error: { icon: "fa-times", title: "Error" },
      warning: { icon: "fa-exclamation", title: "Warning" },
      info: { icon: "fa-info", title: "Notification" },
    };
    return map[normalized] || map.info;
  }

  function showTransientNotice(message, type, duration, title) {
    var normalized = normalizeNoticeType(type);
    var meta = getNoticeMeta(normalized);
    var displayTitle = title || meta.title;
    var host = ensureNoticeHost();
    var notice = document.createElement("div");
    notice.className = "barcie-notice barcie-notice-" + normalized;
    notice.setAttribute("role", "status");
    notice.setAttribute("aria-live", "polite");

    notice.innerHTML =
      '<div class="barcie-notice-icon"><i class="fas ' +
      meta.icon +
      '"></i></div>' +
      '<div class="barcie-notice-body">' +
      '<p class="barcie-notice-title">' +
      displayTitle +
      "</p>" +
      '<p class="barcie-notice-message">' +
      String(message || "Notification") +
      "</p>" +
      "</div>" +
      '<button type="button" class="barcie-notice-close" aria-label="Close">&times;</button>';

    host.appendChild(notice);

    requestAnimationFrame(function () {
      notice.classList.add("barcie-notice-show");
    });

    var done = false;
    var close = function () {
      if (done) return;
      done = true;
      notice.classList.remove("barcie-notice-show");
      notice.classList.add("barcie-notice-hide");
      setTimeout(function () {
        if (notice.parentNode) {
          notice.parentNode.removeChild(notice);
        }
      }, 220);
    };

    var closeBtn = notice.querySelector(".barcie-notice-close");
    if (closeBtn) closeBtn.onclick = close;

    var autoCloseMs = typeof duration === "number" ? duration : 3000;
    if (autoCloseMs > 0) {
      setTimeout(close, autoCloseMs);
    }

    return { remove: close, close: close };
  }

  function showPopupNotice(message, type, duration) {
    removeLegacyToastHost();
    return showTransientNotice(message, type, duration);
  }

  window.showToast = function (message, type, duration) {
    return showPopupNotice(message, type, duration);
  };

  window.toast = window.showToast;

  window.showAdminAlert = function (typeOrMessage, messageOrType, duration) {
    var knownTypes = /^(success|danger|error|warning|info)$/i;
    var type = "info";
    var message = "";

    if (
      typeof typeOrMessage === "string" &&
      knownTypes.test(typeOrMessage) &&
      typeof messageOrType === "string"
    ) {
      type = typeOrMessage;
      message = messageOrType;
    } else {
      message = String(typeOrMessage || "");
      if (typeof messageOrType === "string" && knownTypes.test(messageOrType)) {
        type = messageOrType;
      }
    }

    return window.showToast(message, type, duration || 3000);
  };

  var originalAlert = window.alert;
  window.alert = function (message) {
    try {
      showTransientNotice(String(message || "Notice"), "info", 3000, "Notice");
    } catch (e) {
      originalAlert(message);
    }
  };
})();
