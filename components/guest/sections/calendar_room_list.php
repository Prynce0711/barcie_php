<!-- Room & Facility List Section -->
<div id="roomFacilityList" class="room-facility-list" style="display:none;">
  <div class="row" id="roomListContainer">
    <!-- Rooms will be populated here by JavaScript -->
    <div class="col-12 text-center py-5">
      <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
      <p class="text-muted">Loading rooms and facilities...</p>
    </div>
  </div>
</div>

<script>
(function() {
  // Render rooms and facilities in list format
  function renderRoomFacilityList() {
    const container = document.getElementById('roomListContainer');
    if (!container) return;

    function choosePreviewImage(item) {
      const defaultImg = '/assets/images/imageBg/barcie_logo.jpg';

      function normalize(path) {
        if (!path) return null;
        if (typeof path !== 'string') return null;
        path = path.trim();
        if (!path) return null;
        if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) return path;
        return '/' + path.replace(/^\/+/, '');
      }

      try {
        // Try images field which may be JSON string or array
        if (item.images) {
          let imgs = item.images;
          if (typeof imgs === 'string') {
            try { imgs = JSON.parse(imgs); } catch (e) { imgs = [item.images]; }
          }
          if (Array.isArray(imgs) && imgs.length) {
            let first = imgs[0];
            // handle objects in array: { url: '...' } or similar
            if (typeof first === 'object' && first !== null) {
              first = first.url || first.src || first.path || first.image || null;
            }
            const n = normalize(first);
            if (n) return n;
          }
        }

        // Fallback single image fields
        const candidates = [item.image, item.preview, item.thumbnail];
        for (const c of candidates) {
          const n = normalize(c);
          if (n) return n;
        }
      } catch (e) { /* ignore */ }

      return defaultImg;
    }

    // Wait for window.allItems
    function waitForItems(timeout = 5000, interval = 200) {
      return new Promise((resolve) => {
        const start = Date.now();
        (function poll() {
          const items = window.allItems;
          if (items && Array.isArray(items) && items.length > 0) return resolve(items);
          if (Date.now() - start > timeout) return resolve(items || []);
          setTimeout(poll, interval);
        })();
      });
    }

    waitForItems().then(items => {
      if (!items || items.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="fas fa-exclamation-circle fa-2x text-muted mb-3"></i>
            <p class="text-muted">No rooms or facilities available.</p>
          </div>
        `;
        return;
      }

      container.innerHTML = '';
      
      items.forEach(item => {
        const preview = choosePreviewImage(item);
        const col = document.createElement('div');
        col.className = 'col-12 mb-3';
        
        col.innerHTML = `
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-auto">
                  <img src="${preview}" alt="${item.name}" 
                       style="width:120px;height:90px;object-fit:cover;border-radius:8px;" 
                       onerror="this.src='/assets/images/imageBg/barcie_logo.jpg';">
                </div>
                <div class="col">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h5 class="mb-1 fw-bold">${item.name.toUpperCase()}</h5>
                      <span class="badge ${item.item_type === 'room' ? 'bg-primary' : 'bg-info'} mb-2">
                        ${item.item_type === 'room' ? 'ROOM' : 'FACILITY'}
                      </span>
                      <p class="text-muted mb-1">
                        ${item.item_type === 'room' ? (item.room_number ? 'Room #' + item.room_number + ' · ' : '') : 'Facility · '}
                        ${item.capacity || 0} ${item.item_type === 'room' ? 'guests' : 'people'}
                      </p>
                    </div>
                    <div class="text-end">
                      <h4 class="mb-0 text-primary">₱${parseInt(item.price||0).toLocaleString()}</h4>
                      <small class="text-muted">${item.item_type === 'room' ? '/night' : '/day'}</small>
                    </div>
                  </div>
                  <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm me-2 view-calendar-btn" data-item-id="${item.id}">
                      <i class="fas fa-calendar-alt me-1"></i>View Calendar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        
        container.appendChild(col);
      });

      // Attach View Calendar button handlers (open room-specific modal)
      container.querySelectorAll('.view-calendar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const itemId = this.dataset.itemId;
          const itemName = this.dataset.itemName || '';
          if (typeof window.openRoomCalendarModal === 'function') {
            try { window.openRoomCalendarModal(itemId, itemName); } catch (e) { console.error(e); }
            return;
          }

          // Fallback: switch to main calendar view and navigate
          if (typeof switchToCalendarView === 'function') switchToCalendarView();
          if (window.guestCalendar) {
            try {
              const events = window.guestCalendar.getEvents ? window.guestCalendar.getEvents() : [];
              const match = events.find(ev => {
                const p = ev.extendedProps || {};
                return p.item_id == itemId || p.room_id == itemId || ev.id == itemId;
              });
              if (match) {
                try { window.guestCalendar.gotoDate(match.start || match.startStr); } catch(e){}
              }
            } catch(e){}
          }
        });
      });
    });
  }

  // Expose function globally
  window.renderRoomFacilityList = renderRoomFacilityList;
})();
</script>
