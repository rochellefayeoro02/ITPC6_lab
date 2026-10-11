<?php
// Only accept the registration form
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["register"])) {
    header("Location: index.html");
    exit();
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

require "db.php";

// Small styled error page (no raw errors, always escaped)
function fail($message, $code = 400) {
    http_response_code($code);
    $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<title>Registration error</title><style>'
       . 'body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0b0b0b;color:#f2f2f2;font-family:"Segoe UI",Arial,sans-serif}'
       . '.box{width:100%;max-width:420px;background:#141414;border:1px solid #262626;border-radius:14px;padding:34px}'
       . 'h1{font-size:22px;margin:0 0 12px}'
       . 'p{padding:12px 14px;border-left:4px solid #ff6b6b;background:rgba(255,107,107,.08);color:#ffb3b3;font-size:14px;border-radius:8px}'
       . 'a{display:block;margin-top:22px;padding:13px;background:#f2f2f2;color:#000;border-radius:8px;text-align:center;font-weight:600;font-size:14px;text-decoration:none}'
       . '</style></head><body><div class="box"><h1>Registration error</h1><p>' . $safe . '</p>'
       . '<a href="index.html" onclick="history.back();return false;">Go back</a></div></body></html>';
    exit();
}

$userName         = trim($_POST["userName"] ?? '');
$email            = strtolower(trim($_POST["email"] ?? ''));
$password         = $_POST["password"] ?? '';
$confirm_password = $_POST["confirm_password"] ?? '';

// ---------- validation ----------
if ($userName === '' || $email === '' || $password === '' || $confirm_password === '') {
    fail("All fields are required.");
}

if (!preg_match('/^[\p{L}\p{N}_. -]{3,30}$/u', $userName)) {
    fail("Username must be 3-30 characters (letters, numbers, spaces, . _ -).");
}

if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail("Please enter a valid email address.");
}

if (strlen($password) < 8 || strlen($password) > 72) {
    fail("Password must be 8-72 characters long.");
}

if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    fail("Password must contain at least one letter and one number.");
}

if ($password !== $confirm_password) {
    fail("Passwords do not match.");
}

// ---------- save ----------
try {
    $check = $conn->prepare("SELECT id FROM form WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $check->close();
        fail("Email already exists.");
    }
    $check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO form (userName, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $userName, $email, $hashed_password);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    header("Location: login.php?registered=1");
    exit();

} catch (mysqli_sql_exception $e) {
    // 1062 = duplicate entry (two people registering the same email at once)
    if ((int) $e->getCode() === 1062) {
        fail("Email already exists.");
    }
    error_log("Registration failed: " . $e->getMessage());
    fail("Something went wrong. Please try again later.", 500);
}
?>