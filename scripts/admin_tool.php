<?php
// CLI utility to inspect or update admin users
// Usage:
//   php admin_tool.php check <username>
//   php admin_tool.php set-super <username> [password]
//   php admin_tool.php list

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

require __DIR__ . '/../database/db_connect.php';

$argv0 = array_shift($argv);
$action = strtolower($argv[0] ?? '');

function hasRoleColumn($conn) {
    $res = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'role'");
    return ($res && $res->num_rows > 0);
}

if ($action === 'check') {
    $username = $argv[1] ?? '';
    if (empty($username)) {
        echo "Usage: php admin_tool.php check <username>\n";
        exit(1);
    }

    $roleCol = hasRoleColumn($conn);
    if ($roleCol) {
        $stmt = $conn->prepare("SELECT id, username, email, role, created_at, last_login FROM admins WHERE username = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, created_at, last_login FROM admins WHERE username = ?");
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        echo "Admin found:\n";
        echo "  id: " . ($row['id'] ?? '') . "\n";
        echo "  username: " . ($row['username'] ?? '') . "\n";
        echo "  email: " . ($row['email'] ?? '') . "\n";
        echo "  created_at: " . ($row['created_at'] ?? '') . "\n";
        echo "  last_login: " . ($row['last_login'] ?? 'Never') . "\n";
        echo "  role: " . ($row['role'] ?? 'staff (default)') . "\n";
    } else {
        echo "Admin with username '{$username}' not found.\n";
    }
    $stmt->close();
    exit(0);
}

if ($action === 'list') {
    $roleCol = hasRoleColumn($conn);
    if ($roleCol) {
        $query = "SELECT id, username, email, role, created_at FROM admins ORDER BY id ASC";
    } else {
        $query = "SELECT id, username, email, created_at FROM admins ORDER BY id ASC";
    }
    $res = $conn->query($query);
    if (!$res) {
        echo "Failed to query admins: " . $conn->error . "\n";
        exit(1);
    }
    while ($row = $res->fetch_assoc()) {
        echo sprintf("%4s | %-20s | %-30s | %-15s | %s\n",
            $row['id'], $row['username'], $row['email'] ?? '-', $row['role'] ?? 'staff', $row['created_at'] ?? '');
    }
    exit(0);
}

if ($action === 'set-super') {
    $username = $argv[1] ?? '';
    $password = $argv[2] ?? '';

    if (empty($username)) {
        echo "Usage: php admin_tool.php set-super <username> [password]\n";
        exit(1);
    }

    $roleCol = hasRoleColumn($conn);
    if (!$roleCol) {
        echo "WARNING: `role` column not found in `admins` table. Run database/update_database.php to add it.\n";
        // we'll still attempt to update, but there's no role column
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $adminId = (int)$row['id'];
        // If password provided, update password
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $up = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $up->bind_param('si', $hash, $adminId);
            $ok = $up->execute();
            $up->close();
            if ($ok) echo "Password updated for {$username}\n";
            else echo "Failed to update password: " . $conn->error . "\n";
        }

        if ($roleCol) {
            $up2 = $conn->prepare("UPDATE admins SET role = 'super_admin' WHERE id = ?");
            $up2->bind_param('i', $adminId);
            if ($up2->execute()) {
                echo "User '{$username}' set to role 'super_admin'.\n";
            } else {
                echo "Failed to set role: " . $conn->error . "\n";
            }
            $up2->close();
        } else {
            echo "Cannot set role because `role` column is missing. Run database/update_database.php to add the column, then re-run this script.\n";
        }

    } else {
        // Create a new admin
        if (empty($password)) {
            // Generate a secure random password
            $password = bin2hex(random_bytes(6));
            echo "No password provided. Generated password: {$password}\n";
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($roleCol) {
            $ins = $conn->prepare("INSERT INTO admins (username, email, password, role, created_at) VALUES (?, ?, ?, 'super_admin', NOW())");
            $email = null;
            $ins->bind_param('sss', $username, $email, $hash);
        } else {
            $ins = $conn->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (?, ?, ? , NOW())");
            $email = null;
            $ins->bind_param('sss', $username, $email, $hash);
        }
        if ($ins->execute()) {
            echo "Created user '{$username}'" . ($roleCol ? " with role 'super_admin'" : " (role column missing)") . ".\n";
            echo "Password: {$password}\n";
        } else {
            echo "Failed to create user: " . $conn->error . "\n";
        }
        $ins->close();
    }
    $stmt->close();
    exit(0);
}

echo "Unknown action. Usage:\n";
echo "  php admin_tool.php check <username>\n";
echo "  php admin_tool.php list\n";
echo "  php admin_tool.php set-super <username> [password]\n";
exit(1);
