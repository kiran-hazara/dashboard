<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $brand_name              = trim($_POST['brand_name'] ?? '');
    $helpline_phone          = trim($_POST['helpline_phone'] ?? '');
    $support_email           = trim($_POST['support_email'] ?? '');
    $currency_timezone       = trim($_POST['currency_timezone'] ?? 'PKR (Pakistan Standard Time - PKT)');
    $delivery_fee            = floatval($_POST['delivery_fee'] ?? 0);
    $free_delivery_threshold = floatval($_POST['free_delivery_threshold'] ?? 0);
    $delivery_notice         = trim($_POST['delivery_notice'] ?? '');
    $exchange_policy         = trim($_POST['exchange_policy'] ?? '');

    // Validation
    if (empty($brand_name)) {
        $errors[] = "Brand Name is required.";
    }

    if (empty($support_email) || !filter_var($support_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid Support Email is required.";
    }

    if (empty($helpline_phone)) {
        $errors[] = "Helpline Phone number is required.";
    }

    if ($delivery_fee < 0) {
        $errors[] = "Standard Delivery Fee cannot be negative.";
    }

    if ($free_delivery_threshold < 0) {
        $errors[] = "Free Delivery Threshold cannot be negative.";
    }

    // Return back if validation errors
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../settings.php");
        exit;
    }

    // Prepare settings array matching database schema: setting_group, setting_key, setting_value, description
    $settings_to_save = [
        ['general', 'brand_name', $brand_name, 'Store Brand Name'],
        ['general', 'helpline_phone', $helpline_phone, 'Support Phone Number'],
        ['general', 'support_email', $support_email, 'Support Email Address'],
        ['general', 'currency_timezone', $currency_timezone, 'Store Currency & Timezone'],
        ['logistics', 'delivery_fee', (string)$delivery_fee, 'Standard Nationwide Delivery Fee (PKR)'],
        ['logistics', 'free_delivery_threshold', (string)$free_delivery_threshold, 'Free Delivery Order Minimum (PKR)'],
        ['logistics', 'delivery_notice', $delivery_notice, 'Delivery Transit Estimates Notice'],
        ['policy', 'exchange_policy', $exchange_policy, 'Customer Exchange & Warranty Policy']
    ];

    $has_error = false;
    foreach ($settings_to_save as $item) {
        $group = mysqli_real_escape_string($conn, $item[0]);
        $key   = mysqli_real_escape_string($conn, $item[1]);
        $val   = mysqli_real_escape_string($conn, $item[2]);
        $desc  = mysqli_real_escape_string($conn, $item[3]);

        $query = "INSERT INTO store_settings (setting_group, setting_key, setting_value, description) 
                  VALUES ('{$group}', '{$key}', '{$val}', '{$desc}') 
                  ON DUPLICATE KEY UPDATE setting_value = '{$val}', description = '{$desc}'";

        if (!mysqli_query($conn, $query)) {
            $has_error = true;
            $errors[] = "Failed to save {$key}: " . mysqli_error($conn);
            break;
        }
    }

    if ($has_error) {
        $_SESSION['errors'] = $errors;
    } else {
        $_SESSION['success'] = "Store configuration settings successfully updated!";
    }

    header("Location: ../settings.php");
    exit;
} else {
    header("Location: ../settings.php");
    exit;
}
