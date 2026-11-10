<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id         = intval($_SESSION['user_id'] ?? 0);
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($admin_id <= 0) {
        $errors[] = "Invalid session. Please log in again.";
        $_SESSION['errors'] = $errors;
        header("Location: ../login.php");
        exit;
    }

    // Name validations
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    } elseif (strlen($name) > 120) {
        $errors[] = "Full Name cannot exceed 120 characters.";
    }

    // Email validations
    if (empty($email)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    } elseif (strlen($email) > 191) {
        $errors[] = "Email Address cannot exceed 191 characters.";
    } else {
        // Check if email already used by another admin
        $escaped_email = mysqli_real_escape_string($conn, $email);
        $check_sql = "SELECT id FROM admins WHERE email = '{$escaped_email}' AND id != {$admin_id} LIMIT 1";
        $check_res = mysqli_query($conn, $check_sql);
        if ($check_res && mysqli_num_rows($check_res) > 0) {
            $errors[] = "Email address '{$email}' is already in use by another administrator.";
        }
    }

    // Password validations (only if user entered a new password)
    $update_password = false;
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "New password and confirm password do not match.";
        } else {
            $update_password = true;
        }
    }

    // If validation fails, redirect back with errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../profile.php");
        exit;
    }

    // Prepare SQL update
    $escaped_name  = mysqli_real_escape_string($conn, $name);
    $escaped_email = mysqli_real_escape_string($conn, $email);

    if ($update_password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $update_sql = "UPDATE admins SET 
            name = '{$escaped_name}', 
            email = '{$escaped_email}', 
            password_hash = '{$password_hash}' 
            WHERE id = {$admin_id}";
    } else {
        $update_sql = "UPDATE admins SET 
            name = '{$escaped_name}', 
            email = '{$escaped_email}' 
            WHERE id = {$admin_id}";
    }

    if (mysqli_query($conn, $update_sql)) {
        // Update session values if stored
        if (isset($_SESSION['user_name'])) {
            $_SESSION['user_name'] = $name;
        }
        if (isset($_SESSION['user_email'])) {
            $_SESSION['user_email'] = $email;
        }

        $_SESSION['success'] = "Profile details updated successfully!";
        header("Location: ../profile.php");
        exit;
    } else {
        $_SESSION['errors'] = ["Failed to update profile: " . mysqli_error($conn)];
        header("Location: ../profile.php");
        exit;
    }
} else {
    header("Location: ../profile.php");
    exit;
}