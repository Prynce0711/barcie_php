<div class="card mb-3" id="discountApplicationCard">
    <div class="card-header bg-success text-white">
        <strong><i class="fas fa-percent me-2"></i>Apply for Discount - Automatic Approval</strong>
    </div>
    <div class="card-body">
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Automatic Discount Approval:</strong> When you upload a valid ID proof, your discount will be
            automatically approved and applied to your booking. No waiting for manual review!
        </div>
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
            <label for="discount_proof" class="form-label">Upload Valid ID/Proof <span
                    class="text-danger">*</span></label>
            <!-- Inline alert placeholder immediately after the asterisk -->
            <span id="discount_proof_alert" style="display:inline-block; margin-left:6px;"></span>
            <input type="file" name="discount_proof" id="discount_proof" class="form-control"
                accept="image/*,application/pdf">
            <input type="hidden" name="discount_proof_cropped" id="discount_proof_cropped">
            <small class="form-text text-muted">Accepted: ID, certificate, or other proof (image or PDF). Discount will
                be automatically approved upon upload.</small>

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
                        <div class="progress-bar" role="progressbar" style="width:0%" aria-valuemin="0"
                            aria-valuemax="100">0%</div>
                    </div>
                    <button type="button" id="discount_upload_cancel" class="btn btn-sm btn-outline-danger"
                        style="display:none;margin-top:6px;">Cancel Upload</button>
                </div>
            </div>
        </div>
        <div class="mb-3" id="discount_details_section" style="display:none;">
            <label for="discount_details" class="form-label">Discount Details</label>
            <input type="text" name="discount_details" id="discount_details" class="form-control"
                placeholder="ID number, personnel/student number, etc.">
        </div>
        <div class="alert alert-info mb-0" id="discount_info_text" style="display:none;"></div>
    </div>
</div>

<!-- Image Crop/Edit Modal -->
<?php include __DIR__ . '/../Booking/image_crop_modal.php'; ?>

<script>
    // Discount card behaviour: show proof/details when a discount type selected
    (function () {
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

            // Helpful messages per type with automatic approval info
            let msg = '';
            if (v === 'pwd_senior') msg = '✓ Upload a government-issued ID showing PWD/senior status. Discount of 20% will be automatically applied.';
            else if (v === 'lcuppersonnel') msg = '✓ Upload your LCUP personnel ID or certificate. Discount of 10% will be automatically applied.';
            else if (v === 'lcupstudent') msg = '✓ Upload your student ID or alumni certificate. Discount of 7% will be automatically applied.';
            else msg = '✓ Please upload proof for the selected discount. It will be automatically approved.';

            info.textContent = msg;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const sel = document.getElementById('discount_type');
            if (!sel) return;
            sel.addEventListener('change', onDiscountChange);
            // Initialize visibility based on initial value
            onDiscountChange();
        });
    })();
