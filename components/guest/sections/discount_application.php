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
      <input type="hidden" name="discount_proof_cropped" id="discount_proof_cropped">
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

<!-- Image Crop/Edit Modal -->
  <?php include __DIR__ . '/../modals/image_crop_modal.php'; ?>

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
    // Client-side ID scanning using Canvas API - works entirely in browser
    const proofInput = document.getElementById('discount_proof');
    const proofPreview = document.getElementById('discount_proof_preview');
    const proofLoading = document.getElementById('discount_proof_loading');
    const proofStatus = document.getElementById('discount_proof_status');
    const proofThumb = document.getElementById('discount_proof_thumb');
    const discountTypeSel = document.getElementById('discount_type');

    if (!proofInput) return;

    function escapeHtml(s){ return String(s||'').replace(/[&<>'\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;'}[c])); }

    /**
     * Extract text from image using Tesseract.js OCR
     */
    async function extractTextFromImage(imageDataUrl) {
      // Check if Tesseract is available (we'll load it dynamically)
      if (typeof Tesseract === 'undefined') {
        try {
          // Load Tesseract.js from CDN
          await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
          });
        } catch (error) {
          console.warn('Failed to load Tesseract.js:', error);
          return '';
        }
      }

      try {
        const worker = await Tesseract.createWorker('eng');
        const result = await worker.recognize(imageDataUrl);
        await worker.terminate();
        return result.data.text.toLowerCase();
      } catch (error) {
        console.warn('OCR failed:', error);
        return '';
      }
    }

    /**
     * Check if text contains LCUP keywords (strict matching)
     */
    function checkTextForLCUP(text) {
      // Primary keywords - must find at least one of these
      const primaryKeywords = [
        'la consolacion university philippines',
        'la consolacion university',
        'consolacion university philippines',
        'consolacion university'
      ];

      // Secondary keywords - only valid if combined with primary
      const secondaryKeywords = [
        'college of information technology',
        'student',
        'employee',
        'personnel'
      ];

      const foundPrimary = [];
      const foundSecondary = [];

      for (const keyword of primaryKeywords) {
        if (text.includes(keyword)) {
          foundPrimary.push(keyword);
        }
      }

      for (const keyword of secondaryKeywords) {
        if (text.includes(keyword)) {
          foundSecondary.push(keyword);
        }
      }

      // Must have at least one primary keyword to be valid
      const isValid = foundPrimary.length > 0;
      const confidence = isValid ? Math.min(100, (foundPrimary.length * 50) + (foundSecondary.length * 15)) : 0;

      return {
        found: isValid,
        keywords: foundPrimary.concat(foundSecondary),
        confidence: confidence
      };
    }

    /**
     * Scan ID image using Canvas API to detect colors and patterns
     */
    async function scanIDImage(file, discountType) {
      if (!discountType || !file.type.startsWith('image/')) {
        return { detected: false, confidence: 30, features: ['File uploaded'] };
      }

      return new Promise(async (resolve) => {
        const img = new Image();
        const reader = new FileReader();

        reader.onload = async function(e) {
          const imageDataUrl = e.target.result;
          
          img.onload = async function() {
            try {
              const canvas = document.createElement('canvas');
              const ctx = canvas.getContext('2d');
              
              // Resize for faster processing
              const maxWidth = 800;
              const scale = Math.min(1, maxWidth / img.width);
              canvas.width = img.width * scale;
              canvas.height = img.height * scale;
              
              ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
              const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
              const pixels = imageData.data;
              
              // Analyze different regions
              const top = analyzeRegion(pixels, canvas.width, canvas.height, 0, 0.33);
              const middle = analyzeRegion(pixels, canvas.width, canvas.height, 0.33, 0.67);
              const bottom = analyzeRegion(pixels, canvas.width, canvas.height, 0.67, 1);
              
              const aspectRatio = canvas.width / canvas.height;
              const isLandscape = aspectRatio > 1.3 && aspectRatio < 2;
              
              const result = { detected: false, confidence: 0, features: [], ocrText: '' };
              
              // For LCUP IDs, require BOTH color AND text detection
              if (discountType === 'lcuppersonnel' || discountType === 'lcupstudent') {
                // Run OCR in parallel with color analysis
                const ocrPromise = extractTextFromImage(imageDataUrl);
                detectLCUPID(top, middle, bottom, isLandscape, result);
                
                const colorScore = result.confidence;
                const hasColorMatch = colorScore > 25; // Must have some blue color
                
                // Wait for OCR result
                const extractedText = await ocrPromise;
                let hasTextMatch = false;
                
                if (extractedText) {
                  const textCheck = checkTextForLCUP(extractedText);
                  if (textCheck.found) {
                    hasTextMatch = true;
                    result.features.unshift('✓ Text: "' + textCheck.keywords[0] + '" detected');
                    result.ocrText = extractedText;
                  }
                }
                
                // STRICT: Require BOTH color pattern AND university text
                if (hasColorMatch && hasTextMatch) {
                  result.detected = true;
                  result.confidence = Math.min(100, colorScore + 50); // Bonus for text match
                } else if (hasColorMatch && !hasTextMatch) {
                  result.detected = false;
                  result.confidence = Math.min(25, colorScore);
                  result.features.push('⚠️ Missing "La Consolacion University" text');
                } else if (!hasColorMatch && hasTextMatch) {
                  result.detected = false;
                  result.confidence = 20;
                  result.features.push('⚠️ Missing blue ID color pattern');
                } else {
                  result.detected = false;
                  result.confidence = 10;
                  result.features.push('⚠️ Not an LCUP ID');
                }
              } else if (discountType === 'pwd_senior') {
                detectSeniorPWDID(top, middle, bottom, isLandscape, result);
              }
              
              if (isLandscape && result.detected) {
                result.features.push('Standard ID format');
                result.confidence = Math.min(100, result.confidence + 10);
              }
              
              resolve(result);
            } catch (error) {
              resolve({ detected: false, confidence: 30, features: ['Upload complete'], error: error.message });
            }
          };
          
          img.onerror = () => resolve({ detected: false, confidence: 30, features: ['Upload complete'] });
          img.src = imageDataUrl;
        };
        
        reader.onerror = () => resolve({ detected: false, confidence: 30, features: ['Upload complete'] });
        reader.readAsDataURL(file);
      });
    }

    function analyzeRegion(pixels, width, height, startY, endY) {
      const colors = { blue: 0, orange: 0, red: 0, yellow: 0, white: 0, samples: 0 };
      const startRow = Math.floor(height * startY);
      const endRow = Math.floor(height * endY);
      
      for (let y = startRow; y < endRow; y += 10) {
        for (let x = 0; x < width; x += 10) {
          const i = (y * width + x) * 4;
          const r = pixels[i], g = pixels[i + 1], b = pixels[i + 2];
          colors.samples++;
          
          if (b > 100 && b > r && b > g && (r < 100 || g < 100)) colors.blue++;
          if (r > 150 && g > 80 && g < 150 && b < 100) colors.orange++;
          if (r > 150 && g < 100 && b < 100) colors.red++;
          if (r > 180 && g > 150 && b < 100) colors.yellow++;
          if (r > 200 && g > 200 && b > 200) colors.white++;
        }
      }
      
      if (colors.samples > 0) {
        colors.bluePercent = (colors.blue / colors.samples) * 100;
        colors.orangePercent = (colors.orange / colors.samples) * 100;
        colors.redPercent = (colors.red / colors.samples) * 100;
        colors.yellowPercent = (colors.yellow / colors.samples) * 100;
        colors.whitePercent = (colors.white / colors.samples) * 100;
      }
      
      return colors;
    }

    function detectLCUPID(top, middle, bottom, isLandscape, result) {
      let colorScore = 0;
      
      // Check for blue header (lowered threshold for real photos)
      if (top.bluePercent > 8 || middle.bluePercent > 8) {
        result.features.push('✓ Blue ID background');
        const blueConfidence = Math.min(30, Math.max(top.bluePercent, middle.bluePercent) * 2);
        colorScore += blueConfidence;
      }
      
      // Check overall blue presence (LCUP IDs are predominantly blue)
      const totalBlue = (top.bluePercent + middle.bluePercent + bottom.bluePercent) / 3;
      if (totalBlue > 5 && colorScore === 0) {
        result.features.push('✓ Blue color pattern');
        colorScore += Math.min(20, totalBlue * 3);
      }
      
      const totalOrange = (top.orangePercent + middle.orangePercent + bottom.orangePercent) / 3;
      if (totalOrange > 3) {
        result.features.push('✓ Orange CIT accent');
        colorScore += Math.min(15, totalOrange * 2);
      }
      
      // White content area (for photo and text)
      if (middle.whitePercent > 15 || bottom.whitePercent > 15) {
        result.features.push('✓ ID content area');
        colorScore += 10;
      }
      
      // Store color score but don't set detected flag yet
      // Will require BOTH color AND text validation
      result.confidence = colorScore;
      result.detected = false; // Will be set true only if text also matches
    }

    function detectSeniorPWDID(top, middle, bottom, isLandscape, result) {
      let hasGovColors = false;
      let hasIdStructure = false;
      
      const totalYellow = (top.yellowPercent + middle.yellowPercent + bottom.yellowPercent) / 3;
      if (totalYellow > 10 || top.yellowPercent > 15) {
        result.features.push('Government ID yellow/gold (' + Math.round(totalYellow) + '%)');
        result.confidence += Math.min(40, totalYellow * 2);
        hasGovColors = true;
      }
      
      const totalRed = (top.redPercent + middle.redPercent + bottom.redPercent) / 3;
      if (totalRed > 8) {
        result.features.push('Red accent color');
        result.confidence += Math.min(30, totalRed * 2);
        hasGovColors = true;
      }
      
      if (middle.whitePercent > 30 || bottom.whitePercent > 30) {
        result.features.push('ID format detected');
        result.confidence += 15;
        hasIdStructure = true;
      }
      
      // Must have government colors to be considered valid
      result.detected = hasGovColors && (hasIdStructure || result.confidence >= 40);
    }

    function showScanResults(file, scanResult, discountType) {
      proofPreview.style.display = 'block';
      proofStatus.innerHTML = '';
      
      // Show thumbnail
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          proofThumb.innerHTML = `<img src="${e.target.result}" style="max-width:160px;border-radius:6px;object-fit:cover;">`;
        };
        reader.readAsDataURL(file);
      } else {
        proofThumb.innerHTML = `<div class="p-2 border" style="border-radius:6px;">
          <i class="far fa-file-pdf fa-2x"></i>
          <div style="font-size:0.9rem;">${escapeHtml(file.name)}</div>
        </div>`;
      }

      // Display scan results
      const statusEl = document.createElement('div');
      
      // VALIDATION: Accept if ID features are detected with minimum 30% confidence
      if (scanResult.detected && scanResult.confidence >= 30) {
        statusEl.className = scanResult.confidence >= 60 ? 'text-success fw-bold' : 'text-success';
        let msg = '';
        
        switch(discountType) {
          case 'lcuppersonnel': msg = 'LCUP Personnel ID detected'; break;
          case 'lcupstudent': msg = 'LCUP Student/Alumni ID detected'; break;
          case 'pwd_senior': msg = 'Senior/PWD ID detected'; break;
          default: msg = 'Valid ID detected';
        }
        
        statusEl.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + msg + ' (Confidence: ' + scanResult.confidence + '%)';
        proofInput.dataset.validProof = '1';
        proofInput.dataset.confidence = scanResult.confidence;
      } else {
        // REJECT: No valid ID features detected
        statusEl.className = 'text-danger fw-bold';
        let msg = '';
        
        switch(discountType) {
          case 'lcuppersonnel':
            msg = 'This does not appear to be a LCUP Personnel ID. Please upload a valid LCUP employee ID with the blue header.';
            break;
          case 'lcupstudent':
            msg = 'This does not appear to be a LCUP Student/Alumni ID. Please upload a valid LCUP student ID with the blue header.';
            break;
          case 'pwd_senior':
            msg = 'This does not appear to be a Senior/PWD ID. Please upload a valid government-issued senior citizen or PWD ID.';
            break;
          default:
            msg = 'Could not detect valid ID features. Please upload a clear photo of your ID.';
        }
        
        statusEl.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + msg;
        proofInput.dataset.validProof = '0';
        proofInput.dataset.confidence = scanResult.confidence || 0;
        
        // Show what was checked
        const hintEl = document.createElement('div');
        hintEl.className = 'text-muted small mt-2';
        hintEl.innerHTML = '<strong>Tips:</strong><br>• Ensure good lighting and clear image<br>• ID should fill most of the frame<br>• Avoid shadows and glare<br>• Take photo on a plain background';
        proofStatus.appendChild(hintEl);
      }
      
      proofStatus.appendChild(statusEl);
      
      // Show detected features
      if (scanResult.features && scanResult.features.length > 0) {
        const featuresEl = document.createElement('div');
        featuresEl.className = 'text-muted small mt-1';
        featuresEl.innerHTML = '<i class="fas fa-check me-1"></i>' + scanResult.features.join(' • ');
        proofStatus.appendChild(featuresEl);
      }
      
      proofInput.dataset.validReason = scanResult.features.join(', ') || 'ID validation failed';
    }

    let currentFile = null;
    let cropper = null;

    // Load Cropper.js library dynamically
    function loadCropperJS() {
      return new Promise((resolve, reject) => {
        if (typeof Cropper !== 'undefined') {
          resolve();
          return;
        }
        
        // Load CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css';
        document.head.appendChild(link);
        
        // Load JS
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
      });
    }

    // Open crop modal
    async function openCropModal(file) {
      try {
        await loadCropperJS();
        
        const reader = new FileReader();
        reader.onload = function(e) {
          const cropImage = document.getElementById('cropImage');
          const imgSrc = e.target.result;

          // Determine image orientation first so we can set correct aspect ratio
          const probe = new Image();
          probe.onload = function() {
            // LCUP ID is portrait (height > width) — use portrait aspect ratio
            const idLandscapeRatio = 1.586; // width/height for landscape IDs
            const idPortraitRatio = 1 / idLandscapeRatio; // width/height for portrait IDs
            const usePortrait = probe.naturalHeight > probe.naturalWidth;
            const aspectForCropper = usePortrait ? idPortraitRatio : idLandscapeRatio;

            cropImage.src = imgSrc;

            // Initialize Cropper
            if (cropper) {
              cropper.destroy();
            }

            let cropInitialZoom = 1;
            // Always enforce ID aspect ratio, but we'll set the crop box to contain the whole image
            cropper = new Cropper(cropImage, {
              aspectRatio: aspectForCropper,
              viewMode: 1, // Restrict to canvas
              dragMode: 'move',
              autoCropArea: 1,
              restore: false,
              guides: true,
              center: true,
              highlight: true,
              cropBoxMovable: true,
              cropBoxResizable: true,
              toggleDragModeOnDblclick: false,
              responsive: true,
              checkCrossOrigin: false,
              background: false,
              minContainerWidth: 700,
              minContainerHeight: 500,
              ready() {
                // Fit the image fully inside the container initially (avoid cropping portrait images)
                try {
                  const containerData = cropper.getContainerData();
                  const imgData = cropper.getImageData();

                  // Calculate a zoom level that fits the whole image inside container
                  const fitZoomX = containerData.width / (imgData.naturalWidth || imgData.width || 1);
                  const fitZoomY = containerData.height / (imgData.naturalHeight || imgData.height || 1);
                  const fitZoom = Math.min(fitZoomX, fitZoomY, 1);

                  // Apply the zoom to show the entire image (so portrait images are not cropped)
                  try { cropper.zoomTo(fitZoom); } catch (e) { /* ignore */ }

                  // Configure zoom slider around the fitZoom
                  const zr = document.getElementById('zoomRange');
                  if (zr) {
                    const min = Math.max(0.2, fitZoom * 0.5);
                    const max = Math.max(fitZoom * 3, min + 0.5);
                    zr.min = min; zr.max = max; zr.step = 0.01; zr.value = fitZoom; zr.dataset.base = fitZoom;
                  }
                  // Expand crop box to contain the displayed image while keeping the enforced aspect ratio
                  try {
                    const updatedImg = cropper.getImageData();
                    const canvasData = cropper.getCanvasData();
                    const dispW = updatedImg.width;
                    const dispH = updatedImg.height;
                    const targetAspect = aspectForCropper;

                    // Compute minimal crop box that contains the displayed image while matching aspect ratio
                    let cropW = dispW;
                    let cropH = cropW / targetAspect;
                    if (cropH < dispH) {
                      cropH = dispH;
                      cropW = cropH * targetAspect;
                    }

                    // Center crop box over the image display area
                    const left = canvasData.left + (canvasData.width - cropW) / 2;
                    const top = canvasData.top + (canvasData.height - cropH) / 2;

                    cropper.setCropBoxData({ left: Math.max(0, left), top: Math.max(0, top), width: Math.min(cropW, canvasData.width), height: Math.min(cropH, canvasData.height) });
                  } catch (e) { /* ignore */ }
                } catch (e) {
                  console.warn('Cropper fit failed', e);
                }
              }
            });            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('imageCropModal'));
            modal.show();
            try { bindModalControls(); } catch (e) { /* ignore */ }
          };
          probe.src = imgSrc;
        };
        reader.readAsDataURL(file);
      } catch (error) {
        console.error('Failed to load cropper:', error);
        alert('Failed to load image editor. Processing original image...');
        processImage(file);
      }
    }

    // Crop controls
    document.getElementById('rotateLeft')?.addEventListener('click', () => {
      if (cropper) cropper.rotate(-90);
    });
    
    document.getElementById('rotateRight')?.addEventListener('click', () => {
      if (cropper) cropper.rotate(90);
    });
    
    document.getElementById('flipHorizontal')?.addEventListener('click', () => {
      if (cropper) cropper.scaleX(-cropper.getData().scaleX || -1);
    });
    
    document.getElementById('flipVertical')?.addEventListener('click', () => {
      if (cropper) cropper.scaleY(-cropper.getData().scaleY || -1);
    });
    
    document.getElementById('zoomRange')?.addEventListener('input', function() {
      if (cropper) {
        const v = parseFloat(this.value);
        if (!isNaN(v)) {
          try { cropper.zoomTo(v); } catch(e) { /* ignore */ }
        }
      }
    });
    
    // Reset button removed; keep function available if needed elsewhere
    
    document.getElementById('applyCrop')?.addEventListener('click', async () => {
      if (!cropper) return;
      
      // Get cropped canvas
      const canvas = cropper.getCroppedCanvas({
        maxWidth: 1920,
        maxHeight: 1920,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
      });
      
      // Convert to blob
      canvas.toBlob(async (blob) => {
        const croppedFile = new File([blob], currentFile.name, {
          type: currentFile.type,
          lastModified: Date.now()
        });
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('imageCropModal')).hide();
        
        // Process cropped image
        await processImage(croppedFile);
        
        // Store cropped data URL for submission
        document.getElementById('discount_proof_cropped').value = canvas.toDataURL(currentFile.type);
      }, currentFile.type, 0.95);
    });

    // Detect current crop without closing modal
    async function detectCurrentCrop() {
      const statusEl = document.getElementById('modal_detect_status');
      statusEl.innerHTML = '<small class="text-muted">Running quick detection on current crop...</small>';
      try {
        if (!cropper) {
          statusEl.innerHTML = '<span class="text-danger">No image loaded</span>';
          return;
        }
        const canvas = cropper.getCroppedCanvas({ maxWidth: 1600, maxHeight: 1600 });
        if (!canvas) {
          statusEl.innerHTML = '<span class="text-danger">Unable to get crop</span>';
          return;
        }
        // Convert to blob and create File to reuse scanIDImage
        await new Promise((res) => setTimeout(res, 50));
        canvas.toBlob(async (blob) => {
          const tmpFile = new File([blob], (currentFile && currentFile.name) ? currentFile.name : 'crop.jpg', { type: 'image/jpeg', lastModified: Date.now() });
          const discountType = discountTypeSel ? discountTypeSel.value : '';
          const scanResult = await scanIDImage(tmpFile, discountType);
            // Show quick result
            if (scanResult.detected && scanResult.confidence >= 30) {
              statusEl.innerHTML = '<span class="text-success fw-bold">Detected: ' + (scanResult.confidence || 0) + '%</span>';
            } else {
              statusEl.innerHTML = '<span class="text-danger">Not detected (Confidence: ' + (scanResult.confidence || 0) + '%)</span>';
            }
            // Show OCR preview for user feedback
            try {
              const ocrPreview = document.getElementById('modalOcrPreview');
              if (ocrPreview) {
                ocrPreview.style.display = 'block';
                if (scanResult.ocrText && scanResult.ocrText.trim().length > 0) {
                  ocrPreview.textContent = 'OCR: ' + scanResult.ocrText.trim();
                } else {
                  ocrPreview.textContent = 'OCR: (no readable text detected)';
                }
              }
            } catch (e) { /* ignore */ }
        }, 'image/jpeg', 0.95);
      } catch (e) {
        console.error(e);
        statusEl.innerHTML = '<span class="text-danger">Detection failed</span>';
      }
    }

    // Detect Now button removed per request

    // Bind modal upload/camera controls
    function bindModalControls() {
      const modalUploadBtn = document.getElementById('modalUploadBtn');
      const modalUploadInput = document.getElementById('modalUploadInput');
      const modalCameraBtn = document.getElementById('modalCameraBtn');
      const modalCameraCloseBtn = document.getElementById('modalCameraCloseBtn');
      const cameraWrapper = document.getElementById('cameraWrapper');
      const modalVideo = document.getElementById('modalCameraVideo');
      const modalCaptureBtn = document.getElementById('modalCaptureBtn');

      if (modalUploadBtn && modalUploadInput) {
        modalUploadBtn.onclick = () => modalUploadInput.click();
        modalUploadInput.onchange = async (ev) => {
          const f = ev.target.files && ev.target.files[0];
          if (f) {
            currentFile = f;
            // Replace image src and re-init cropper: reopen modal with file
            await openCropModal(f);
          }
        };
      }

      let streamRef = null;
      async function startCamera() {
        try {
          const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
          streamRef = stream;
          if (modalVideo) modalVideo.srcObject = stream;
          if (cameraWrapper) cameraWrapper.style.display = 'flex';
          const imgEl = document.getElementById('cropImage');
          if (imgEl) imgEl.style.display = 'none';
          if (cropper) { cropper.destroy(); cropper = null; }
        } catch (err) {
          alert('Camera access denied or not available');
        }
      }

      function stopCamera() {
        try {
          if (streamRef) {
            streamRef.getTracks().forEach(t => t.stop());
            streamRef = null;
          }
        } catch (e) {}
        if (cameraWrapper) cameraWrapper.style.display = 'none';
        const imgEl = document.getElementById('cropImage');
        if (imgEl) imgEl.style.display = 'block';
      }

      modalCameraBtn?.addEventListener('click', () => startCamera());
      modalCameraCloseBtn?.addEventListener('click', () => stopCamera());

      modalCaptureBtn?.addEventListener('click', async () => {
        try {
          const video = modalVideo;
          const w = video.videoWidth || 1280;
          const h = video.videoHeight || 720;
          const cv = document.createElement('canvas');
          cv.width = w; cv.height = h;
          const ctx = cv.getContext('2d');
          ctx.drawImage(video, 0, 0, w, h);
          const dataUrl = cv.toDataURL('image/jpeg', 0.95);
          stopCamera();
          const blob = await (await fetch(dataUrl)).blob();
          const file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
          currentFile = file;
          // Re-open modal with the captured file so openCropModal handles orientation and fitting
          await openCropModal(file);
        } catch (e) {
          console.error('capture failed', e);
        }
      });
    }

    // Keyboard shortcuts while modal is open
    (function(){
      const modalEl = document.getElementById('imageCropModal');
      if (!modalEl) return;

      modalEl.addEventListener('shown.bs.modal', () => {
        window._cropModalKeyHandler = function(e) {
          if (!cropper) return;
          const zr = document.getElementById('zoomRange');
          if (e.key === 'r' || e.key === 'R') { try { cropper.rotate(90); } catch(e){} e.preventDefault(); }
          if (e.key === 'l' || e.key === 'L') { try { cropper.rotate(-90); } catch(e){} e.preventDefault(); }
          if (e.key === '+' || e.key === '=') { try { if (zr) { let v = parseFloat(zr.value||1); v = Math.min(parseFloat(zr.max||4), v + 0.1); zr.value = v; cropper.zoomTo(v); } } catch(e){} e.preventDefault(); }
          if (e.key === '-') { try { if (zr) { let v = parseFloat(zr.value||1); v = Math.max(parseFloat(zr.min||0.2), v - 0.1); zr.value = v; cropper.zoomTo(v); } } catch(e){} e.preventDefault(); }
          if (e.key === 'Enter') { const apply = document.getElementById('applyCrop'); if (apply) apply.click(); }
          if (e.key === 'Escape') { try { bootstrap.Modal.getInstance(modalEl).hide(); } catch(e){} }
        };
        window.addEventListener('keydown', window._cropModalKeyHandler);
      });

      modalEl.addEventListener('hidden.bs.modal', () => {
        if (window._cropModalKeyHandler) {
          window.removeEventListener('keydown', window._cropModalKeyHandler);
          window._cropModalKeyHandler = null;
        }
      });
    })();

    // Process and validate image
    async function processImage(file) {
      proofLoading.style.display = 'inline-block';
      proofStatus.innerHTML = '<small class="text-muted">Scanning ID...</small>';
      proofThumb.innerHTML = '';
      proofInput.dataset.validProof = '0';
      proofPreview.style.display = 'block';

      const discountType = discountTypeSel ? discountTypeSel.value : '';
      
      // Scan the ID image
      const scanResult = await scanIDImage(file, discountType);
      
      proofLoading.style.display = 'none';
      showScanResults(file, scanResult, discountType);
    }

    proofInput.addEventListener('change', async function(){
      const f = this.files && this.files[0];
      if (!f) {
        proofPreview.style.display = 'none';
        proofInput.dataset.validProof = '';
        return;
      }

      // Only allow cropping for images
      if (f.type.startsWith('image/')) {
        currentFile = f;
        await openCropModal(f);
      } else {
        // PDF or other files - process directly
        await processImage(f);
      }
    });

    // Re-scan when discount type changes
    if (discountTypeSel){
      discountTypeSel.addEventListener('change', async function(){
        const f = proofInput.files && proofInput.files[0];
        if (!f || !f.type.startsWith('image/')) return;
        
        proofLoading.style.display = 'inline-block';
        proofStatus.innerHTML = '<small class="text-muted">Re-scanning ID...</small>';
        
        const scanResult = await scanIDImage(f, discountTypeSel.value);
        
        proofLoading.style.display = 'none';
        showScanResults(f, scanResult, discountTypeSel.value);
      });
    }
  })();
</script>
