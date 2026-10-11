<?php
session_start();

// Block framing, sniffing, and any script that isn't our own app.js
header("Content-Security-Policy: default-src 'none'; style-src 'self'; script-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// auto log out after 30 minutes of inactivity
if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > 1800) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_active'] = time();

function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$username  = !empty($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['email'];
$email     = $_SESSION['email'];
$loginTime = $_SESSION['login_time'] ?? time();

$initial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($username, 0, 1))
    : strtoupper(substr($username, 0, 1));

$hour = (int) date('G');
$greeting = $hour < 12 ? "Good morning" : ($hour < 18 ? "Good afternoon" : "Good evening");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome, <?php echo h($username); ?></title>

    <link rel="stylesheet" href="landing.css">
</head>

<body>
<div class="shell">

    <nav class="nav">
        <div class="brand">ITPC 6</div>
        <a href="logout.php" class="btn btn-ghost">Log out</a>
    </nav>

    <header class="hero">
        <div class="avatar"><?php echo h($initial); ?></div>
        <div class="greeting"><?php echo h($greeting); ?></div>
        <h1 class="name"><?php echo h($username); ?></h1>
        <p class="sub">You have successfully logged in to your account.</p>
        <span class="status-pill">Logged in</span>
    </header>

    <section class="stats">
        <div class="stat"><span>Username</span><b><?php echo h($username); ?></b></div>
        <div class="stat"><span>Email</span><b><?php echo h($email); ?></b></div>
        <div class="stat"><span>Login time</span><b><?php echo h(date('M j, Y g:i A', $loginTime)); ?></b></div>
    </section>

    <div class="section-head">
        <h2>Secure XSS Training Lab</h2>
    </div>

    <div class="notice">
        This page treats all user input as text. It does not intentionally execute JavaScript.
    </div>

    <section class="card">
        <h3>Safe Reflected-Style Output</h3>

        <form id="messageForm">
            <label for="messageInput">Enter a message</label>
            <textarea id="messageInput" maxlength="500"></textarea>
            <button type="submit" class="btn">Display Message</button>
        </form>

        <div id="messageOutput" class="output"></div>
    </section>

    <section class="card">
        <h3>Safe DOM Output</h3>

        <label for="domInput">Enter another message</label>
        <textarea id="domInput" maxlength="500"></textarea>
        <button id="domButton" type="button" class="btn">Display Safely</button>

        <div id="domOutput" class="output"></div>
    </section>

    <section class="card">
        <h3>Validation Result</h3>
        <p id="status" class="success">No input has been submitted yet.</p>
    </section>

</div>

<script src="app.js" defer></script>
</body>
</html>