</script>
<script>
    (function () {
        // Discount ID Validation
        const proofInput = document.getElementById('discount_proof');
        const proofPreview = document.getElementById('discount_proof_preview');
        const proofLoading = document.getElementById('discount_proof_loading');
        const proofStatus = document.getElementById('discount_proof_status');
        const proofThumb = document.getElementById('discount_proof_thumb');
        const discountTypeSel = document.getElementById('discount_type');

        if (!proofInput) return;

        // ============================================
        // OCR: Extract text from ID image
        // ============================================
        async function extractTextFromImage(imageDataUrl) {
            console.log('extractTextFromImage called');

            if (typeof Tesseract === 'undefined') {
                console.log('Tesseract not loaded, loading now...');
                try {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                    console.log('Tesseract loaded successfully');
                } catch (error) {
                    console.error('Failed to load OCR library:', error);
                    return { text: '', fullData: null };
                }
            }

            try {
                console.log('Creating Tesseract worker...');
                const worker = await Tesseract.createWorker('eng');
                console.log('Recognizing text from image...');
                const result = await worker.recognize(imageDataUrl);
                await worker.terminate();

                console.log('OCR Result:', result.data);
                console.log('OCR Text (original):', result.data.text);

                // Return both lowercase for validation and original case for extraction
                return {
                    text: result.data.text.toLowerCase(),
                    originalText: result.data.text,
                    fullData: result.data
                };
            } catch (error) {
                console.error('OCR failed:', error);
                return { text: '', originalText: '', fullData: null };
            }
        }

        // ============================================
        // Extract name and birthdate from OCR text
        // ============================================
        function extractNameAndBirthdate(ocrData) {
            console.log('extractNameAndBirthdate called with:', ocrData);

            if (!ocrData) {
                console.error('No ocrData provided');
                return { name: '', birthdate: '' };
            }

            if (!ocrData.fullData) {
                console.error('No fullData in ocrData');
                return { name: '', birthdate: '' };
            }

            // Use original text (not lowercase) for extraction
            const text = ocrData.originalText || ocrData.fullData.text || '';
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            let extractedName = '';
            let extractedBirthdate = '';

            console.log('OCR Full Text for Extraction (original case):', text);
            console.log('OCR Lines:', lines);

            // Extract name with more flexible patterns
            const namePatterns = [
                // Matches "NAME: Juan Dela Cruz" or "NAME Juan Dela Cruz"
                /(?:full\s*)?name[:\s]*([a-z][a-z\s,.'-]+)/i,
                // Matches "SURNAME: Dela Cruz, GIVEN NAME: Juan"
                /surname[:\s]*([a-z][a-z\s,.'-]+)/i,
                // Matches "GIVEN NAME: Juan" or "FIRST NAME: Juan"
                /(?:given|first)\s*name[:\s]*([a-z][a-z\s,.'-]+)/i,
                // Matches lines that look like names (2-4 words, each starting with capital)
                /^([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})$/m
            ];

            for (const pattern of namePatterns) {
                const match = text.match(pattern);
                if (match && match[1]) {
                    let name = match[1].trim();
                    // Clean up the name: remove multiple spaces, trim
                    name = name.replace(/\s+/g, ' ').trim();
                    // Remove any trailing punctuation or numbers
                    name = name.replace(/[,.:;\d]+$/, '').trim();
                    // Must be at least 3 characters and not too long
                    if (name.length >= 3 && name.length <= 50) {
                        extractedName = name;
                        console.log('Name extracted with pattern:', pattern, '→', name);
                        break;
                    }
                }
            }

            // Extract birthdate with multiple patterns
            const datePatterns = [
                // Matches "BIRTH DATE: 01/15/1990" or "DATE OF BIRTH: 01/15/1990"
                /(?:birth|birth[\s]?date|date[\s]?of[\s]?birth)[:\s]*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/i,
                // Matches "DOB: 01/15/1990" or "D.O.B: 01/15/1990"
                /d\.?o\.?b\.?[:\s]*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/i,
                // Matches "BORN: 01/15/1990"
                /born[:\s]*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/i,
                // Matches any date format with year 1900-2099
                /(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.](?:19|20)\d{2})/,
                // Matches dates like "Jan 15, 1990" or "January 15, 1990"
                /((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{1,2},?\s+(?:19|20)\d{2})/i
            ];

            for (const pattern of datePatterns) {
                const match = text.match(pattern);
                if (match && match[1]) {
                    extractedBirthdate = match[1];
                    console.log('Birthdate extracted with pattern:', pattern, '→', extractedBirthdate);
                    break;
                }
            }

            console.log('Final extraction → Name:', extractedName, 'Birthdate:', extractedBirthdate);
            return { name: extractedName, birthdate: extractedBirthdate };
        }

        // ============================================
        // Helper: Convert date format to YYYY-MM-DD
        // ============================================
        function convertToDateFormat(dateStr) {
            if (!dateStr) return null;

            // Handle month name formats like "Jan 15, 1990" or "January 15, 1990"
            const monthNames = {
                jan: '01', january: '01',
                feb: '02', february: '02',
                mar: '03', march: '03',
                apr: '04', april: '04',
                may: '05',
                jun: '06', june: '06',
                jul: '07', july: '07',
                aug: '08', august: '08',
                sep: '09', september: '09',
                oct: '10', october: '10',
                nov: '11', november: '11',
                dec: '12', december: '12'
            };

            // Try to parse "Month DD, YYYY" format
            const monthNameMatch = dateStr.match(/(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+(\d{1,2}),?\s+((?:19|20)\d{2})/i);
            if (monthNameMatch) {
                const month = monthNames[monthNameMatch[1].toLowerCase()];
                const day = monthNameMatch[2].padStart(2, '0');
                const year = monthNameMatch[3];
                return `${year}-${month}-${day}`;
            }

            // Try numeric date formats
            const parts = dateStr.split(/[\/\-\.]/);
            if (parts.length !== 3) return null;

            let year, month, day;

            // Check if format is YYYY-MM-DD
            if (parts[0].length === 4) {
                year = parts[0];
                month = parts[1];
                day = parts[2];
            }
            // Otherwise assume DD/MM/YYYY or MM/DD/YYYY
            else {
                year = parts[2];
                // Try both formats
                if (parseInt(parts[0]) > 12) {
                    // Must be DD/MM/YYYY (common in Philippines)
                    day = parts[0];
                    month = parts[1];
                } else if (parseInt(parts[1]) > 12) {
                    // Must be MM/DD/YYYY
                    month = parts[0];
                    day = parts[1];
                } else {
                    // Ambiguous, assume DD/MM/YYYY (Philippine format)
                    day = parts[0];
                    month = parts[1];
                }
            }

            // Pad with zeros
            month = String(month).padStart(2, '0');
            day = String(day).padStart(2, '0');

            // Basic validation
            if (year.length === 4 && month.length === 2 && day.length === 2) {
                const monthNum = parseInt(month);
                const dayNum = parseInt(day);
                if (monthNum >= 1 && monthNum <= 12 && dayNum >= 1 && dayNum <= 31) {
                    return `${year}-${month}-${day}`;
                }
            }

            return null;
        }

        // ============================================
        // KEYWORD MAPPING - Edit keywords here
        // ============================================
        const DISCOUNT_KEYWORDS = {
            // LCUP Student/Alumni Keywords
            lcupstudent: [
                'course',
                'year',
                'student',
                'alumni',
                'graduate',
                'batch'
            ],

            // LCUP Personnel/Faculty Keywords
            lcuppersonnel: [
                'personnel',
                'faculty',
                'staff',
                'employee',
                'official'
            ],

            // PWD/Senior Citizen Keywords
            pwd_senior: [
                'pwd',
                'senior citizen',
                'senior',
                'affairs',
                'osca',
                'disability',
                'disabled',
                'elderly',
                'person with disability',
                'persons with disability',
                'pensioner',
                'retiree'
            ]
        };

        // ============================================
        // Validation: Check keywords in text
        // ============================================
        function checkKeywordsInText(text, discountType) {
            const normalized = text.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ');

            // Get keywords for this discount type
            const keywords = DISCOUNT_KEYWORDS[discountType] || [];

            // Count how many keywords found
            let matches = 0;
            for (const keyword of keywords) {
                if (normalized.includes(keyword)) matches++;
            }

            // Valid if at least 1 keyword found
            return matches > 0;
        }

        // ============================================
        // Color Detection: Check for LCUP blue color
        // ============================================
        function hasLCUPBlueColor(imageData) {
            const pixels = imageData.data;
            let bluePixels = 0;
            let totalSamples = 0;

            // Sample every 10th pixel for speed
            for (let i = 0; i < pixels.length; i += 40) {
                const r = pixels[i];
                const g = pixels[i + 1];
                const b = pixels[i + 2];

                totalSamples++;

                // Check if pixel is blue-ish
                if (b > 80 && b > r && b > g) {
                    bluePixels++;
                }
            }

            const bluePercent = (bluePixels / totalSamples) * 100;
            console.log('Blue color detected:', bluePercent.toFixed(1) + '%');

            // LCUP IDs have at least 3% blue
            return bluePercent > 3;
        }

        // ============================================
        // Main Validation: Scan and validate ID
        // ============================================
        async function validateDiscountID(file, discountType) {
            // Only validate images (reject PDFs)
            if (!file.type.startsWith('image/')) {
                return {
                    valid: false
                };
            }

            return new Promise(async (resolve) => {
                const reader = new FileReader();

                reader.onload = async function (e) {
                    const imageDataUrl = e.target.result;
                    const img = new Image();

                    img.onload = async function () {
                        try {
                            // Prepare canvas for analysis
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            const maxWidth = 800;
                            const scale = Math.min(1, maxWidth / img.width);
                            canvas.width = img.width * scale;
                            canvas.height = img.height * scale;
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                            // Extract text with OCR
                            const ocrData = await extractTextFromImage(imageDataUrl);
                            const extractedText = ocrData.text;
                            console.log('OCR Text:', extractedText.substring(0, 200));

                            // Extract name and birthdate for auto-fill
                            const { name, birthdate } = extractNameAndBirthdate(ocrData);
                            console.log('=== AUTO-FILL DEBUG ===');
                            console.log('Extracted Name:', name);
                            console.log('Extracted Birthdate:', birthdate);

                            if (name) {
                                // Capitalize name properly (Title Case)
                                const capitalizedName = name.split(' ')
                                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                                    .join(' ');

                                console.log('Capitalized Name:', capitalizedName);

                                // Auto-fill guest name fields
                                const reservationName = document.getElementById('reservation_guest_name');
                                const pencilName = document.querySelector('#pencilForm input[name="guest_name"]');

                                console.log('Reservation Name Field:', reservationName);
                                console.log('Current Value:', reservationName ? reservationName.value : 'N/A');
                                console.log('Pencil Name Field:', pencilName);

                                if (reservationName) {
                                    if (!reservationName.value) {
                                        reservationName.value = capitalizedName;
                                        console.log('✓ Filled reservation_guest_name with:', capitalizedName);
                                    } else {
                                        console.log('⚠ Skipped: field already has value:', reservationName.value);
                                    }
                                }
                                if (pencilName) {
                                    if (!pencilName.value) {
                                        pencilName.value = capitalizedName;
                                        console.log('✓ Filled pencil guest_name with:', capitalizedName);
                                    } else {
                                        console.log('⚠ Skipped: pencil field already has value');
                                    }
                                }
                            } else {
                                console.log('⚠ No name extracted from OCR');
                            }

                            if (birthdate) {
                                console.log('Converting birthdate:', birthdate);
                                // Convert and auto-fill birthdate
                                const formattedDate = convertToDateFormat(birthdate);
                                console.log('Formatted Date:', formattedDate);

                                if (formattedDate) {
                                    const reservationBirthdate = document.getElementById('reservation_birthdate');
                                    const pencilBirthdate = document.getElementById('pencil_birthdate');

                                    console.log('Reservation Birthdate Field:', reservationBirthdate);
                                    console.log('Pencil Birthdate Field:', pencilBirthdate);

                                    if (reservationBirthdate && !reservationBirthdate.value) {
                                        reservationBirthdate.value = formattedDate;
                                        reservationBirthdate.dispatchEvent(new Event('change'));
                                        console.log('✓ Filled reservation_birthdate with:', formattedDate);
                                    }
                                    if (pencilBirthdate && !pencilBirthdate.value) {
                                        pencilBirthdate.value = formattedDate;
                                        pencilBirthdate.dispatchEvent(new Event('change'));
                                        console.log('✓ Filled pencil_birthdate with:', formattedDate);
                                    }
                                } else {
                                    console.log('⚠ Failed to convert date format');
                                }
                            } else {
                                console.log('⚠ No birthdate extracted from OCR');
                            }
                            console.log('=== END AUTO-FILL DEBUG ===');

                            // Validate based on discount type (keywords only)
                            let isValid = false;

                            if (discountType === 'lcuppersonnel' || discountType === 'lcupstudent' || discountType === 'pwd_senior') {
                                // Check keywords for all discount types
                                const hasKeywords = checkKeywordsInText(extractedText, discountType);

                                console.log('Validation Check - Keywords found:', hasKeywords);

                                // Accept only if keywords are found
                                if (hasKeywords) {
                                    isValid = true;
                                } else {
                                    // Reject if no keywords found
                                    isValid = false;
                                }
                            } else {
                                // Unknown type - reject
                                isValid = false;
                            }

                            resolve({
                                valid: isValid
                            });

                        } catch (error) {
                            console.error('Validation error:', error);
                            // On error, reject
                            resolve({
                                valid: false
                            });
                        }
                    };

                    img.onerror = () => resolve({
                        valid: false
                    });
                    img.src = imageDataUrl;
                };

                reader.onerror = () => resolve({
                    valid: false
                });
                reader.readAsDataURL(file);
            });
        }

        // ============================================
        // Display: Show validation results
        // ============================================
        function showValidationResults(file, result, discountType) {
            proofPreview.style.display = 'block';
            proofStatus.innerHTML = '';

            // Show file thumbnail
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    proofThumb.innerHTML = `<img src="${e.target.result}" style="max-width:160px;border-radius:6px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                proofThumb.innerHTML = `<div class="p-2 border rounded"><i class="far fa-file-pdf fa-2x"></i><div class="small mt-1">${file.name}</div></div>`;
            }

            // Show validation status
            const statusEl = document.createElement('div');

            if (result.valid) {
                statusEl.className = 'text-success fw-bold';
                statusEl.innerHTML = '<i class="fas fa-check-circle me-1"></i>✓ Discount ID accepted';
                proofInput.dataset.validProof = '1';
            } else {
                statusEl.className = 'text-danger fw-bold';
                statusEl.innerHTML = '<i class="fas fa-times-circle me-1"></i>✗ Please upload a valid ID for the selected discount type';
                proofInput.dataset.validProof = '0';
            }

            proofStatus.appendChild(statusEl);
        }

        // ============================================
        // Main: Process uploaded file
        // ============================================
        async function processUploadedFile(file) {
            proofLoading.style.display = 'inline-block';
            proofStatus.innerHTML = '<small class="text-muted">Validating ID...</small>';
            proofThumb.innerHTML = '';
            proofInput.dataset.validProof = '0';
            proofPreview.style.display = 'block';

            const discountType = discountTypeSel ? discountTypeSel.value : '';

            // Validate the ID
            const result = await validateDiscountID(file, discountType);

            proofLoading.style.display = 'none';
            showValidationResults(file, result, discountType);

            // Trigger form lock check if the function exists (in booking.php)
            if (typeof window.checkAndEnableFormFields === 'function') {
                window.checkAndEnableFormFields('reservation');
                window.checkAndEnableFormFields('pencil');
            }
        }

        // ============================================
        // Event: File input changed
        // ============================================
        proofInput.addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) {
                proofPreview.style.display = 'none';
                proofInput.dataset.validProof = '';
                return;
            }

            // Process file directly without cropping
            await processUploadedFile(file);
        });

        // ============================================
        // Event: Discount type changed - re-validate
        // ============================================
        if (discountTypeSel) {
            discountTypeSel.addEventListener('change', async function () {
                const file = proofInput.files && proofInput.files[0];
                if (!file || !file.type.startsWith('image/')) {
                    // Trigger form lock check when discount type changes
                    if (typeof window.checkAndEnableFormFields === 'function') {
                        window.checkAndEnableFormFields('reservation');
                        window.checkAndEnableFormFields('pencil');
                    }
                    return;
                }

                proofLoading.style.display = 'inline-block';
                proofStatus.innerHTML = '<small class="text-muted">Re-validating ID...</small>';

                const result = await validateDiscountID(file, discountTypeSel.value);

                proofLoading.style.display = 'none';
                showValidationResults(file, result, discountTypeSel.value);

                // Trigger form lock check after re-validation
                if (typeof window.checkAndEnableFormFields === 'function') {
                    window.checkAndEnableFormFields('reservation');
                    window.checkAndEnableFormFields('pencil');
                }
            });
        }

    })();
</script>