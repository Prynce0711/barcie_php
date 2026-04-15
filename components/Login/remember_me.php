<?php
/**
 * Persistent remember-me helpers for admin authentication.
 */

if (!defined('ADMIN_REMEMBER_ME_COOKIE')) {
    define('ADMIN_REMEMBER_ME_COOKIE', 'barcie_admin_remember');
}

if (!defined('ADMIN_REMEMBER_ME_TTL')) {
    define('ADMIN_REMEMBER_ME_TTL', 60 * 60 * 24 * 30); // 30 days
}

function remember_me_cookie_path(): string
{
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $normalized = trim(str_replace('\\', '/', $scriptName), '/');

    if ($normalized === '') {
        return '/';
    }

    $parts = explode('/', $normalized);
    $first = $parts[0] ?? '';

    if ($first !== '' && strpos($first, '.') === false) {
        return '/' . $first;
    }

    return '/';
}

function remember_me_cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => remember_me_cookie_path(),
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function remember_me_set_cookie(string $selector, string $validator, int $expires): void
{
    $value = $selector . ':' . $validator;
    setcookie(ADMIN_REMEMBER_ME_COOKIE, $value, remember_me_cookie_options($expires));
    $_COOKIE[ADMIN_REMEMBER_ME_COOKIE] = $value;
}

function remember_me_clear_cookie(): void
{
    setcookie(ADMIN_REMEMBER_ME_COOKIE, '', remember_me_cookie_options(time() - 3600));
    unset($_COOKIE[ADMIN_REMEMBER_ME_COOKIE]);
}

function remember_me_random_hex(int $bytes): string
{
    try {
        return bin2hex(random_bytes($bytes));
    } catch (Throwable $e) {
        $fallback = '';
        for ($i = 0; $i < $bytes; $i++) {
            $fallback .= chr(mt_rand(0, 255));
        }
        return bin2hex($fallback);
    }
}

function remember_me_parse_cookie(): ?array
{
    $raw = $_COOKIE[ADMIN_REMEMBER_ME_COOKIE] ?? '';

    if (!is_string($raw) || $raw === '') {
        return null;
    }

    $parts = explode(':', $raw, 2);
    if (count($parts) !== 2) {
        return null;
    }

    [$selector, $validator] = $parts;

    if (!preg_match('/^[a-f0-9]{24}$/', $selector)) {
        return null;
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $validator)) {
        return null;
    }

    return [
        'selector' => $selector,
        'validator' => $validator,
    ];
}

function remember_me_ensure_table(mysqli $conn): bool
{
    static $checked = null;

    if ($checked !== null) {
        return $checked;
    }

    $sql = "CREATE TABLE IF NOT EXISTS admin_remember_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        selector CHAR(24) NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_used_at DATETIME NULL,
        user_agent VARCHAR(255) NULL,
        ip_address VARCHAR(45) NULL,
        UNIQUE KEY uniq_selector (selector),
        KEY idx_admin_id (admin_id),
        KEY idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $checked = (bool) $conn->query($sql);
    return $checked;
}

function remember_me_load_admin_identity(mysqli $conn, int $adminId): ?array
{
    $hasRoleColumn = false;
    $colRes = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'role'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasRoleColumn = true;
    }

    if ($hasRoleColumn) {
        $stmt = $conn->prepare("SELECT username, role FROM admins WHERE id = ? LIMIT 1");
    } else {
        $stmt = $conn->prepare("SELECT username FROM admins WHERE id = ? LIMIT 1");
    }

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $adminId);
    $stmt->execute();

    if ($hasRoleColumn) {
        $stmt->bind_result($username, $role);
    } else {
        $stmt->bind_result($username);
        $role = 'staff';
    }

    $found = $stmt->fetch();
    $stmt->close();

    if (!$found || !is_string($username) || $username === '') {
        return null;
    }

    return [
        'id' => $adminId,
        'username' => $username,
        'role' => is_string($role) && $role !== '' ? $role : 'staff',
    ];
}

function remember_me_delete_selector(mysqli $conn, string $selector): void
{
    $stmt = $conn->prepare("DELETE FROM admin_remember_tokens WHERE selector = ?");
    if ($stmt) {
        $stmt->bind_param('s', $selector);
        $stmt->execute();
        $stmt->close();
    }
}

