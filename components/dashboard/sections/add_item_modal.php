<!-- Add Item Modal -->
<style>
  /* Unique styles for Add Item Modal only */
  #addItemModal .modal-dialog {
    max-width: 800px;
    margin: 1.75rem auto;
  }
  
  #addItemModal .modal-content {
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    border-radius: 10px;
  }
  
  #addItemModal .modal-header {
    flex-shrink: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
  
  #addItemModal .modal-header .btn-close {
    filter: brightness(0) invert(1);
  }
  
  #addItemModal .modal-body {
    flex: 1 1 auto;
    max-height: calc(85vh - 140px);
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.5rem;
  }
  
  #addItemModal .modal-footer {
    flex-shrink: 0;
    border-top: 1px solid #dee2e6;
  }
  
  /* Custom scrollbar for Add Item Modal */
  #addItemModal .modal-body::-webkit-scrollbar {
    width: 8px;
  }
  
  #addItemModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }
  
  #addItemModal .modal-body::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 4px;
  }
  
  #addItemModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
  }
  
  /* Ensure modal is centered and accessible */
  #addItemModal.modal {
    overflow-y: auto;
  }
  
  /* Responsive adjustments */
  @media (max-height: 700px) {
    #addItemModal .modal-content {
      max-height: 95vh;
    }
    
    #addItemModal .modal-body {
      max-height: calc(95vh - 140px);
    }
  }

  /* Ensure modal and backdrop sit above other layout elements */
  #addItemModal {
    z-index: 20000 !important;
    position: fixed !important;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }
  .modal-backdrop {
    z-index: 19990 !important;
  }
</style>

<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-plus me-2"></i>Add New Room / Facility / Amenities
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="add_item" value="1">

          <!-- Add-ons repeater -->
          <div class="col-12 mb-3">
            <label class="form-label">Add-ons (optional)</label>
            <div id="addonsRepeater" class="mb-2">
              <!-- Repeater rows inserted here -->
            </div>
            <div>
              <button type="button" id="addAddonBtn" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-plus me-1"></i>Add Add-on
              </button>
              <div class="form-text">Define add-ons specific to this room/facility (e.g. Breakfast, Extra Bed). These will be offered to guests during booking.</div>
            </div>
            <input type="hidden" name="addons_json" id="addons_json" value="">
          </div>

          <div class="row">
            <div class="col-12 mb-3">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Type <span class="text-danger">*</span></label>
              <select name="item_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="room">Room</option>
                <option value="facility">Facility</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Room Number</label>
              <input type="text" class="form-control" name="room_number" placeholder="Optional">
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3" placeholder="Brief description of the room or facility"></textarea>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Capacity <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="capacity" min="1" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="price" min="0" step="1" required>
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Image</label>
              <input type="file" class="form-control" name="image" accept="image/*">
              <div class="form-text">Optional: Upload an image for this room or facility</div>
            </div>
          </div>
        </div>

            <script>
              // Add-ons repeater script
              (function(){
                function createAddonRow(addon) {
                  addon = addon || {label:'', price:'', pricing:'per_event'};
                  const row = document.createElement('div');
                  row.className = 'd-flex gap-2 align-items-center mb-2 addon-row';
                  row.innerHTML = `
                    <input type="text" class="form-control form-control-sm addon-label" placeholder="Add-on name (e.g. Breakfast)" value="${addon.label}">
                    <input type="number" min="0" step="1" class="form-control form-control-sm addon-price" placeholder="Price" value="${addon.price}">
                    <select class="form-select form-select-sm addon-pricing">
                      <option value="per_person" ${addon.pricing==='per_person'?'selected':''}>Per Person</option>
                      <option value="per_night" ${addon.pricing==='per_night'?'selected':''}>Per Night</option>
                      <option value="per_event" ${addon.pricing==='per_event'?'selected':''}>Per Event</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-danger remove-addon-btn"><i class="fas fa-trash"></i></button>
                  `;
                  row.querySelector('.remove-addon-btn').addEventListener('click', () => { row.remove(); updateHidden(); });
                  return row;
                }

                function updateHidden(){
                  const rows = document.querySelectorAll('#addonsRepeater .addon-row');
                  const addons = [];
                  rows.forEach(r => {
                    const label = r.querySelector('.addon-label').value.trim();
                    const price = r.querySelector('.addon-price').value.trim();
                    const pricing = r.querySelector('.addon-pricing').value;
                    if (label !== '') addons.push({label, price: Number(price||0), pricing});
                  });
                  document.getElementById('addons_json').value = JSON.stringify(addons);
                }

                document.getElementById('addAddonBtn').addEventListener('click', function(){
                  const r = createAddonRow();
                  document.getElementById('addonsRepeater').appendChild(r);
                  r.querySelector('.addon-label').focus();
                  r.addEventListener('input', updateHidden);
                  updateHidden();
                });

                // Ensure hidden input is updated before submit
                var form = document.querySelector('#addItemModal form');
                if (form) {
                  form.addEventListener('submit', function(){ updateHidden(); });
                }

                // If modal has data-addons attribute (editing), prefill
                document.addEventListener('DOMContentLoaded', function(){
                  const pre = document.getElementById('addonsRepeater');
                  if (!pre) return;
                  // nothing prefilled by default; admin can add
                });
              })();

              // Move modal to document.body to avoid ancestor clipping/stacking issues
              (function () {
                function moveModal() {
                  var modal = document.getElementById('addItemModal');
                  if (!modal) return;
                  if (modal.parentNode !== document.body) {
                    document.body.appendChild(modal);
                  }
                }

                if (document.readyState === 'loading') {
                  document.addEventListener('DOMContentLoaded', moveModal);
                } else {
                  moveModal();
                }

                // In case the modal is re-inserted later, keep observing and move if needed
                var observer = new MutationObserver(function() {
                  moveModal();
                });
                observer.observe(document.documentElement, { childList: true, subtree: true });
              })();
            </script>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Add Item
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
