<?php
// Only accept the registration form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "uns");

$userName  = $_POST['userName'];
$email      = $_POST['email'];
$password   = $_POST['password'];
$pass       = $_POST['confirm_password'];

if ($password !== $pass) {
    die("Passwords do not match. <a href='index.html'>Go back</a>");
}

$sql = "INSERT INTO regis (userName, email, pass, password)
        VALUES ('$userName', '$email', '$pass', '$password')";

if (mysqli_query($conn, $sql)) {
    header("Location: login.php?registered=1");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>