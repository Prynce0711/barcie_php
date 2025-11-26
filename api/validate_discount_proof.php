<?php
/**
 * API endpoint to validate discount proof images using OCR/text detection
 * Analyzes actual image content rather than just filename
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Check if request is POST with file
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file']) || !isset($_POST['discount_type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing file or discount_type']);
    exit;
}

$file = $_FILES['file'];
$discountType = $_POST['discount_type'];

// Validate file upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit;
}

// Check file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images and PDFs are allowed.']);
    exit;
}

// Check file size (max 10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB.']);
    exit;
}

/**
 * Extract text from image using Tesseract OCR if available
 * Falls back to basic image analysis if Tesseract is not installed
 */
function extractTextFromImage($imagePath) {
    $text = '';
    
    // Try Tesseract OCR if available
    if (function_exists('exec')) {
        // Check if tesseract is installed
        $output = [];
        $returnVar = 0;
        @exec('tesseract --version 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            // Tesseract is available
            $tempTxt = tempnam(sys_get_temp_dir(), 'ocr_');
            $cmd = "tesseract " . escapeshellarg($imagePath) . " " . escapeshellarg($tempTxt) . " 2>&1";
            @exec($cmd, $output, $returnVar);
            
            if (file_exists($tempTxt . '.txt')) {
                $text = file_get_contents($tempTxt . '.txt');
                @unlink($tempTxt . '.txt');
            }
            @unlink($tempTxt);
        }
    }
    
    // Fallback: analyze image metadata and basic properties
    if (empty($text)) {
        // Try to extract EXIF data which might contain text
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($imagePath);
            if ($exif && isset($exif['ImageDescription'])) {
                $text .= ' ' . $exif['ImageDescription'];
            }
            if ($exif && isset($exif['UserComment'])) {
                $text .= ' ' . $exif['UserComment'];
            }
        }
    }
    
    return $text;
}

/**s
 * Extract text from PDF using pdftotext if available
 */
function extractTextFromPDF($pdfPath) {
    $text = '';
    
    if (function_exists('exec')) {
        // Try pdftotext (part of poppler-utils)
        $output = [];
        $returnVar = 0;
        @exec('pdftotext --version 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            $tempTxt = tempnam(sys_get_temp_dir(), 'pdf_');
            $cmd = "pdftotext " . escapeshellarg($pdfPath) . " " . escapeshellarg($tempTxt) . " 2>&1";
            @exec($cmd, $output, $returnVar);
            
            if (file_exists($tempTxt)) {
                $text = file_get_contents($tempTxt);
                @unlink($tempTxt);
            }
        }
    }
    
    return $text;
}

/**
 * Validate the extracted text against the discount type
 */
