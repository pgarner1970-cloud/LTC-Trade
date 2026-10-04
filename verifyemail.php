<?php
require_once __DIR__ . '/functions/db.php';
$success = '';
$error = '';
$token = trim((string)($_GET['token'] ?? ''));

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
    $error = 'This verification link is invalid.';
} else {
    try {
        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare("
            SELECT id
            FROM tblusers
            WHERE usertype = 'T'
              AND verification_token_hash = ?
              AND verification_expires_at IS NOT NULL
              AND verification_expires_at >= NOW()
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            $error = 'This verification link is invalid or has expired.';
        } else {
            $upd = $pdo->prepare("
                UPDATE tblusers
                SET verification_token_hash = NULL,
                    verification_expires_at = NULL,
                    otp_code = NULL
                WHERE id = ? AND verification_token_hash = ?
            ");
            $upd->execute([$id, $tokenHash]);
            $success = 'Email address verified. You can now sign in.';
        }
    } catch (Throwable $e) {
        error_log('LTC verification error: ' . $e->getMessage());
        $error = 'Verification could not be completed. Please try again later.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LTC Trade - Verify Email</title><link rel="stylesheet" href="css/auth.css?v=20261004-1"></head>
<body class="auth-page"><main class="auth-shell"><div class="auth-wrap"><img class="auth-logo" src="ltc_logo_600w.png" alt="LTC Tyres">
<section class="auth-card"><div class="auth-head"><h1>Email verification</h1><p>Confirm your LTC Trade account.</p></div><div class="auth-body">
<?php if ($success): ?><div class="auth-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><a class="auth-btn" href="login.php">Go to sign in</a>
<?php else: ?><div class="auth-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><div class="auth-links"><a href="login.php">Return to sign in</a></div><?php endif; ?>
</div></section></div></main></body></html>
