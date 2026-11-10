<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $id   = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    // Validate ID
    if ($id <= 0) {
        $errors[] = "Invalid brand ID";
    }

    // Validate brand name
    if (empty($name)) {
        $errors[] = "Brand name is required";
    }

    // Check if brand exists
    $check_exists = mysqli_query($conn, "SELECT id FROM brands WHERE id = {$id} LIMIT 1");
    if (!$check_exists || mysqli_num_rows($check_exists) === 0) {
        $errors[] = "Brand not found";
    }

    // Return if validation errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../brands.php");
        exit;
    }

    $escaped_name = mysqli_real_escape_string($conn, $name);

    // Verify duplication (excluding current brand)
    $check_duplicate = mysqli_query($conn, "SELECT id FROM brands WHERE name = '{$escaped_name}' AND id != {$id} LIMIT 1");
    if (mysqli_num_rows($check_duplicate) > 0) {
        $errors[] = "Brand '{$name}' already exists";
        $_SESSION['errors'] = $errors;
        header("Location: ../brands.php");
        exit;
    }

    // Update brand
    $update_query = "UPDATE brands SET name = '{$escaped_name}' WHERE id = {$id}";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Brand '{$name}' updated successfully";
    } else {
        $_SESSION['errors'] = ["Failed to update brand: " . mysqli_error($conn)];
    }

    header("Location: ../brands.php");
    exit;
} else {
    header("Location: ../brands.php");
    exit;
}