function remember_me_forget_current(mysqli $conn): void
{
    $cookie = remember_me_parse_cookie();

    if ($cookie !== null && remember_me_ensure_table($conn)) {
        remember_me_delete_selector($conn, $cookie['selector']);
    }

    remember_me_clear_cookie();
}

function remember_me_issue_token(mysqli $conn, int $adminId): bool
{
    if ($adminId <= 0) {
        return false;
    }

    if (!remember_me_ensure_table($conn)) {
        return false;
    }

    $selector = remember_me_random_hex(12);
    $validator = remember_me_random_hex(32);
    $tokenHash = hash('sha256', $validator);
    $expiresTs = time() + ADMIN_REMEMBER_ME_TTL;
    $expiresAt = date('Y-m-d H:i:s', $expiresTs);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

    $cleanup = $conn->prepare("DELETE FROM admin_remember_tokens WHERE admin_id = ? OR expires_at < NOW()");
    if ($cleanup) {
        $cleanup->bind_param('i', $adminId);
        $cleanup->execute();
        $cleanup->close();
    }

    $stmt = $conn->prepare(
        "INSERT INTO admin_remember_tokens (admin_id, selector, token_hash, expires_at, user_agent, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('isssss', $adminId, $selector, $tokenHash, $expiresAt, $userAgent, $ipAddress);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        remember_me_set_cookie($selector, $validator, $expiresTs);
    }

    return $ok;
}

function remember_me_restore_session(mysqli $conn): bool
{
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && !empty($_SESSION['admin_id'])) {
        return true;
    }

    $cookie = remember_me_parse_cookie();
    if ($cookie === null) {
        return false;
    }

    if (!remember_me_ensure_table($conn)) {
        remember_me_clear_cookie();
        return false;
    }

    $selector = $cookie['selector'];
    $validator = $cookie['validator'];

    $stmt = $conn->prepare(
        "SELECT id, admin_id, token_hash, expires_at
         FROM admin_remember_tokens
         WHERE selector = ?
         LIMIT 1"
    );

    if (!$stmt) {
        remember_me_clear_cookie();
        return false;
    }

    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $result = $stmt->get_result();
    $tokenRow = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$tokenRow) {
        remember_me_clear_cookie();
        return false;
    }

    $expiresAtTs = strtotime((string) ($tokenRow['expires_at'] ?? ''));
    if ($expiresAtTs === false || $expiresAtTs < time()) {
        remember_me_delete_selector($conn, $selector);
        remember_me_clear_cookie();
        return false;
    }

    $expectedHash = hash('sha256', $validator);
    $storedHash = (string) ($tokenRow['token_hash'] ?? '');

    if (!hash_equals($storedHash, $expectedHash)) {
        remember_me_delete_selector($conn, $selector);
        remember_me_clear_cookie();
        return false;
    }

    $adminId = (int) ($tokenRow['admin_id'] ?? 0);
    $identity = remember_me_load_admin_identity($conn, $adminId);

    if ($identity === null) {
        remember_me_delete_selector($conn, $selector);
        remember_me_clear_cookie();
        return false;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $identity['id'];
    $_SESSION['admin_username'] = $identity['username'];
    $_SESSION['admin_role'] = $identity['role'];

    $newValidator = remember_me_random_hex(32);
    $newHash = hash('sha256', $newValidator);
    $newExpiresTs = time() + ADMIN_REMEMBER_ME_TTL;
    $newExpiresAt = date('Y-m-d H:i:s', $newExpiresTs);
    $tokenId = (int) ($tokenRow['id'] ?? 0);
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

    if ($tokenId > 0) {
        $update = $conn->prepare(
            "UPDATE admin_remember_tokens
             SET token_hash = ?, expires_at = ?, last_used_at = NOW(), user_agent = ?, ip_address = ?
             WHERE id = ?"
        );

        if ($update) {
            $update->bind_param('ssssi', $newHash, $newExpiresAt, $userAgent, $ipAddress, $tokenId);
            $updated = $update->execute();
            $update->close();

            if ($updated) {
                remember_me_set_cookie($selector, $newValidator, $newExpiresTs);
            } else {
                remember_me_clear_cookie();
            }
        }
    }

    return true;
}
