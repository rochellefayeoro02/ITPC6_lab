<?php
session_start();

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// already logged in -> straight to the landing page
if (isset($_SESSION['email'])) {
    header("Location: landing.php");
    exit();
}

require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, userName, email, password FROM form WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']     = $user['id'];
            $_SESSION['email']       = $user['email'];
            $_SESSION['username']    = $user['userName'];
            $_SESSION['login_time']  = time();
            $_SESSION['last_active'] = time();

            header("Location: landing.php");
            exit();
        }

        $error = "Invalid email or password.";
    }
}

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
$loggedOut  = isset($_GET['loggedout'])  && $_GET['loggedout']  === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<div class="auth-wrap">
    <div class="auth">

        <div class="tabs">
            <a href="login.php" class="active">Log in</a>
            <a href="index.html">Register</a>
        </div>

        <h1>Welcome back</h1>
        <p class="lead">Log in with your email and password.</p>

        <?php if ($registered && $error === ""): ?>
            <div class="alert alert-ok">Registration successful! Please log in.</div>
        <?php endif; ?>

        <?php if ($loggedOut && $error === ""): ?>
            <div class="alert alert-ok">You have been logged out.</div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="on">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="you@example.com"
                   maxlength="254" autocomplete="email"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password"
                   autocomplete="current-password" required>

            <button type="submit" class="btn">Log In</button>
        </form>

        <div class="switch">
            No account? <a href="index.html">Register</a>
        </div>

    </div>
</div>
</body>
</html>