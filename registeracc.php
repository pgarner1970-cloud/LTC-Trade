<?php
require_once __DIR__ . '/functions/db.php';
require_once __DIR__ . '/functions/email_template.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function reg_value(string $key): string {
    return htmlspecialchars((string)($_POST[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}
function reg_csrf(): string {
    if (empty($_SESSION['register_csrf'])) {
        $_SESSION['register_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['register_csrf'];
}
function reg_mail(string $email, string $username, string $token): bool {
    $subject = 'Verify your LTC Trade email address';
    $verifyUrl = email_base_url() . '/verifyemail.php?token=' . rawurlencode($token);
    $body  = '<p>Hi ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ',</p>';
    $body .= '<p>Thanks for registering for LTC Trade access.</p>';
    $body .= '<p>Please verify your email address using the button below. This link expires in 60 minutes.</p>';
    $body .= '<p><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '">Verify my email</a></p>';
    $body .= '<p>If you did not request this account, you can ignore this email.</p>';
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: LTC Tyres <no-reply@tyres4sale.com>\r\n";
    return mail($email, $subject, email_header_html($subject) . $body . email_footer_html(), $headers);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company   = trim((string)($_POST['company'] ?? ''));
    $telephone = trim((string)($_POST['telephone'] ?? ''));
    $username  = trim((string)($_POST['username'] ?? ''));
    $email     = trim((string)($_POST['email'] ?? ''));
    $confemail = trim((string)($_POST['confemail'] ?? ''));
    $pass      = (string)($_POST['userpass'] ?? '');
    $confpass  = (string)($_POST['confpass'] ?? '');
    $csrf      = (string)($_POST['csrf'] ?? '');
    $website   = trim((string)($_POST['website'] ?? ''));

    if (!hash_equals((string)($_SESSION['register_csrf'] ?? ''), $csrf)) {
        $error = 'Your registration session has expired. Please refresh the page and try again.';
    } elseif ($website !== '') {
        $error = 'Registration could not be completed.';
    } elseif ($company === '' || $telephone === '' || $username === '' || $email === '' || $pass === '') {
        $error = 'Please complete all required fields.';
    } elseif (strlen($company) > 150 || strlen($telephone) > 40 || strlen($username) > 50 || strlen($email) > 254) {
        $error = 'One or more fields are too long.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
        $error = 'Username must be 4–50 characters using letters, numbers, dot, underscore or hyphen.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strcasecmp($email, $confemail) !== 0) {
        $error = 'Email addresses do not match.';
    } elseif (strlen($pass) < 10 || strlen($pass) > 200) {
        $error = 'Password must be at least 10 characters.';
    } elseif ($pass !== $confpass) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM tblusers WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn()) {
                $error = 'An account already exists using that username or email address.';
            } else {
                $plainToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $plainToken);
                $expires = (new DateTimeImmutable('+60 minutes'))->format('Y-m-d H:i:s');
                $passwordHash = password_hash($pass, PASSWORD_DEFAULT);

                $pdo->beginTransaction();
                $ins = $pdo->prepare("
                    INSERT INTO tblusers
                      (username, email, hash, usertype, description, telephone, otp_code, verification_token_hash, verification_expires_at)
                    VALUES (?, ?, ?, 'T', ?, ?, NULL, ?, ?)
                ");
                $ins->execute([$username, $email, $passwordHash, $company, $telephone, $tokenHash, $expires]);

                if (!reg_mail($email, $username, $plainToken)) {
                    throw new RuntimeException('Verification email could not be handed to the mail server.');
                }

                $pdo->commit();
                unset($_SESSION['register_csrf']);
                $success = 'Account created. Please check your email and verify your address before signing in.';
                $_POST = [];
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration could not be completed. Please try again later.';
            error_log('LTC registration error: ' . $e->getMessage());
        }
    }
}
$csrfToken = reg_csrf();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LTC Tyres - Register for Trade</title>
<link rel="stylesheet" href="css/auth.css?v=20261004-1">
</head>
<body class="auth-page">
<main class="auth-shell"><div class="auth-wrap">
<img class="auth-logo" src="ltc_logo_600w.png" alt="LTC Tyres">
<section class="auth-card">
<div class="auth-head"><h1>Create your Trade account</h1><p>Register to search stock and order tyres online.</p></div>
<div class="auth-body">
<?php if ($error): ?><div class="auth-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
<?php if ($success): ?><div class="auth-success" role="status"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><a class="auth-btn" href="login.php">Go to sign in</a>
<?php else: ?>
<form method="post" action="registeracc.php" autocomplete="on">
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
<div class="auth-hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
<div class="auth-grid">
<div class="auth-field auth-full"><label for="company">Company name</label><input id="company" name="company" maxlength="150" required autocomplete="organization" value="<?php echo reg_value('company'); ?>"></div>
<div class="auth-field"><label for="telephone">Telephone</label><input id="telephone" name="telephone" maxlength="40" required autocomplete="tel" inputmode="tel" value="<?php echo reg_value('telephone'); ?>"></div>
<div class="auth-field"><label for="username">Username</label><input id="username" name="username" minlength="4" maxlength="50" pattern="[A-Za-z0-9._-]{4,50}" required autocomplete="username" value="<?php echo reg_value('username'); ?>"><div class="auth-help">4–50 characters: letters, numbers, dot, underscore or hyphen.</div></div>
<div class="auth-field"><label for="email">Email</label><input id="email" type="email" name="email" maxlength="254" required autocomplete="email" value="<?php echo reg_value('email'); ?>"></div>
<div class="auth-field"><label for="confemail">Confirm email</label><input id="confemail" type="email" name="confemail" maxlength="254" required autocomplete="email" value="<?php echo reg_value('confemail'); ?>"></div>
<div class="auth-field"><label for="userpass">Password</label><div class="auth-password"><input id="userpass" type="password" name="userpass" minlength="10" maxlength="200" required autocomplete="new-password"><button class="auth-show" type="button" data-show-password="userpass">Show</button></div><div class="auth-help">Use at least 10 characters.</div></div>
<div class="auth-field"><label for="confpass">Confirm password</label><div class="auth-password"><input id="confpass" type="password" name="confpass" minlength="10" maxlength="200" required autocomplete="new-password"><button class="auth-show" type="button" data-show-password="confpass">Show</button></div></div>
<div class="auth-full"><button class="auth-btn" type="submit">Create Trade Account</button></div>
</div></form>
<?php endif; ?>
<div class="auth-links">Already registered? <a href="login.php">Sign in</a></div>
</div></section>
</div></main>
<script src="js/registeracc.js?v=20261004-1"></script>
</body></html>