function validateDiscountProof($text, $discountType) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);
    
    // Define specific keywords for each discount type
    $studentKeywords = [
        'student',
        'estudyante',
        'undergraduate',
        'scholar',
        'enrollee',
        'id no',
        'student no',
        'student number',
        'alumni'
    ];
    
    $facultyKeywords = [
        'faculty',
        'employee',
        'personnel',
        'staff',
        'teacher',
        'professor',
        'instructor',
        'employee no',
        'emp no',
        'employee id'
    ];
    
    $lcupGeneral = [
        'la consolacion',
        'lcup',
        'consolacion university',
        'la consolacion university philippines',
        'malolos'
    ];
    
    $seniorKeywords = [
        'senior',
        'senior citizen',
        'osca',
        'office of senior citizen',
        'elderly',
        'lolo',
        'lola',
        'senior id',
        'sc id'
    ];
    
    $pwdKeywords = [
        'pwd',
        'person with disability',
        'disability',
        'persons with disabilities',
        'pwd id',
        'disabled'
    ];
    
    $result = [
        'valid' => false,
        'reason' => '',
        'matched_keywords' => [],
        'id_type' => ''
    ];
    
    switch ($discountType) {
        case 'lcupstudent':
            // Must have LCUP keywords AND student-specific keywords
            $lcupMatch = 0;
            $studentMatch = 0;
            
            foreach ($lcupGeneral as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $lcupMatch++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            foreach ($studentKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $studentMatch++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            // Check if it's actually a faculty/personnel ID (reject it)
            $facultyMatch = 0;
            foreach ($facultyKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $facultyMatch++;
                }
            }
            
            if ($facultyMatch > 0) {
                $result['valid'] = false;
                $result['reason'] = 'This appears to be a Faculty/Personnel ID, not a Student ID. Please upload a valid LCUP Student or Alumni ID.';
                $result['id_type'] = 'faculty';
            } else if ($lcupMatch >= 1 && $studentMatch >= 1) {
                $result['valid'] = true;
                $result['reason'] = 'Valid LCUP Student/Alumni ID approved.';
                $result['id_type'] = 'student';
            } else if ($lcupMatch >= 1 && $studentMatch === 0) {
                $result['valid'] = false;
                $result['reason'] = 'This appears to be a LCUP ID but student-specific information is not clear. Please ensure it\'s a Student or Alumni ID.';
            } else {
                $result['valid'] = false;
                $result['reason'] = 'This does not appear to be a LCUP Student/Alumni ID. Please upload a valid LCUP Student or Alumni ID card.';
            }
            break;
            
        case 'lcuppersonnel':
            // Must have LCUP keywords AND faculty/personnel keywords
            $lcupMatch = 0;
            $facultyMatch = 0;
            
            foreach ($lcupGeneral as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $lcupMatch++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            foreach ($facultyKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $facultyMatch++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            // Check if it's actually a student ID (reject it)
            $studentMatch = 0;
            foreach ($studentKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $studentMatch++;
                }
            }
            
            if ($studentMatch > 0) {
                $result['valid'] = false;
                $result['reason'] = 'This appears to be a Student ID, not a Faculty/Personnel ID. Please upload a valid LCUP Employee/Faculty ID.';
                $result['id_type'] = 'student';
            } else if ($lcupMatch >= 1 && $facultyMatch >= 1) {
                $result['valid'] = true;
                $result['reason'] = 'Valid LCUP Faculty/Personnel ID approved.';
                $result['id_type'] = 'faculty';
            } else if ($lcupMatch >= 1 && $facultyMatch === 0) {
                $result['valid'] = false;
                $result['reason'] = 'This appears to be a LCUP ID but personnel-specific information is not clear. Please ensure it\'s a Faculty or Employee ID.';
            } else {
                $result['valid'] = false;
                $result['reason'] = 'This does not appear to be a LCUP Faculty/Personnel ID. Please upload a valid LCUP Employee or Faculty ID card.';
            }
            break;
            
        case 'pwd_senior':
            // Must have EITHER senior OR PWD keywords (not LCUP keywords)
            $seniorMatch = 0;
            $pwdMatch = 0;
            $lcupMatch = 0;
            
            foreach ($seniorKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $seniorMatch++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            foreach ($pwdKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $pwdMatch++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            // Check if it's actually a LCUP ID (reject it)
            foreach ($lcupGeneral as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $lcupMatch++;
                }
            }
            
            if ($lcupMatch > 0) {
                $result['valid'] = false;
                $result['reason'] = 'This appears to be a LCUP ID, not a Senior/PWD ID. Please upload a valid government-issued Senior Citizen or PWD ID.';
                $result['id_type'] = 'lcup';
            } else if ($seniorMatch >= 1) {
                $result['valid'] = true;
                $result['reason'] = 'Valid Senior Citizen ID approved.';
                $result['id_type'] = 'senior';
            } else if ($pwdMatch >= 1) {
                $result['valid'] = true;
                $result['reason'] = 'Valid PWD ID approved.';
                $result['id_type'] = 'pwd';
            } else {
                $result['valid'] = false;
                $result['reason'] = 'This does not appear to be a Senior Citizen or PWD ID. Please upload a valid government-issued Senior or PWD ID card.';
            }
            break;
            
        default:
            $result['valid'] = true;
            $result['reason'] = 'Unknown discount type - accepted by default';
    }
    
    return $result;
}

// Process the uploaded file
try {
    $extractedText = '';
    
    if ($fileType === 'application/pdf') {
        $extractedText = extractTextFromPDF($file['tmp_name']);
    } else {
        $extractedText = extractTextFromImage($file['tmp_name']);
    }
    
    // If no text could be extracted, accept with manual review flag
    if (empty($extractedText)) {
        // Provide helpful message based on discount type
        $manualMsg = 'ID uploaded successfully. ';
        switch ($discountType) {
            case 'lcuppersonnel':
                $manualMsg .= 'LCUP Personnel discount will be verified by admin.';
                break;
            case 'lcupstudent':
                $manualMsg .= 'LCUP Student/Alumni discount will be verified by admin.';
                break;
            case 'pwd_senior':
                $manualMsg .= 'PWD/Senior Citizen discount will be verified by admin.';
                break;
            default:
                $manualMsg .= 'Discount will be verified by admin.';
        }
        
        echo json_encode([
            'success' => true,
            'valid' => true,
            'reason' => $manualMsg,
            'manual_review' => true,
            'warning' => 'Automatic OCR validation not available. Your ID will be manually verified.',
            'matched_keywords' => []
        ]);
        exit;
    }
    
    // Validate the extracted text
    $validation = validateDiscountProof($extractedText, $discountType);
    
    echo json_encode([
        'success' => true,
        'valid' => $validation['valid'],
        'reason' => $validation['reason'],
        'matched_keywords' => $validation['matched_keywords'],
        'id_type' => $validation['id_type'],
        'extracted_text_length' => strlen($extractedText)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing file: ' . $e->getMessage()
    ]);
}
