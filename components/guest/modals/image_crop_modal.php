<!-- Image Crop/Edit Modal (moved out from discount_application.php) -->
<div class="modal fade" id="imageCropModal">

  <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
    <div class="modal-content">
      <div class="modal-header" style="padding: 16px 20px; border-bottom: 2px solid #e9ecef;">
        <div class="d-flex align-items-center gap-2">
          <i class="fas fa-crop-alt" style="font-size: 1.2rem; color: #0d6efd;"></i>
          <h5 class="modal-title mb-0" id="imageCropModalLabel">Crop & Edit Your ID</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 0; background: #f8f9fa;">
        <!-- Main Image Area -->
        <div id="cropImageContainer" style="width: 100%; height: 360px; overflow: hidden; background: #2c3e50; position: relative; display:flex; align-items:center; justify-content:center;">
          <div id="cameraWrapper" style="display:none; width:100%; height:100%; align-items:center; justify-content:center; background: #000;">
            <video id="modalCameraVideo" autoplay playsinline style="max-width:100%; max-height:100%; display:block;"></video>
            <div style="position:absolute; bottom:20px; left:50%; transform:translateX(-50%); display:flex; gap:12px;">
              <button type="button" class="btn btn-danger" id="modalCaptureBtn" aria-label="Capture photo">
                <i class="fas fa-circle me-2"></i> Capture Photo
              </button>
              <button type="button" class="btn btn-secondary" id="modalCameraCloseBtn" aria-label="Close camera">
                <i class="fas fa-times me-2"></i> Cancel
              </button>
            </div>
          </div>
          <img id="cropImage" role="img" alt="ID preview" style="display: block; width: 100%; height: auto; max-height:100%; object-fit:contain;">
        </div>
        
        <!-- Tools Toolbar -->
        <div class="bg-white border-top" style="padding: 16px 20px;">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <!-- Left: Transform Tools -->
            <div class="d-flex align-items-center gap-2">
              <small class="text-dark fw-bold me-2">Transform:</small>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-primary" id="rotateLeft" title="Rotate Left (L)" aria-label="Rotate left">
                  <i class="fas fa-undo"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="rotateRight" title="Rotate Right (R)" aria-label="Rotate right">
                  <i class="fas fa-redo"></i>
                </button>
                <button type="button" class="btn btn-sm btn-info" id="flipHorizontal" title="Flip Horizontal" aria-label="Flip horizontal">
                  <i class="fas fa-arrows-alt-h"></i>
                </button>
                <button type="button" class="btn btn-sm btn-info" id="flipVertical" title="Flip Vertical" aria-label="Flip vertical">
                  <i class="fas fa-arrows-alt-v"></i>
                </button>
              </div>
            </div>
            
            <!-- Center: Zoom Control -->
            <div class="d-flex align-items-center gap-2 flex-fill" style="max-width: 300px;">
              <small class="text-dark fw-bold">Zoom:</small>
              <i class="fas fa-search-minus text-dark"></i>
              <input type="range" class="form-range flex-fill" id="zoomRange" min="0.2" max="4" step="0.01" value="1">
              <i class="fas fa-search-plus text-dark"></i>
            </div>
            
            <!-- Right: Camera Button -->
            <div>
              <button type="button" class="btn btn-sm btn-success" id="modalCameraBtn" title="Open Camera">
                <i class="fas fa-camera me-1"></i> Camera
              </button>
            </div>
          </div>
          
          <!-- Status Messages -->
          <div id="modal_detect_status" class="mt-2"></div>
          <div id="modalOcrPreview" class="small text-dark bg-light p-2 mt-2" style="max-height:80px; overflow:auto; display:none; border-radius:4px; font-family: monospace;">
            <!-- OCR preview appears here -->
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light" style="padding: 16px 20px; border-top: 2px solid #e9ecef;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancel
        </button>
        <button type="button" class="btn btn-success" id="applyCrop">
          <i class="fas fa-check me-2"></i> Apply & Validate
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Scoped modal styles -->
<style>
  #imageCropModal .modal-content { border-radius: 8px; overflow: hidden; }
  #imageCropModal .modal-footer { display:flex; justify-content:space-between; align-items:center; gap:8px; }
  #imageCropModal .btn-group .btn { padding: 6px 12px; }
  #imageCropModal #cropImageContainer { box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1); }
  #imageCropModal .form-range::-webkit-slider-thumb { cursor: pointer; }
  #imageCropModal .form-range::-moz-range-thumb { cursor: pointer; }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    #imageCropModal .modal-dialog { max-width: 95vw !important; margin: 8px auto; }
    #imageCropModal #cropImageContainer { height: 240px !important; }
    #imageCropModal .modal-body > div:last-child { flex-direction: column; }
    #imageCropModal .modal-body > div:last-child > div { max-width: 100% !important; width: 100%; }
  }
