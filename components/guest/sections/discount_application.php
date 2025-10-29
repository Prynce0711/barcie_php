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
  <!-- Inline alert placeholder immediately after the asterisk -->
  <span id="discount_proof_alert" style="display:inline-block; margin-left:6px;"></span>
      <input type="file" name="discount_proof" id="discount_proof" class="form-control" accept="image/*,application/pdf">
      <small class="form-text text-muted">Accepted: ID, certificate, or other proof (image or PDF)</small>

      <!-- Preview, loading and validation status -->
      <div id="discount_proof_preview" style="margin-top:10px;display:none;">
        <div id="discount_proof_loading" style="display:none;">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <small class="text-muted">Validating file...</small>
        </div>
        <div id="discount_proof_status" style="margin-top:6px;"></div>
        <div id="discount_proof_thumb" style="margin-top:8px;max-width:160px;">
        </div>
        <div id="discount_upload_controls" style="margin-top:8px;display:none;">
          <div class="progress" id="discount_upload_progress" style="height:12px;display:none;">
            <div class="progress-bar" role="progressbar" style="width:0%" aria-valuemin="0" aria-valuemax="100">0%</div>
          </div>
          <button type="button" id="discount_upload_cancel" class="btn btn-sm btn-outline-danger" style="display:none;margin-top:6px;">Cancel Upload</button>
        </div>
      </div>
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
      const proofInput = document.getElementById('discount_proof');

      if (!sel || !proof || !details || !info || !proofInput) return;

      const v = sel.value;
      if (!v) {
        proof.style.display = 'none';
        details.style.display = 'none';
        info.style.display = 'none';
        info.textContent = '';
        proofInput.required = false; // Make proof not required when no discount is selected
        return;
      }

      // Show sections
      proof.style.display = 'block';
      details.style.display = 'block';
      info.style.display = 'block';
      proofInput.required = true; // Make proof required when a discount is selected

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
<script>
  (function(){
    // Client-side quick validation and preview for discount proof upload
    const proofInput = document.getElementById('discount_proof');
    const proofPreview = document.getElementById('discount_proof_preview');
    const proofLoading = document.getElementById('discount_proof_loading');
    const proofStatus = document.getElementById('discount_proof_status');
    const proofThumb = document.getElementById('discount_proof_thumb');
    const discountTypeSel = document.getElementById('discount_type');

    if (!proofInput) return;

    // keywords for LCUP and Senior
    const LCUP_KEYWORDS = ['la consolacion', 'lcup', 'la consolacion university', 'la consolacion university philippines', 'consolacion'];
    const SENIOR_KEYWORDS = ['senior', 'senior citizen', 'seniorcitizen', 'senior_id', 'senior-id', 'seniorid'];

    function normalize(s){ return (s||'').toLowerCase().replace(/[^a-z0-9 ]+/g,' '); }

    function checkFileForType(file, discountType){
      // Quick filename-based heuristic. Returns {ok, reason}
      const name = normalize(file.name || '');
      if (!discountType) return { ok: true, reason: 'No discount selected' };

      if (discountType === 'lcuppersonnel' || discountType === 'lcupstudent'){
        for (const k of LCUP_KEYWORDS) if (name.indexOf(k) !== -1) return { ok: true, reason: 'Detected LCUP in filename' };
        return { ok: false, reason: 'Filename did not contain LCUP keywords. Please upload your LCUP ID or certificate.' };
      }

      if (discountType === 'pwd_senior'){
        for (const k of SENIOR_KEYWORDS) if (name.indexOf(k) !== -1) return { ok: true, reason: 'Detected senior keyword in filename' };
        // allow if filename mentions lcup as well (some students/employees may be senior)
        for (const k of LCUP_KEYWORDS) if (name.indexOf(k) !== -1) return { ok: true, reason: 'Detected LCUP in filename (accepted for senior/PWD)' };
        return { ok: false, reason: 'Filename did not contain senior/PWD keywords. Please upload a government-issued senior ID or document showing senior status.' };
      }

      return { ok: true, reason: 'Unknown discount type - accepted by default' };
    }

    function showPreviewForFile(file, result){
      proofPreview.style.display = 'block';
      proofStatus.innerHTML = '';
      proofThumb.innerHTML = '';

      // set status
      const statusEl = document.createElement('div');
      statusEl.className = result.ok ? 'text-success' : 'text-danger';
      statusEl.textContent = result.reason;
      proofStatus.appendChild(statusEl);

      // thumbnail for images
      if (file.type.startsWith('image/')){
        const fr = new FileReader();
        fr.onload = function(e){
          proofThumb.innerHTML = `<img src="${e.target.result}" style="max-width:160px;border-radius:6px;object-fit:cover;">`;
        };
        fr.readAsDataURL(file);
      } else {
        // PDF or other: show icon + name
        proofThumb.innerHTML = `<div class="p-2 border" style="border-radius:6px;">
          <i class="far fa-file-pdf fa-2x"></i>
          <div style="font-size:0.9rem;">${escapeHtml(file.name)}</div>
        </div>`;
      }
    }

    function escapeHtml(s){ return String(s||'').replace(/[&<>'\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c])); }

    proofInput.addEventListener('change', function(){
      const f = this.files && this.files[0];
      if (!f) {
        proofPreview.style.display = 'none';
        proofInput.dataset.validProof = '';
        return;
      }

      proofLoading.style.display = 'inline-block';
      proofStatus.innerHTML = '';
      proofThumb.innerHTML = '';
      proofInput.dataset.validProof = '0';

      // Quick async validation (filename heuristics)
      setTimeout(() => {
        const discountType = discountTypeSel ? discountTypeSel.value : '';
        const res = checkFileForType(f, discountType);
        showPreviewForFile(f, res);
        proofLoading.style.display = 'none';
        proofInput.dataset.validProof = res.ok ? '1' : '0';
        proofInput.dataset.validReason = res.reason;
      }, 250);
    });

    // When discount type changes, re-run validation against selected file
    if (discountTypeSel){
      discountTypeSel.addEventListener('change', function(){
        const f = proofInput.files && proofInput.files[0];
        if (!f) return;
        proofLoading.style.display = 'inline-block';
        setTimeout(() => {
          const res = checkFileForType(f, discountTypeSel.value);
          showPreviewForFile(f, res);
          proofLoading.style.display = 'none';
          proofInput.dataset.validProof = res.ok ? '1' : '0';
          proofInput.dataset.validReason = res.reason;
        }, 150);
      });
    }
  })();
</script>
