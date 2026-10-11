<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// user input 
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
    <title>Welcome, <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="landing.css">
   
</head>
<body>
<div class="shell">

    <nav class="nav">
        <div class="brand">ITPC6</div>
        <a href="logout.php" class="btn btn-ghost">Log out</a>
    </nav>

    <header class="hero">
        <div class="avatar"><?php echo htmlspecialchars($initial); ?></div>
        <div class="greeting"><?php echo $greeting; ?></div>
        <h1 class="name"><?php echo htmlspecialchars($username); ?></h1>
        <p class="sub">You have successfully logged in to your account.</p>
        <span class="status">Logged in</span>
    </header>

    <section class="stats">
        <div class="stat"><span>Username</span><b><?php echo htmlspecialchars($username); ?></b></div>
        <div class="stat"><span>Email</span><b><?php echo htmlspecialchars($email); ?></b></div>
        <div class="stat"><span>Login time</span><b><?php echo date('M j, Y g:i A', $loginTime); ?></b></div>
    </section>

    <div class="section-head">
        <h2>XSS Training Lab</h2>
        <p>Compare how the same input behaves with unsafe and safe output.</p>
    </div>

    <div class="warning">
        For classroom use only. Run this page locally and never test it against systems you do not own.
    </div>

    <section class="card">
        <h3>Exercise A: Reflected XSS <span class="chip vuln">Vulnerable</span></h3>

        <form id="reflectedForm">
            <label for="reflectedInput">Enter a greeting</label>
            <textarea id="reflectedInput" placeholder="Type a message"></textarea>
            <button type="submit" class="btn">Display Message</button>
        </form>

        <div id="reflectedOutput" class="output"></div>
    </section>

    <section class="card">
        <h3>Exercise B: DOM XSS <span class="chip vuln">Vulnerable</span></h3>

        <label for="domInput">Enter a message</label>
        <textarea id="domInput" placeholder="Type a message"></textarea>
        <button id="domButton" class="btn">Display Message</button>

        <div id="domOutput" class="output"></div>
    </section>

    <section class="card">
        <h3>Exercise C: Safe Output <span class="chip safe">Safe</span></h3>

        <label for="safeInput">Enter a message</label>
        <textarea id="safeInput" placeholder="Type a message"></textarea>
        <button id="safeButton" class="btn">Display Safely</button>

        <div id="safeOutput" class="output"></div>
    </section>

</div>

<script>
    const reflectedForm = document.getElementById("reflectedForm");
    const reflectedInput = document.getElementById("reflectedInput");
    const reflectedOutput = document.getElementById("reflectedOutput");

    reflectedForm.addEventListener("submit", function (event) {
        event.preventDefault();
        reflectedOutput.innerHTML = "You entered: " + reflectedInput.value;
    });

    const domInput = document.getElementById("domInput");
    const domButton = document.getElementById("domButton");
    const domOutput = document.getElementById("domOutput");

    domButton.addEventListener("click", function () {
        domOutput.innerHTML = domInput.value;
    });

    const safeInput = document.getElementById("safeInput");
    const safeButton = document.getElementById("safeButton");
    const safeOutput = document.getElementById("safeOutput");

    safeButton.addEventListener("click", function () {
        safeOutput.textContent = safeInput.value;
    });
</script>

</body>
</html>