</style>

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
// ...existing code...
async function extractTextFromImage(imageDataUrl) {
  // Ensure Tesseract is available (load from CDN if missing)
  if (typeof Tesseract === 'undefined') {
    try {
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
      // Proper worker lifecycle for Tesseract.js v5+
      const worker = Tesseract.createWorker({ logger: () => {} });
      await worker.load();
      await worker.loadLanguage('eng');
      await worker.initialize('eng');

      // Create simple image-processing variants to improve OCR robustness
      async function makeVariants(dataUrl) {
        return new Promise((resolve) => {
          const img = new Image();
          img.onload = function() {
            const maxW = 1600;
            const scale = Math.min(1, maxW / img.width) || 1;
            const w = Math.max(800, Math.floor(img.width * scale));
            const h = Math.max(600, Math.floor(img.height * scale));
            const cvs = document.createElement('canvas');
            cvs.width = w; cvs.height = h;
            const ctx = cvs.getContext('2d');
            // draw original scaled
            ctx.drawImage(img, 0, 0, w, h);

            // original
            const original = cvs.toDataURL('image/jpeg', 0.9);

            // high contrast / brighten
            const imgd = ctx.getImageData(0, 0, w, h);
            const d = imgd.data;
            // simple linear contrast/stretch
            const contrast = 1.2; // >1 increases contrast
            const brightness = 10; // add slight brightness
            for (let i = 0; i < d.length; i += 4) {
              for (let c = 0; c < 3; c++) {
                let v = d[i + c];
                v = ((v - 128) * contrast) + 128 + brightness;
                d[i + c] = Math.max(0, Math.min(255, v));
              }
            }
            ctx.putImageData(imgd, 0, 0);
            const enhanced = cvs.toDataURL('image/jpeg', 0.9);

            // inverted (sometimes helps light-on-dark)
            const imgd2 = ctx.getImageData(0, 0, w, h);
            const d2 = imgd2.data;
            for (let i = 0; i < d2.length; i += 4) {
              d2[i] = 255 - d2[i];
              d2[i+1] = 255 - d2[i+1];
              d2[i+2] = 255 - d2[i+2];
            }
            ctx.putImageData(imgd2, 0, 0);
            const inverted = cvs.toDataURL('image/jpeg', 0.9);

            // restore original image on canvas for potential reuse
            const ctxRestore = cvs.getContext('2d');
            ctxRestore.clearRect(0,0,w,h);
            ctxRestore.drawImage(img,0,0,w,h);

            resolve([original, enhanced, inverted]);
          };
          img.onerror = function() { resolve([imageDataUrl]); };
          img.src = dataUrl;
        });
      }

      const variants = await makeVariants(imageDataUrl);

      // Run OCR on each variant and pick the richest result (most alphanumeric characters)
      const results = [];
      for (const v of variants) {
        try {
          const { data: { text } } = await worker.recognize(v);
          results.push((text || '').toLowerCase());
        } catch (e) {
          results.push('');
        }
      }

      await worker.terminate();

      // Choose best result by counting letters/digits
      let best = '';
      let bestScore = 0;
      for (const t of results) {
        const s = (t.match(/[a-z0-9]/gi) || []).length;
        if (s > bestScore) { bestScore = s; best = t; }
      }

      if (window._idDebug) console.debug('OCR variants results', results, 'selected', best.slice(0,200));
      return best;
    } catch (error) {
      console.warn('OCR failed:', error);
      return '';
    }
}
// ...existing code...

    /**
     * Check if text contains LCUP keywords (strict matching)
     */
    function checkTextForLCUP(text) {
      // Relaxed matching: look for shorter, partial keywords so OCR variance
      // (cropping, lighting, fonts) doesn't prevent detection.
      const primaryPatterns = [
        /la consolacion university philippines/i,
        /la consolacion university/i,
        /la consolacion college/i,
        /la consolacion/i,
        /consolacion university/i,
        /consolacion/i,
        /\blcup\b/i
      ];

      const secondaryPatterns = [ /student/i, /alumni/i, /employee/i, /personnel/i, /id/i ];

      const foundPrimary = [];
      const foundSecondary = [];

      for (const p of primaryPatterns) {
        if (p.test(text)) foundPrimary.push(p.toString());
      }
      for (const p of secondaryPatterns) {
        if (p.test(text)) foundSecondary.push(p.toString());
      }

      // Compute a lightweight confidence score based on matches and text length
      let confidence = 0;
      if (foundPrimary.length > 0) confidence += Math.min(70, foundPrimary.length * 40);
      if (foundSecondary.length > 0) confidence += Math.min(30, foundSecondary.length * 12);
      // small boost for longer readable OCR output
      if (text && text.length > 60) confidence = Math.min(100, confidence + 10);

      return {
        found: foundPrimary.length > 0,
        keywords: foundPrimary.concat(foundSecondary),
        confidence: Math.round(confidence)
      };
    }

    /**
     * Check if text indicates a Senior or PWD ID.
     */
    function checkTextForPWD(text) {
      if (!text || !text.trim()) return { found: false, keywords: [], confidence: 0 };
      const patterns = [
        /\bsenior citizen(s)?\b/i,
        /\bsenior\b/i,
        /\bpwd\b/i,
        /person with disab/i,
        /\bdisab/i,
        /\bsenior id\b/i,
        /\bsenior card\b/i,
        /\bsenior citizen id\b/i,
        /\bsenior citizen card\b/i,
        /office of senior citizens/i,
        /office of senior citizens affairs/i,
        /senior citizens affairs/i,
        /municipality of/i,
        /province of/i,
        /date of birth/i,
        /id no\b/i,
        /id no\./i,
        /printed name/i,
        /non[- ]transferable/i
      ];
      const found = [];
      for (const p of patterns) {
        try {
          if (p.test(text)) found.push(p.toString());
        } catch (e) { /* ignore */ }
      }

      let confidence = 0;
      if (found.length > 0) confidence += Math.min(80, found.length * 40);
      if (text.length > 80) confidence = Math.min(100, confidence + 10);

      return { found: found.length > 0, keywords: found, confidence: Math.round(confidence) };
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
                    result.features.unshift('✓ Text: "' + (textCheck.keywords[0] || 'la consolacion') + '" detected');
                    result.ocrText = extractedText;
                  } else if (extractedText.includes('consolacion') || extractedText.includes('la consolacion')) {
                    // Fallback: partial match (not caught by patterns due to OCR noise)
                    hasTextMatch = true;
                    result.features.unshift('✓ Partial text match: "consolacion"');
                    result.ocrText = extractedText;
                  } else {
                    result.ocrText = extractedText;
                  }
                }

                // More forgiving acceptance: prefer color+text, but allow good color score
                // with partial/low-confidence text matches. Also accept strong text
                // matches even if color score is modest.
                const textScore = (result.ocrText && result.ocrText.length > 0) ? ( (hasTextMatch) ? 40 : 0 ) : 0;

                if (hasColorMatch && hasTextMatch) {
                  result.detected = true;
                  result.confidence = Math.min(100, colorScore + 50 + textScore);
                } else if (hasColorMatch && !hasTextMatch) {
                  // If color score is strong, allow a generous fallback (user photos can lose text readability)
                  if (colorScore >= 40) {
                    result.detected = true;
                    result.confidence = Math.min(100, colorScore + 15);
                    result.features.push('⚠️ Text unclear, accepting based on color pattern');
                  } else {
                    result.detected = false;
                    result.confidence = Math.min(30, colorScore);
                    result.features.push('⚠️ Text not detected (partial OCR may be present)');
                  }
                } else if (!hasColorMatch && hasTextMatch) {
                  // Accept if text is clearly present even without strong color cues
                  result.detected = true;
                  result.confidence = Math.min(80, 30 + (textScore));
                  result.features.push('⚠️ Color pattern weak, accepting based on text match');
                } else {
                  result.detected = false;
                  result.confidence = 10;
                  result.features.push('⚠️ Not an LCUP ID');
                }
              } else if (discountType === 'pwd_senior') {
                // For Senior/PWD IDs, run OCR as well and combine text+color signals.
                const ocrPromisePwd = extractTextFromImage(imageDataUrl);
                detectSeniorPWDID(top, middle, bottom, isLandscape, result);

                const extractedPwdText = await ocrPromisePwd;
                let hasPwdText = false;
                let textCheckPwd = null;
                if (extractedPwdText) {
                  textCheckPwd = checkTextForPWD(extractedPwdText);
                  if (textCheckPwd.found) {
                    hasPwdText = true;
                    result.features.unshift('✓ Text: "' + (textCheckPwd.keywords[0] || 'senior/pwd') + '" detected');
                    result.ocrText = extractedPwdText;
                  } else if (extractedPwdText.toLowerCase().includes('senior') || extractedPwdText.toLowerCase().includes('pwd') || extractedPwdText.toLowerCase().includes('person with')) {
                    // partial fallback
                    hasPwdText = true;
                    result.features.unshift('✓ Partial text match: "senior/pwd"');
                    result.ocrText = extractedPwdText;
                  } else {
                    result.ocrText = extractedPwdText;
                  }
                }

                // Decision rules: prefer combined signals, but accept strong text or strong color alone.
                const colorScorePwd = result.confidence || 0;
                if (hasPwdText && colorScorePwd >= 10) {
                  result.detected = true;
                  result.confidence = Math.min(100, colorScorePwd + 40);
                } else if (hasPwdText && colorScorePwd < 10) {
                  // Accept if text is clear even if color clues are weak
                  result.detected = true;
                  const pwdTextConf = (textCheckPwd && textCheckPwd.confidence) ? textCheckPwd.confidence : 30;
                  result.confidence = Math.min(90, 30 + pwdTextConf);
                } else if (!hasPwdText && colorScorePwd >= 35) {
                  // Strong color pattern (e.g., yellow/gov colors) may be enough
                  result.detected = true;
                  result.confidence = Math.min(80, colorScorePwd + 10);
                } else {
                  result.detected = false;
                  result.confidence = Math.max(10, colorScorePwd);
                  if (!hasPwdText) result.features.push('⚠️ No clear "Senior" or "PWD" text detected');
                }
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
      // Relaxed color scoring to accept partial crops and IDs not filling frame.
      // We reduce the strict dependence on a large white content area and make
      // blue/header detection more permissive. The goal is to allow valid
      // ID photos even when users don't perfectly frame the card.
      let colorScore = 0;

      // Blue header / strong blue region: lower thresholds to detect small headers
      const topBlue = top.bluePercent || 0;
      const midBlue = middle.bluePercent || 0;
      const botBlue = bottom.bluePercent || 0;

      if (topBlue > 5 || midBlue > 5) {
        result.features.push('✓ Blue ID background');
        const blueConfidence = Math.min(40, Math.max(topBlue, midBlue) * 2);
        colorScore += blueConfidence;
      }

      // Overall blue presence across the card. Accept smaller percentages as signal
      const totalBlue = (topBlue + midBlue + botBlue) / 3;
      if (totalBlue > 3 && colorScore === 0) {
        result.features.push('✓ Blue color pattern');
        colorScore += Math.min(30, totalBlue * 4);
      }

      // Orange accent detection (CIT accent) remains useful but lowered thresholds
      const totalOrange = (top.orangePercent + middle.orangePercent + bottom.orangePercent) / 3;
      if (totalOrange > 2) {
        result.features.push('✓ Orange CIT accent');
        colorScore += Math.min(20, totalOrange * 3);
      }

      // White content area (photo/text) is informative but not required - lower weight
      const whiteScore = Math.max(middle.whitePercent || 0, bottom.whitePercent || 0);
      if (whiteScore > 8) {
        result.features.push('✓ Visible ID content area');
        colorScore += 8;
      }

      // If the card is small in the frame (high overall whitePercent) we should
      // not penalize it: prefer color/text matches over occupying the frame.
      // (No negative adjustments here)

      // Store color score but don't set detected flag yet. Text/OCR will be combined
      // later to determine final detection.
      result.confidence = Math.round(colorScore);
      result.detected = false;
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
    // Guard to avoid binding modal controls multiple times (prevents duplicate listeners/cropper instances)
    if (typeof window._cropModalControlsBound === 'undefined') window._cropModalControlsBound = false;

    // Stop any active camera stream attached to the modal video
    function stopModalCamera() {
      try {
        const modalVideo = document.getElementById('modalCameraVideo');
        if (modalVideo && modalVideo.srcObject) {
          const stream = modalVideo.srcObject;
          if (stream.getTracks) {
            stream.getTracks().forEach(t => t.stop());
          }
          modalVideo.srcObject = null;
        }
      } catch (e) { /* ignore */ }
    }

    // Clean up modal state: destroy cropper, clear inputs and previews
    function cleanupModalState() {
      try {
        if (cropper) {
          try { cropper.destroy(); } catch (e) { /* ignore */ }
          cropper = null;
        }
      } catch (e) {}

      // Remove any leftover cropper containers that are inside the crop image area only
      try {
        removeScopedCropperContainers(document.getElementById('cropImage'));
      } catch (e) {}

      try {
        const proofInputEl = document.getElementById('discount_proof');
        if (proofInputEl) {
          proofInputEl.value = '';
          proofInputEl.dataset.validProof = '';
          proofInputEl.dataset.confidence = '';
          proofInputEl.dataset.validReason = '';
        }

        const proofPreviewEl = document.getElementById('discount_proof_preview');
        if (proofPreviewEl) proofPreviewEl.style.display = 'none';

        const proofThumbEl = document.getElementById('discount_proof_thumb');
        if (proofThumbEl) proofThumbEl.innerHTML = '';

        const proofStatusEl = document.getElementById('discount_proof_status');
        if (proofStatusEl) proofStatusEl.innerHTML = '';

        const proofLoadingEl = document.getElementById('discount_proof_loading');
        if (proofLoadingEl) proofLoadingEl.style.display = 'none';

        const croppedInput = document.getElementById('discount_proof_cropped');
        if (croppedInput) croppedInput.value = '';
      } catch (e) { /* ignore */ }
    }

    // Remove .cropper-container nodes only within the crop image container
    function removeScopedCropperContainers(imgEl) {
      try {
        var parent = null;
        if (imgEl && imgEl.parentNode) parent = imgEl.parentNode;
        if (!parent) parent = document.getElementById('cropImageContainer') || document.body;
        parent.querySelectorAll('.cropper-container').forEach(function(el){
          try { el.remove(); } catch(e) { /* ignore */ }
        });
      } catch (e) { /* ignore */ }
    }

    // Load Cropper.js library dynamically
    function loadCropperJS() {
      return new Promise((resolve, reject) => {
        if (typeof Cropper === 'undefined') {
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
        } else {
          resolve();
        }
      });
    }

    // Open crop modal
    async function openCropModal(file) {
      try {
        await loadCropperJS();
        
        const reader = new FileReader();
        reader.onload = function(e) {
          // Use a mutable reference so we can replace the element if needed
          let cropImage = document.getElementById('cropImage');
          const imgSrc = e.target.result;

          // Determine image orientation first so we can set correct aspect ratio
          const probe = new Image();
          probe.onload = function() {
            // LCUP ID is portrait (height > width) — use portrait aspect ratio
            const idLandscapeRatio = 1.586; // width/height for landscape IDs
            const idPortraitRatio = 1 / idLandscapeRatio; // width/height for portrait IDs
            const usePortrait = probe.naturalHeight > probe.naturalWidth;
            const aspectForCropper = usePortrait ? idPortraitRatio : idLandscapeRatio;

            // Replace the image element with a fresh clone to ensure no previous
            // Cropper data or attached elements remain. This prevents visual
            // duplication when creating a new Cropper instance.
            try {
              const existing = document.getElementById('cropImage');
              if (existing) {
                const fresh = existing.cloneNode(false);
                // preserve inline styles/attributes we rely on
                fresh.id = existing.id;
                fresh.role = existing.getAttribute('role') || 'img';
                fresh.alt = existing.alt || 'ID preview';
                existing.parentNode.replaceChild(fresh, existing);
                cropImage = fresh;
              }
            } catch (e) { /* ignore */ }

            // Ensure previous cropper removed and any leftover containers cleared
            if (cropper) {
              try { cropper.destroy(); } catch (e) { /* ignore */ }
              cropper = null;
            }
            try { removeScopedCropperContainers(document.getElementById('cropImage')); } catch (e) { /* ignore */ }

            cropImage.src = imgSrc;

            const modalEl = document.getElementById('imageCropModal');
            const modal = new bootstrap.Modal(modalEl);

            // Initialize Cropper only after modal is fully shown to avoid timing/backdrop issues
            const onShown = function onShownHandler() {
              modalEl.removeEventListener('shown.bs.modal', onShownHandler);

              // Defensive checks
              if (!cropImage || !cropImage.src) return;

              try {
                cropper = new Cropper(cropImage, {
                  aspectRatio: aspectForCropper,
                  viewMode: 1,
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
                    try {
                      const containerData = cropper.getContainerData();
                      const imgData = cropper.getImageData();
                      const fitZoomX = containerData.width / (imgData.naturalWidth || imgData.width || 1);
                      const fitZoomY = containerData.height / (imgData.naturalHeight || imgData.height || 1);
                      const fitZoom = Math.min(fitZoomX, fitZoomY, 1);
                      try { cropper.zoomTo(fitZoom); } catch (e) { /* ignore */ }
                      const zr = document.getElementById('zoomRange');
                      if (zr) {
                        const min = Math.max(0.2, fitZoom * 0.5);
                        const max = Math.max(fitZoom * 3, min + 0.5);
                        zr.min = min; zr.max = max; zr.step = 0.01; zr.value = fitZoom; zr.dataset.base = fitZoom;
                      }
                      try {
                        const updatedImg = cropper.getImageData();
                        const canvasData = cropper.getCanvasData();
                        const dispW = updatedImg.width;
                        const dispH = updatedImg.height;
                        const targetAspect = aspectForCropper;
                        let cropW = dispW;
                        let cropH = cropW / targetAspect;
                        if (cropH < dispH) { cropH = dispH; cropW = cropH * targetAspect; }
                        const left = canvasData.left + (canvasData.width - cropW) / 2;
                        const top = canvasData.top + (canvasData.height - cropH) / 2;
                        cropper.setCropBoxData({ left: Math.max(0, left), top: Math.max(0, top), width: Math.min(cropW, canvasData.width), height: Math.min(cropH, canvasData.height) });
                      } catch (e) { /* ignore */ }
                    } catch (e) { console.warn('Cropper fit failed', e); }
                  }
                });
              } catch (e) {
                console.error('Cropper init failed', e);
              }

              try { bindModalControls(); } catch (e) { /* ignore */ }
            };

            modalEl.addEventListener('shown.bs.modal', onShown);
            modal.show();

            // If the modal is already visible (replacing image while open), initialize immediately
            if (modalEl.classList && modalEl.classList.contains('show')) {
              try { onShown(); } catch (e) { /* ignore */ }
            }
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
          try {
            // Mark that a crop was applied so the hidden handler preserves the preview/input
            window._cropApplied = true;
          } catch (e) {}

          var modal = bootstrap.Modal.getInstance(document.getElementById('imageCropModal'));
          modal.hide();

          // Process cropped image (we still pass the file directly)
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
      // Prevent double-binding controls (returns early if already bound)
      if (window._cropModalControlsBound) return;
      window._cropModalControlsBound = true;

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

        // Reset controls-bound flag so controls can be rebound next time
        try { window._cropModalControlsBound = false; } catch (e) {}

        // Stop camera
        try { stopModalCamera(); } catch (e) { /* ignore */ }

        // If a crop was just applied, preserve the main file input and preview
        // (processImage already handled updating the preview). Still destroy
        // the cropper internals to keep DOM clean.
        try {
          // Prefer preserving preview if the hidden input has cropped data.
          const croppedInput = document.getElementById('discount_proof_cropped');
          const hasCroppedValue = croppedInput && croppedInput.value && croppedInput.value.length > 100;

          if (hasCroppedValue) {
            // destroy cropper and remove internal containers only
            try { if (cropper) { cropper.destroy(); } } catch (e) {}
            cropper = null;
            try { removeScopedCropperContainers(document.getElementById('cropImage')); } catch (e) {}
            // clear any transient applied flag
            try { window._cropApplied = false; } catch (e) {}
          } else if (window._cropApplied) {
            // fallback: if flag was set but no cropped input (race), still preserve briefly
            try { if (cropper) { cropper.destroy(); } } catch (e) {}
            cropper = null;
            try { removeScopedCropperContainers(document.getElementById('cropImage')); } catch (e) {}
            try { window._cropApplied = false; } catch (e) {}
          } else {
            // Full cleanup (user cancelled or closed without applying)
            try { cleanupModalState(); } catch (e) { /* ignore */ }
          }
        } catch (e) { /* ignore */ }
      });
    })();

    // Defensive cleanup: ensure body scrolling is restored if modal backdrop handling got out-of-sync
    (function ensureBodyScrollRestored(){
      document.addEventListener('hidden.bs.modal', function(ev){
        // run after a short delay to allow Bootstrap to finish its work
        setTimeout(() => {
          try {
            const anyOpen = document.querySelectorAll('.modal.show').length > 0;
            if (!anyOpen) {
              // remove leftover backdrops
              document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
              // restore body class/style
              try { document.body.classList.remove('modal-open'); } catch(e) {}
              try { document.body.style.overflow = ''; } catch(e) {}
            }
          } catch (e) { /* ignore */ }
        }, 50);
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
