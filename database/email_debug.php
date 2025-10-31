<?php
/**
 * email_debug.php
 *
 * Small helper utilities for live email debugging.
 * Usage:
 *  - Include/require this file where you want to produce or capture email debug logs.
 *  - Control by environment variable: EMAIL_DEBUG=true
 *  - Optional log path: EMAIL_DEBUG_LOG=/full/path/to/email_debug.log
 *
 * Functions provided:
 *  - email_debug_enabled(): bool
 *  - email_debug_log_path(): string
 *  - email_debug_write(string $message): void
 *  - email_debug_phpmailer_callback(string $str, int $level): void  (use as PHPMailer Debugoutput)
 */

if (!function_exists('email_debug_enabled')) {
    function _email_debug_env($key, $default = null) {
        $v = getenv($key);
        if ($v === false) {
            // fallback to $_ENV
            return $_ENV[$key] ?? $default;
        }
        return $v;
    }

    function email_debug_enabled() {
        $val = _email_debug_env('EMAIL_DEBUG', false);
        if (is_bool($val)) return $val;
        if (is_string($val)) {
            $lower = strtolower($val);
            return in_array($lower, ['1','true','yes','on'], true);
        }
        return (bool)$val;
    }

    function email_debug_log_path() {
        $path = _email_debug_env('EMAIL_DEBUG_LOG', null);
        if (!empty($path)) return $path;
        // Default to project-level logs directory
        return __DIR__ . '/../logs/email_debug.log';
    }

    function email_debug_write($message) {
        $file = email_debug_log_path();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $entry = date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
        // Best-effort append to file and to PHP error log
        @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
        error_log('EmailDebug: ' . $message);
    }

    function email_debug_phpmailer_callback($str, $level) {
        // Format and write PHPMailer debug lines
        $msg = sprintf('[PHPMailer][level %d] %s', $level, trim($str));
        email_debug_write($msg);
    }
}
