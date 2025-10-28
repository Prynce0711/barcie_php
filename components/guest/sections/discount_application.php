<div class="card mb-3" id="discountApplicationCard">
  <div class="card-header bg-warning text-dark">
    <strong><i class="fas fa-percent me-2"></i>Apply for Discount</strong>
  </div>
  <div class="card-body">
    <div class="mb-3">
      <label for="discount_type" class="form-label">Discount Type</label>
      <select name="discount_type" id="discount_type" class="form-select">
        <option value="">No Discount</option>
        <option value="pwd_senior">PWD / Senior Citizen (20%)</option>
        <option value="lcuppersonnel">LCUP Personnel (10%)</option>
        <option value="lcupstudent">LCUP Student/Alumni (7%)</option>
      </select>
    </div>
    <div class="mb-3" id="discount_proof_section" style="display:none;">
      <label for="discount_proof" class="form-label">Upload Valid ID/Proof <span class="text-danger">*</span></label>
      <input type="file" name="discount_proof" id="discount_proof" class="form-control" accept="image/*,application/pdf">
      <small class="form-text text-muted">Accepted: ID, certificate, or other proof (image or PDF)</small>
    </div>
    <div class="mb-3" id="discount_details_section" style="display:none;">
      <label for="discount_details" class="form-label">Discount Details</label>
      <input type="text" name="discount_details" id="discount_details" class="form-control" placeholder="ID number, personnel/student number, etc.">
    </div>
    <div class="alert alert-info mb-0" id="discount_info_text" style="display:none;"></div>
  </div>
</div>

<script>
  // Discount card behaviour: show proof/details when a discount type selected
  (function(){
    function onDiscountChange() {
      const sel = document.getElementById('discount_type');
      const proof = document.getElementById('discount_proof_section');
      const details = document.getElementById('discount_details_section');
      const info = document.getElementById('discount_info_text');
      if (!sel || !proof || !details || !info) return;

      const v = sel.value;
      if (!v) {
        proof.style.display = 'none';
        details.style.display = 'none';
        info.style.display = 'none';
        info.textContent = '';
        return;
      }

      // Show sections
      proof.style.display = 'block';
      details.style.display = 'block';
      info.style.display = 'block';

      // Helpful messages per type
      let msg = '';
      if (v === 'pwd_senior') msg = 'Upload a government-issued ID showing PWD/senior status. Discount is 20%.';
      else if (v === 'lcuppersonnel') msg = 'Upload your LCUP personnel ID or certificate. Discount is 10%.';
      else if (v === 'lcupstudent') msg = 'Upload your student ID or alumni certificate. Discount is 7%.';
      else msg = 'Please upload proof for the selected discount.';

      info.textContent = msg;
    }

    document.addEventListener('DOMContentLoaded', function(){
      const sel = document.getElementById('discount_type');
      if (!sel) return;
      sel.addEventListener('change', onDiscountChange);
      // Initialize visibility based on initial value
      onDiscountChange();
    });
  })();
</script>
