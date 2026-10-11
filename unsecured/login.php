<?php

session_start();

$error = "";
$notice = "";

if (isset($_GET['loggedout']))  { $notice = "You have been logged out."; }
if (isset($_GET['registered'])) { $notice = "Account created. Please log in."; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn = new mysqli("localhost", "root", "", "uns");

    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {

        $error = "Please enter email and password.";

    } else {

        // VULNERABLE: user input concatenated straight into the query
        $sql = "SELECT * FROM regis
                WHERE email = '$email' AND password = '$password'";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'] ?? null;
            $_SESSION['email'] = $user['email'];

            // username the person typed when registering
            // Change 'userName' if your column in the regis table is named differently.
            $_SESSION['username'] = !empty($user['userName']) ? $user['userName'] : $user['email'];

            $_SESSION['login_time'] = time();

            header("Location: landing.php");
            exit();

        } else {
            // VULNERABLE: shows the raw email and the SQL error
            $error = "Invalid login for: $email <small>" . $conn->error . "</small>";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg: #070707; --panel: #0f0f0f; --panel-2: #151515; --line: #242424;
            --text: #f2f2f2; --muted: #8a8a8a; --ok: #3ddc84; --bad: #ff6b6b;
        }
        html { color-scheme: dark; }

        body {
            min-height: 100vh;
            background: var(--bg);
            background-image:
                radial-gradient(900px 520px at 50% -10%, rgba(255,255,255,.09), transparent 60%),
                radial-gradient(600px 400px at 100% 100%, rgba(255,255,255,.05), transparent 60%);
            background-attachment: fixed;
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* faint grid for the immersive feel */
        body::before {
            content: "";
            position: fixed; inset: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 56px 56px;
            -webkit-mask-image: radial-gradient(ellipse at 50% 0%, #000 0%, transparent 75%);
            mask-image: radial-gradient(ellipse at 50% 0%, #000 0%, transparent 75%);
        }

        .page { position: relative; z-index: 1; }

        label {
            display: block; margin: 16px 0 7px;
            font-size: 12px; font-weight: 600; letter-spacing: .6px;
            text-transform: uppercase; color: var(--muted);
        }

        input, textarea {
            width: 100%; padding: 12px 14px;
            background: #0a0a0a; border: 1px solid #2a2a2a; border-radius: 8px;
            color: var(--text); font: inherit; font-size: 14px; outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        input::placeholder, textarea::placeholder { color: #555; }
        input:focus, textarea:focus { border-color: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,.08); }
        input:-webkit-autofill {
            -webkit-text-fill-color: var(--text);
            -webkit-box-shadow: 0 0 0 100px #0a0a0a inset;
        }

        .btn {
            display: inline-block; width: 100%; margin-top: 20px; padding: 13px 18px;
            background: #f2f2f2; color: #000; border: none; border-radius: 8px;
            font: inherit; font-size: 14px; font-weight: 600; text-align: center;
            text-decoration: none; cursor: pointer;
            transition: background .2s, transform .1s;
        }
        .btn:hover { background: #fff; }
        .btn:active { transform: scale(.99); }
        .btn-ghost { background: transparent; color: var(--text); border: 1px solid #333; }
        .btn-ghost:hover { background: #1a1a1a; }

        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { * { animation: none !important; } }

        .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 16px; }

        .auth {
            width: 100%; max-width: 420px; padding: 34px;
            background: rgba(15,15,15,.85); border: 1px solid var(--line); border-radius: 16px;
            box-shadow: 0 30px 80px rgba(0,0,0,.65); backdrop-filter: blur(8px);
            animation: rise .5s ease both;
        }

        .tabs {
            display: flex; padding: 4px; margin-bottom: 28px;
            background: #0a0a0a; border: 1px solid var(--line); border-radius: 10px;
        }
        .tabs a {
            flex: 1; padding: 10px; border-radius: 7px; text-align: center;
            font-size: 13px; font-weight: 600; color: var(--muted); text-decoration: none;
            transition: color .2s;
        }
        .tabs a:hover { color: var(--text); }
        .tabs a.active { background: #f2f2f2; color: #000; }

        .auth h1 { font-size: 26px; font-weight: 700; letter-spacing: -.5px; }
        .auth .lead { margin-top: 6px; font-size: 14px; color: var(--muted); }

        .alert {
            margin-top: 18px; padding: 12px 14px; border-radius: 8px; border-left: 4px solid;
            font-size: 13px; line-height: 1.5; word-wrap: break-word;
        }
        .alert small { display: block; margin-top: 4px; opacity: .8; }
        .alert-error { background: rgba(255,107,107,.08); border-color: var(--bad); color: #ffb3b3; }
        .alert-ok    { background: rgba(61,220,132,.08);  border-color: var(--ok);  color: #a9f0c8; }

        .switch {
            margin-top: 22px; padding-top: 20px; border-top: 1px solid var(--line);
            text-align: center; font-size: 13px; color: var(--muted);
        }
        .switch a { color: var(--text); font-weight: 600; text-decoration: none; }
        .switch a:hover { text-decoration: underline; }

        @media (max-width: 450px) { .auth { padding: 26px 20px; } }
    </style>
</head>
<body>
<div class="page auth-wrap">

    <div class="auth">

        <div class="tabs">
            <a href="login.php" class="active">Log in</a>
            <a href="index.html">Register</a>
        </div>

        <h1>Welcome back</h1>
        <p class="lead">Log in to continue. No account yet? Register first.</p>

        <?php if ($notice): ?>
            <div class="alert alert-ok"><?php echo $notice; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <button type="submit" class="btn">Log in</button>
        </form>

        <div class="switch">
            Don't have an account? <a href="index.html">Register first</a>
        </div>

    </div>

</div>
</body>
</html>