<?php
/**
 * CSRF Protection Class
 * Provides Cross-Site Request Forgery protection for forms and AJAX requests
 * 
 * Usage:
 * - Generate token: CSRF::generateToken()
 * - Validate token: CSRF::validateToken($_POST['csrf_token'])
 * - Get token field HTML: CSRF::getTokenField()
 * 
 * @package BarCIE
 * @version 1.0.0
 */

class CSRF
{

    /**
     * Token name in session and forms
     */
    private static $tokenName = 'csrf_token';

    /**
     * Token expiry time in seconds (default: 1 hour)
     */
    private static $tokenExpiry = 3600;

    /**
     * Initialize CSRF protection
     * Call this at the start of your application
     */
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load token expiry from environment
        $envExpiry = getenv('CSRF_TOKEN_EXPIRY');
        if ($envExpiry && is_numeric($envExpiry)) {
            self::$tokenExpiry = (int) $envExpiry;
        }
    }

    /**
     * Generate a new CSRF token
     * 
     * @param bool $forceNew Force generation of new token
     * @return string The generated token
     */
    public static function generateToken($forceNew = false)
    {
        self::init();

        // Check if valid token exists
        if (!$forceNew && isset($_SESSION[self::$tokenName]) && isset($_SESSION[self::$tokenName . '_time'])) {
            $tokenAge = time() - $_SESSION[self::$tokenName . '_time'];

            // Return existing token if still valid
            if ($tokenAge < self::$tokenExpiry) {
                return $_SESSION[self::$tokenName];
            }
        }

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$tokenName] = $token;
        $_SESSION[self::$tokenName . '_time'] = time();

        return $token;
    }

    /**
     * Validate CSRF token
     * 
     * @param string|null $token Token to validate (from POST/GET)
     * @param bool $removeAfterValidation Remove token after successful validation
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token = null, $removeAfterValidation = false)
    {
        self::init();

        // Get token from various sources
        if ($token === null) {
            // Check POST
            if (isset($_POST[self::$tokenName])) {
                $token = $_POST[self::$tokenName];
            }
            // Check GET
            elseif (isset($_GET[self::$tokenName])) {
                $token = $_GET[self::$tokenName];
            }
            // Check headers (for AJAX)
            elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        }

        // No token provided
        if (empty($token)) {
            error_log('CSRF: No token provided');
            return false;
        }

        // No token in session
        if (!isset($_SESSION[self::$tokenName])) {
            error_log('CSRF: No token in session');
            return false;
        }

        // Check token expiry
        if (isset($_SESSION[self::$tokenName . '_time'])) {
            $tokenAge = time() - $_SESSION[self::$tokenName . '_time'];
            if ($tokenAge > self::$tokenExpiry) {
                error_log('CSRF: Token expired');
                self::destroyToken();
                return false;
            }
        }

        // Validate token using timing-safe comparison
        $isValid = hash_equals($_SESSION[self::$tokenName], $token);

        if (!$isValid) {
            error_log('CSRF: Token mismatch');
        }

        // Remove token after validation if requested
        if ($isValid && $removeAfterValidation) {
            self::destroyToken();
        }

        return $isValid;
    }

    /**
     * Get HTML input field for CSRF token
     * 
     * @return string HTML input field
     */
    public static function getTokenField()
    {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Get CSRF token value
     * 
     * @return string Token value
     */
    public static function getToken()
    {
        return self::generateToken();
    }

    /**
     * Get token name
     * 
     * @return string Token name
     */
    public static function getTokenName()
    {
        return self::$tokenName;
    }

    /**
     * Destroy current token
     */
    public static function destroyToken()
    {
        self::init();
        unset($_SESSION[self::$tokenName]);
        unset($_SESSION[self::$tokenName . '_time']);
    }

    /**
     * Validate token or die with error
     * Convenience method for critical operations
     * 
     * @param string|null $token Token to validate
     * @param string $errorMessage Custom error message
     */
    public static function validateOrDie($token = null, $errorMessage = 'CSRF validation failed')
    {
        if (!self::validateToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'CSRF Validation Failed',
                'message' => $errorMessage
            ]);
            exit;
        }
    }

    /**
     * Get JavaScript code for AJAX requests
     * 
     * @return string JavaScript code
     */
    public static function getAjaxScript()
    {
        $token = self::generateToken();
        $tokenName = self::$tokenName;

        return <<<JS
<script>
// CSRF Token for AJAX requests
const CSRF_TOKEN = '{$token}';
const CSRF_TOKEN_NAME = '{$tokenName}';

// Add CSRF token to all fetch requests
const originalFetch = window.fetch;
window.fetch = function(url, options = {}) {
    // Add CSRF token to headers
    options.headers = options.headers || {};
    options.headers['X-CSRF-Token'] = CSRF_TOKEN;
    
    // Add CSRF token to FormData
    if (options.body instanceof FormData) {
        if (!options.body.has(CSRF_TOKEN_NAME)) {
            options.body.append(CSRF_TOKEN_NAME, CSRF_TOKEN);
        }
    }
    
    return originalFetch(url, options);
};

// Add CSRF token to all jQuery AJAX requests
if (typeof jQuery !== 'undefined') {
    jQuery.ajaxSetup({
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
        },
        data: function(data) {
            if (typeof data === 'object' && !(data instanceof FormData)) {
                data[CSRF_TOKEN_NAME] = CSRF_TOKEN;
            }
            return data;
        }
    });
}
</script>
JS;
    }
}
