<?php
/**
 * Error Handler Class
 * Centralized error handling and logging system
 * 
 * @package BarCIE
 * @version 1.0.0
 */

class ErrorHandler
{

    private static $logPath = __DIR__ . '/../logs/';
    private static $isProduction = false;
    private static $initialized = false;

    /**
     * Initialize error handler
     */
    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        // Check environment
        $appEnv = getenv('APP_ENV') ?: 'development';
        self::$isProduction = ($appEnv === 'production');

        // Set error reporting based on environment
        if (self::$isProduction) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            ini_set('log_errors', '1');
        }

        // Create logs directory if it doesn't exist
        if (!is_dir(self::$logPath)) {
            @mkdir(self::$logPath, 0755, true);
        }

        // Set custom error log path
        ini_set('error_log', self::$logPath . 'php_errors.log');

        // Set custom error handlers
        set_error_handler([self::class, 'errorHandler']);
        set_exception_handler([self::class, 'exceptionHandler']);
        register_shutdown_function([self::class, 'shutdownHandler']);

        self::$initialized = true;
    }

    /**
     * Custom error handler
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // Don't handle suppressed errors (@)
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = self::getErrorType($errno);

        // Log the error
        self::logError([
            'type' => $errorType,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);

        // Throw exception for fatal errors
        if ($errno === E_ERROR || $errno === E_USER_ERROR) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        return true;
    }

    /**
     * Custom exception handler
     */
    public static function exceptionHandler($exception)
    {
        // Log the exception
        self::logError([
            'type' => 'Exception',
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ]);

        // Send appropriate response
        self::sendErrorResponse($exception->getMessage(), 500);
    }

    /**
     * Shutdown handler for fatal errors
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Log the fatal error
            self::logError([
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Send error response
            self::sendErrorResponse('A critical error occurred. Please try again later.', 500);
        }
    }

    /**
     * Log error to file
     */
    private static function logError($error)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logFile = self::$logPath . 'app_errors.log';

        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\n",
            $timestamp,
            $error['type'],
            $error['message'],
            $error['file'] ?? 'unknown',
            $error['line'] ?? 0
        );

        // Add trace in development mode
        if (!self::$isProduction && isset($error['trace'])) {
            $logMessage .= "Stack trace:\n";
            foreach ($error['trace'] as $i => $trace) {
                $logMessage .= sprintf(
                    "#%d %s(%d): %s\n",
                    $i,
                    $trace['file'] ?? '[internal function]',
                    $trace['line'] ?? 0,
                    isset($trace['class']) ? $trace['class'] . $trace['type'] . $trace['function'] : $trace['function']
                );
            }
        }

        $logMessage .= "\n";

        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Send JSON error response
     */
    public static function sendErrorResponse($message, $code = 500, $additionalData = [])
    {
        // Check if headers already sent
        if (headers_sent()) {
            return;
        }

        http_response_code($code);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'error' => self::getHttpStatusText($code),
            'message' => $message
        ];

        // Add stack trace in development mode
        if (!self::$isProduction && getenv('APP_DEBUG') === 'true') {
            $response['debug'] = [
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ];
        }

        // Merge additional data
        if (!empty($additionalData)) {
            $response = array_merge($response, $additionalData);
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send JSON success response
     */
    public static function sendSuccessResponse($message, $data = [], $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Get error type name
     */
    private static function getErrorType($errno)
    {
        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        return $errorTypes[$errno] ?? 'Unknown Error';
    }

    /**
     * Get HTTP status text
     */
    private static function getHttpStatusText($code)
    {
        $statusTexts = [
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];

        return $statusTexts[$code] ?? 'Error';
    }

    /**
     * Log custom message
     */
    public static function log($message, $level = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logFile = self::$logPath . 'app.log';

        $logMessage = sprintf("[%s] [%s] %s\n", $timestamp, $level, $message);
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
