<?php
require_once __DIR__ . '/functions/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT usertype, hash, otp_code, verification_token_hash FROM tblusers WHERE username = ? AND usertype = 'T' LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || empty($user['hash']) || !password_verify($password, $user['hash'])) {
                $error = 'Invalid username or password.';
            } elseif (!empty($user['verification_token_hash']) || !empty($user['otp_code'])) {
                $error = 'Please verify your email address before signing in.';
            } else {
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['usertype'] = $user['usertype'];
                header('Location: index.php');
                exit;
            }
        } catch (Throwable $e) {
            error_log('LTC login error: ' . $e->getMessage());
            $error = 'Sign in is temporarily unavailable. Please try again later.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LTC Trade - Sign in</title><link rel="stylesheet" href="css/auth.css?v=20261004-1"></head>
<body class="auth-page"><main class="auth-shell"><div class="auth-wrap"><img class="auth-logo" src="ltc_logo_600w.png" alt="LTC Tyres">
<section class="auth-card"><div class="auth-head"><h1>Trade sign in</h1><p>Access LTC Trade stock and ordering.</p></div><div class="auth-body">
<?php if ($error): ?><div class="auth-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
<form method="post" action="login.php">
<div class="auth-grid">
<div class="auth-field auth-full"><label for="username">Username</label><input id="username" name="username" maxlength="50" required autocomplete="username"></div>
<div class="auth-field auth-full"><label for="password">Password</label><div class="auth-password"><input id="password" type="password" name="password" maxlength="200" required autocomplete="current-password"><button class="auth-show" type="button" data-show-password="password">Show</button></div></div>
<div class="auth-full"><button class="auth-btn" type="submit">Sign in</button></div>
</div></form>
<div class="auth-links"><a href="uforgotpass.php">Forgotten password?</a><br>Need Trade access? <a href="registeracc.php">Create an account</a></div>
</div></section></div></main><script src="js/registeracc.js?v=20261004-1"></script></body></html>
