/**
 * Unified Barcie Table Pagination
 * Works automatically with any table rendered via Table.php
 *
 * API per scope:  window.BarcieTable[scope]
 *   .refresh()              – re-paginate from page 1 (call after filter changes)
 *   .refreshKeepPage()      – re-paginate keeping current page
 *   .goToPage(n)            – jump to page n
 *   .getPage()              – current page number
 *   .getPageSize()          – items per page
 *   .getVisibleCount()      – number of rows matching current filters
 *   .setFilter(fn)          – register a filter function: fn(row) → boolean
 *   .setPageSize(n)         – change page size dynamically
 */
(function () {
  "use strict";

  window.BarcieTable = window.BarcieTable || {};

  function initWrapper(wrapper) {
    var scope = wrapper.getAttribute("data-scope") || "default";
    if (window.BarcieTable[scope] && window.BarcieTable[scope]._initialized)
      return;

    var table = wrapper.querySelector("table");
    if (!table) return;

    var paginationEl = wrapper.querySelector("[data-table-pagination]");
    if (!paginationEl) return;

    var pageSize =
      parseInt(paginationEl.getAttribute("data-page-size"), 10) || 10;
    var prevBtn = paginationEl.querySelector("[data-pagination-prev]");
    var nextBtn = paginationEl.querySelector("[data-pagination-next]");
    var currentEl = paginationEl.querySelector("[data-pagination-current]");
    var infoEl = paginationEl.querySelector("[data-pagination-info]");

    var currentPage = 1;
    var filterFn = null; // optional: fn(row) → boolean

    /** Get all data rows from tbody (excludes empty-state placeholder) */
    function getAllRows() {
      var tbody = table.querySelector("tbody");
      if (!tbody) return [];
      var all = tbody.querySelectorAll("tr");
      var rows = [];
      for (var i = 0; i < all.length; i++) {
        if (!all[i].classList.contains("barcie-table-empty")) rows.push(all[i]);
      }
      return rows;
    }

    /** Get rows that pass the registered filter (or all rows if no filter) */
    function getMatchingRows() {
      var all = getAllRows();
      if (typeof filterFn !== "function") return all;
      var result = [];
      for (var i = 0; i < all.length; i++) {
        if (filterFn(all[i])) result.push(all[i]);
      }
      return result;
    }

    function render() {
      var allRows = getAllRows();
      var matchingRows = getMatchingRows();
      var totalItems = matchingRows.length;
      var totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
      if (currentPage > totalPages) currentPage = totalPages;
      if (currentPage < 1) currentPage = 1;

      var start = (currentPage - 1) * pageSize;
      var end = start + pageSize;

      // Build a set of rows to show on this page
      var pageSlice = matchingRows.slice(start, end);
      var showSet = new Set(pageSlice);

      // Hide all rows, then show only the ones on the current page
      for (var i = 0; i < allRows.length; i++) {
        var row = allRows[i];
        if (showSet.has(row)) {
          row.removeAttribute("data-hidden-by-page");
          row.style.display = "";
          row.style.opacity = "";
        } else {
          row.setAttribute("data-hidden-by-page", "true");
          row.style.display = "none";
        }
      }

      // Update controls
      if (prevBtn) prevBtn.disabled = currentPage <= 1;
      if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
      if (currentEl)
        currentEl.textContent = "Page " + currentPage + " / " + totalPages;

      var showStart = totalItems === 0 ? 0 : start + 1;
      var showEnd = Math.min(end, totalItems);
      if (infoEl) {
        infoEl.textContent =
          totalItems === 0
            ? "No items"
            : "Showing " + showStart + "\u2013" + showEnd + " of " + totalItems;
      }

      // dispatch event for other components
      document.dispatchEvent(
        new CustomEvent("page-changed", {
          detail: {
            scope: scope,
            page: currentPage,
            pageSize: pageSize,
            totalPages: totalPages,
            totalItems: totalItems,
          },
        }),
      );
    }

    function goToPage(p) {
      currentPage = p;
      render();
    }

    function refresh() {
      currentPage = 1;
      render();
    }

    function refreshKeepPage() {
      render();
    }

    // Button handlers
    if (prevBtn)
      prevBtn.addEventListener("click", function () {
        if (currentPage > 1) {
          currentPage--;
          render();
        }
      });
    if (nextBtn)
      nextBtn.addEventListener("click", function () {
        var matchingRows = getMatchingRows();
        var tp = Math.max(1, Math.ceil(matchingRows.length / pageSize));
        if (currentPage < tp) {
          currentPage++;
          render();
        }
      });

    // Listen for filter resets
    document.addEventListener("filters-reset", function (e) {
      if (e.detail && e.detail.scope && e.detail.scope !== scope) return;
      refresh();
    });

    // Also expose on the old Pagination namespace for backward compat
    window.Pagination = window.Pagination || {};
    window.Pagination[scope] = {
      update: function (total, page) {
        currentPage = page || 1;
        render();
      },
      getPage: function () {
        return currentPage;
      },
      getPageSize: function () {
        return pageSize;
      },
      reset: function () {
        refresh();
      },
    };

    // Public API
    var api = {
      _initialized: true,
      refresh: refresh,
      refreshKeepPage: refreshKeepPage,
      goToPage: goToPage,
      getPage: function () {
        return currentPage;
      },
      getPageSize: function () {
        return pageSize;
      },
      setPageSize: function (n) {
        pageSize = Math.max(1, parseInt(n, 10) || 10);
        refresh();
      },
      getVisibleCount: function () {
        return getMatchingRows().length;
      },
      setFilter: function (fn) {
        filterFn = typeof fn === "function" ? fn : null;
        refresh();
      },
    };
    window.BarcieTable[scope] = api;

    // Initial render
    render();
  }

  // Initialize all wrappers present in DOM
  document.querySelectorAll("[data-table-wrapper]").forEach(initWrapper);

  // Re-init when DOM mutates (for tables loaded async)
  var _initTimer = null;
  function debouncedInit() {
    if (_initTimer) clearTimeout(_initTimer);
    _initTimer = setTimeout(function () {
      document.querySelectorAll("[data-table-wrapper]").forEach(initWrapper);
    }, 300);
  }

  if (typeof MutationObserver !== "undefined") {
    var observer = new MutationObserver(debouncedInit);
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();
