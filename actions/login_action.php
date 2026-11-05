<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];
if ($_SERVER['REQUEST_METHOD']  == "POST") {

    $email        = trim($_POST['email']) ?? '';
    $password     = $_POST['password'] ?? '';


    // email and password fields are requried
    if (empty($email) || empty($password)) {
        $errors[] = "Email and Password are required";
    }

    // validate email
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $errors[] = "Email address is incorrect";
    }

    // errors then return back to form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: /../login.php");
        exit;
    }


    // cehck email is already used or not ?
    $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        $errors[] = "This email is not registered";
        $_SESSION['errors'] = $errors;
        header("Location: /../login.php");
        exit;
    }

    $user = mysqli_fetch_assoc($result);


    // now we are ready to register the user
    if (password_verify($password, $user['password_hash']) == false) {
        $errors[] = "Wrong credentials";
        $_SESSION['errors'] = $errors;
        header("Location: /../login.php");
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    header("Location: /../index.php");
    exit;
}