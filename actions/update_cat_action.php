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
        $errors[] = "Invalid category ID";
    }

    // Validate category name
    if (empty($name)) {
        $errors[] = "Category name is required";
    }

    // Check if category exists
    $check_exists = mysqli_query($conn, "SELECT id FROM categories WHERE id = {$id} LIMIT 1");
    if (!$check_exists || mysqli_num_rows($check_exists) === 0) {
        $errors[] = "Category not found";
    }

    // Return if validation errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../categories.php");
        exit;
    }

    $escaped_name = mysqli_real_escape_string($conn, $name);

    // Verify duplication (excluding current category)
    $check_duplicate = mysqli_query($conn, "SELECT id FROM categories WHERE name = '{$escaped_name}' AND id != {$id} LIMIT 1");
    if (mysqli_num_rows($check_duplicate) > 0) {
        $errors[] = "Category '{$name}' already exists";
        $_SESSION['errors'] = $errors;
        header("Location: ../categories.php");
        exit;
    }

    // Update category
    $update_query = "UPDATE categories SET name = '{$escaped_name}' WHERE id = {$id}";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Category '{$name}' updated successfully";
    } else {
        $_SESSION['errors'] = ["Failed to update category: " . mysqli_error($conn)];
    }

    header("Location: ../categories.php");
    exit;
} else {
    header("Location: ../categories.php");
    exit;
}

