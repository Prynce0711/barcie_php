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
        $_SESSION['signup_error'] = "Passwords do not match. Please try again.";
        header("Location: ../index.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    if (!$stmt) {
        $_SESSION['signup_error'] = "Something went wrong. Please try again later.";
        header("Location: ../index.php");
        exit;
    }
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['signup_error'] = "Username or email already registered.";
        if ($stmt) $stmt->close();
        $conn->close();
        header("Location: ../index.php");
        exit;
    }
    if ($stmt) $stmt->close();

    // Insert new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        $_SESSION['signup_error'] = "Something went wrong. Please try again later.";
        header("Location: ../index.php");
        exit;
    }
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Signup successful! You can now login.";
    } else {
        $_SESSION['signup_error'] = "Error creating account. Please try again.";
    }

    if ($stmt) $stmt->close();
    $conn->close();
    header("Location: ../index.php");
    exit;
}

// ---------- LOGIN ----------
elseif ($action === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username=?");
    if (!$stmt) {
        $_SESSION['login_error'] = "Something went wrong. Please try again later.";
        header("Location: ../index.php");
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $user, $hashed_password);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user;
            if ($stmt) $stmt->close();
            $conn->close();
            header("Location: ../Guest.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Incorrect password. Please try again.";
        }
    } else {
        $_SESSION['login_error'] = "Invalid username. Please try again.";
    }

    if ($stmt) $stmt->close();
    $conn->close();
    header("Location: ../index.php");
    exit;
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
        $_SESSION['username'] = $username;
        header("Location: ../Guest.php?updated=5");
        exit;
    } else {
        $_SESSION['profile_error'] = "Error updating profile. Please try again.";
        header("Location: ../Guest.php");
        exit;
    }

    if ($stmt) $stmt->close();
    $conn->close();
    exit;
}

// ---------- INVALID ACTION ----------
else {
    $conn->close();
    die("Invalid action.");
}
?>
