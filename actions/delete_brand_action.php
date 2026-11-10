<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['errors'] = ["Invalid brand ID for deletion."];
    header("Location: ../brands.php");
    exit;
}

// Check brand exists
$check = mysqli_query($conn, "SELECT id, name FROM brands WHERE id = {$id} LIMIT 1");
if (!$check || mysqli_num_rows($check) === 0) {
    $_SESSION['errors'] = ["Brand not found or already deleted."];
    header("Location: ../brands.php");
    exit;
}

$brand = mysqli_fetch_assoc($check);
$brand_name = $brand['name'];

// Safe detachment: update any products referencing this brand to NULL
@mysqli_query($conn, "UPDATE products SET brand_id = NULL WHERE brand_id = {$id}");

// Delete the brand
if (mysqli_query($conn, "DELETE FROM brands WHERE id = {$id}")) {
    $_SESSION['success'] = "Brand '{$brand_name}' deleted successfully!";
} else {
    $_SESSION['errors'] = ["Failed to delete brand: " . mysqli_error($conn)];
}

header("Location: ../brands.php");
exit;

