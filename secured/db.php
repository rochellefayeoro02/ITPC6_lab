<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";          // renamed from $pass so it can't clash with form variables
$db_name = "sec";

// throw exceptions on database errors so we can handle them cleanly
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // log the real reason, show visitors nothing sensitive
    error_log("DB connection failed: " . $e->getMessage());
    http_response_code(500);
    die("Service unavailable. Please try again later.");
}
?>