<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['errors'] = ["Invalid category ID for deletion."];
    header("Location: ../categories.php");
    exit;
}

// Check category exists
$check = mysqli_query($conn, "SELECT id, name FROM categories WHERE id = {$id} LIMIT 1");
if (!$check || mysqli_num_rows($check) === 0) {
    $_SESSION['errors'] = ["Category not found or already deleted."];
    header("Location: ../categories.php");
    exit;
}

$category = mysqli_fetch_assoc($check);
$cat_name = $category['name'];

// Safe detachment: update any products referencing this category to NULL
@mysqli_query($conn, "UPDATE products SET category_id = NULL WHERE category_id = {$id}");

// Delete the category
if (mysqli_query($conn, "DELETE FROM categories WHERE id = {$id}")) {
    $_SESSION['success'] = "Category '{$cat_name}' deleted successfully!";
} else {
    $_SESSION['errors'] = ["Failed to delete category: " . mysqli_error($conn)];
}

header("Location: ../categories.php");
exit;

