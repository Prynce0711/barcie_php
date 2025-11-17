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
    
    // Define keywords for each discount type
    $lcupKeywords = [
        'la consolacion',
        'lcup',
        'consolacion university',
        'la consolacion university philippines',
        'employee',
        'personnel',
        'student',
        'alumni',
        'staff'
    ];
    
    $seniorKeywords = [
        'senior',
        'senior citizen',
        'osca',
        'office of senior citizen',
        'pwd',
        'person with disability',
        'disability',
        'senior citizen id',
        'senior id'
    ];
    
    $result = [
        'valid' => false,
        'confidence' => 0,
        'reason' => '',
        'matched_keywords' => []
    ];
    
    switch ($discountType) {
        case 'lcuppersonnel':
        case 'lcupstudent':
            // Check for LCUP-related keywords
            $matchCount = 0;
            foreach ($lcupKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $matchCount++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            if ($matchCount >= 2) {
                $result['valid'] = true;
                $result['confidence'] = min(100, $matchCount * 30);
                $result['reason'] = 'Detected LCUP-related keywords in ID: ' . implode(', ', $result['matched_keywords']);
            } else if ($matchCount === 1) {
                $result['valid'] = true;
                $result['confidence'] = 50;
                $result['reason'] = 'Detected possible LCUP ID (confidence: low). Keywords found: ' . implode(', ', $result['matched_keywords']);
            } else {
                $result['reason'] = 'No LCUP keywords detected in the ID. Please ensure you upload a valid LCUP personnel or student ID.';
            }
            break;
            
        case 'pwd_senior':
            // Check for senior/PWD keywords
            $matchCount = 0;
            foreach ($seniorKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $matchCount++;
                    $result['matched_keywords'][] = $keyword;
                }
            }
            
            if ($matchCount >= 1) {
                $result['valid'] = true;
                $result['confidence'] = min(100, $matchCount * 40);
                $result['reason'] = 'Detected senior/PWD keywords in ID: ' . implode(', ', $result['matched_keywords']);
            } else {
                $result['reason'] = 'No senior citizen or PWD keywords detected. Please upload a valid government-issued senior or PWD ID.';
            }
            break;
            
        default:
            $result['valid'] = true;
            $result['confidence'] = 0;
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
            'confidence' => 50,
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
        'confidence' => $validation['confidence'],
        'reason' => $validation['reason'],
        'matched_keywords' => $validation['matched_keywords'],
        'extracted_text_length' => strlen($extractedText)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing file: ' . $e->getMessage()
    ]);
}
