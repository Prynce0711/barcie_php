<?php
session_start();
include 'db_connect.php';

// Ensure form submitted
if (!isset($_POST['action'])) {
    die("Invalid request.");
}

$action = $_POST['action'];

// ---------- SIGNUP ----------
if ($action === 'signup') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "❌ Passwords do not match. Please try again.";
        exit;
    }

    // Check if username or email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    if (!$stmt) {
        echo "Something went wrong. Please try again later.";
        exit;
    }
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo "⚠️ Username or email already registered.";
        exit;
    }
    $stmt->close();

    // Insert new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo "Something went wrong. Please try again later.";
        exit;
    }
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "✅ Signup successful! You can now login.";
    } else {
        echo "⚠️ Error creating account. Please try again.";
    }

    $stmt->close();
    $conn->close();
    exit;
}

// ---------- LOGIN ----------
elseif ($action === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username=?");
    if (!$stmt) {
        echo "Something went wrong. Please try again later.";
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $user, $hashed_password);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            // Store session
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user;

            $stmt->close();
            $conn->close();

            // ✅ Redirect to Guest.php
            header("Location: ../Guest.php");
            exit;
        } else {
            echo "❌ Incorrect password. Please try again.";
        }
    } else {
        echo "⚠️ User not found. Please check your username.";
    }

    $stmt->close();
    $conn->close();
}




// ---------- UPDATE PROFILE ----------
elseif ($action === 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to update your profile.");
    }

    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $username; // update session
        header("Location: ../Guest.php?updated=5");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    exit;
}




// ---------- INVALID ACTION ----------
else {
    $conn->close();
    die("Invalid action.");
}
?>
