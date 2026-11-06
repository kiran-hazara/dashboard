<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$product_id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($product_id <= 0) {
    $_SESSION['errors'] = ["Invalid Product ID for deletion."];
    header("Location: ../products.php");
    exit;
}

// Check product exists
$check = mysqli_query($conn, "SELECT id, name, image_url FROM products WHERE id = {$product_id} LIMIT 1");
if (!$check || mysqli_num_rows($check) === 0) {
    $_SESSION['errors'] = ["Product not found or already deleted."];
    header("Location: ../products.php");
    exit;
}

$product = mysqli_fetch_assoc($check);
$product_name = $product['name'];

// Remove physical image file if uploaded locally
if (!empty($product['image_url'])) {
    $img_path = __DIR__ . "/../" . $product['image_url'];
    if (file_exists($img_path) && is_file($img_path)) {
        @unlink($img_path);
    }
}

// 1. Delete associated inventory logs
@mysqli_query($conn, "DELETE FROM inventory_logs WHERE product_id = {$product_id}");

// 2. Delete product variants
mysqli_query($conn, "DELETE FROM product_variants WHERE product_id = {$product_id}");

// 3. Delete reviews if any table exists
@mysqli_query($conn, "DELETE FROM reviews WHERE product_id = {$product_id}");

// 4. Delete product
if (mysqli_query($conn, "DELETE FROM products WHERE id = {$product_id}")) {
    $_SESSION['success'] = "Footwear model '{$product_name}' deleted successfully!";
} else {
    $_SESSION['errors'] = ["Failed to delete product: " . mysqli_error($conn)];
}

header("Location: ../products.php");
exit;

