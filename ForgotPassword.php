<?php
require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();
require_once __DIR__ . '/db.php';   // provides $conn

// Look up a reset row by its raw token (we store only the SHA-256 hash).
function pr_find_valid(mysqli $conn, string $token): ?array
{
    if ($token === '') {
        return null;
    }
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare('SELECT id, userId FROM password_resets WHERE token = ? AND usedAt IS NULL AND expiresAt > NOW() LIMIT 1');
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

$stage  = 'request';  // request | reset | done
$error  = '';
$notice = '';
$token  = $_POST['token'] ?? ($_GET['token'] ?? '');
$formType = $_POST['form'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formType === 'request') {
    // Step 1: create a reset token and email the link. Always show a generic message.
    $email = strtolower(trim($_POST['email'] ?? ''));
    if ($email !== '') {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            $plain = bin2hex(random_bytes(32));
            $hash  = hash('sha256', $plain);

            $del = $conn->prepare('DELETE FROM password_resets WHERE userId = ? AND usedAt IS NULL');
            $del->bind_param('i', $user['id']);
            $del->execute();

            // Expiry is computed on the DB clock (NOW()) so it matches the "expiresAt > NOW()"
            // check regardless of any PHP/MySQL timezone difference (common on shared hosting).
            $ins = $conn->prepare('INSERT INTO password_resets (userId, token, expiresAt) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $ins->bind_param('is', $user['id'], $hash);
            $ins->execute();

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
            $url    = "$scheme://$host$dir/ForgotPassword.php?token=$plain";

            if (asclepius_config()['app_env'] === 'development') {
                error_log('Password reset link for ' . $email . ': ' . $url);
            }
            @mail($email, 'ASCLEPIUS password reset',
                "A password reset was requested for your account.\n\nReset your password (valid for 1 hour):\n$url\n\nIf you did not request this, you can ignore this email.");
        }
    }
    $notice = 'If that email is registered, a password reset link has been sent.';
    $stage  = 'request';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $formType === 'reset') {
    // Step 2: set the new password using a valid token.
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $reset    = pr_find_valid($conn, $token);

    if (!$reset) {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
        $stage = 'request';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
        $stage = 'reset';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match. Please try again.';
        $stage = 'reset';
    } else {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $up = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $up->bind_param('si', $newHash, $reset['userId']);
        $up->execute();

        $mark = $conn->prepare('UPDATE password_resets SET usedAt = NOW() WHERE id = ?');
        $mark->bind_param('i', $reset['id']);
        $mark->execute();

        $stage  = 'done';
        $notice = 'Your password has been reset. You can now log in with your new password.';
    }
} elseif ($token !== '') {
    // Arriving from the emailed link: show the reset form if the token is valid.
    if (pr_find_valid($conn, $token)) {
        $stage = 'reset';
    } else {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
        $stage = 'request';
    }
}

$e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password - ASCLEPIUS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
    body{ background:#f4f6fb; min-height:100vh; display:flex; justify-content:center; align-items:center; overflow-y:auto; }
    .container{ width:100vw; max-width:none; min-height:100vh; background:#fff; position:relative; overflow:hidden; display:flex; }
    .left-section{ width:50%; padding:50px 60px; position:relative; z-index:2; }
    .logo{ width:110px; margin-bottom:20px; }
    .branding{ display:flex; align-items:center; gap:18px; margin-bottom:18px; }
    .company-text{ display:flex; flex-direction:column; line-height:1; }
    .company-main{ color:#0aa6a6; font-size:34px; font-weight:800; letter-spacing:2px; }
    .company-sub{ color:#0aa6a6; font-size:12px; font-weight:700; text-transform:uppercase; margin-top:6px; }
    .form-container{ width:340px; margin-left:40px; }
    .step-title{ font-size:26px; font-weight:700; color:#1d2433; margin-bottom:24px; }
    .step-sub{ font-size:13px; color:#5f6f8d; margin-top:-14px; margin-bottom:22px; }
    .input-group{ margin-bottom:12px; }
    .input-group label{ display:block; font-size:12px; color:#555; margin-bottom:6px; }
    .input-group input{ width:100%; padding:10px 12px; border:2px solid #c9ced8; border-radius:6px; outline:none; font-size:14px; transition:border-color .3s ease; }
    .input-group input:hover, .input-group input:focus{ border-color:#2bb18f; }
    .submit-btn{ width:100%; padding:15px; border:none; border-radius:5px; background:#2bb18f; color:#fff; font-size:20px; font-weight:600; cursor:pointer; transition:all .3s ease; margin-top:10px; box-shadow:0 4px 6px rgba(0,0,0,.1); }
    .submit-btn:hover{ background:#259676; transform:scale(1.02) translateY(-2px); box-shadow:0 6px 12px rgba(0,0,0,.15); }
    .submit-btn:active{ transform:scale(.98); }
    .back-link{ margin-top:18px; text-align:center; font-size:14px; color:#5f6f8d; }
    .back-link a{ color:#2bb18f; text-decoration:none; font-weight:600; margin-left:6px; }
    .back-link a:hover{ color:#259676; text-decoration:underline; }
    .message{ margin-bottom:14px; padding:10px 12px; border-radius:6px; font-size:13px; line-height:1.4; }
    .message.error{ background:#fde8e8; color:#b42318; border:1px solid #f5c2c7; }
    .message.success{ background:#e8f8f0; color:#146c43; border:1px solid #b6e2cd; }
    .right-section{ width:50%; position:relative; overflow:hidden; }
    .right-section img{ width:100%; height:100%; object-fit:cover; }
    .curve{ position:absolute; top:-120px; right:35%; width:700px; height:130%; background:white; border-radius:50%; z-index:1; }
    .blue-shape-top{ position:absolute; top:-80px; left:38%; width:220px; height:250px; background:rgba(70,95,170,.7); border-radius:50%; transform:rotate(20deg); z-index:0; }
    .blue-shape-bottom{ position:absolute; bottom:-120px; right:-70px; width:350px; height:280px; background:rgba(70,95,170,.7); border-radius:50%; z-index:0; }
    @media(max-width:1024px){ .container{ flex-direction:column; height:auto; } .left-section, .right-section{ width:100%; } .form-container{ width:100%; margin-left:0; } .curve{ display:none; } }
  </style>
</head>
<body>
  <div class="container">
    <div class="curve"></div>
    <div class="blue-shape-top"></div>
    <div class="blue-shape-bottom"></div>

    <div class="left-section">
      <div class="branding">
        <img src="frontend/assets/img/ASCLEPIUS.jpg" class="logo" alt="Logo">
        <div class="company-text">
          <div class="company-main">ASCLEPIUS</div>
          <div class="company-sub">Medical &amp; Diagnostic Group Inc.</div>
        </div>
      </div>

      <div class="form-container">
        <?php /* Errors and notices are surfaced via SweetAlert2 near </body>. */ ?>

        <?php if ($stage === 'request'): ?>
          <div class="step-title">Forgot Password</div>
          <div class="step-sub">Enter your account email and we'll send a reset link.</div>
          <form method="post" action="ForgotPassword.php" autocomplete="off">
            <input type="hidden" name="form" value="request">
            <div class="input-group">
              <label for="email">Email Address</label>
              <input id="email" name="email" type="email" placeholder="you@example.com" required>
            </div>
            <button class="submit-btn" type="submit">SEND RESET LINK</button>
          </form>

        <?php elseif ($stage === 'reset'): ?>
          <div class="step-title">Reset Your Password</div>
          <div class="step-sub">Choose a new password (at least 8 characters).</div>
          <form method="post" action="ForgotPassword.php" autocomplete="off">
            <input type="hidden" name="form" value="reset">
            <input type="hidden" name="token" value="<?php echo $e($token); ?>">
            <div class="input-group">
              <label for="password">New Password</label>
              <input id="password" name="password" type="password" placeholder="At least 8 characters" required>
            </div>
            <div class="input-group">
              <label for="confirm_password">Confirm Password</label>
              <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter new password" required>
            </div>
            <button class="submit-btn" type="submit">SAVE PASSWORD</button>
          </form>

        <?php else: /* done */ ?>
          <div class="step-title">All set</div>
          <div class="step-sub">Your password was updated successfully.</div>
        <?php endif; ?>

        <div class="back-link">
          <span>Remember your password?</span>
          <a href="index.php">Login</a>
        </div>
      </div>
    </div>

    <div class="right-section">
      <img src="frontend/assets/img/Doctors.webp" alt="Doctor">
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($error !== ''): ?>
  <script>Swal.fire({ icon: 'error', title: 'Something went wrong', text: <?php echo json_encode($error); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
  <?php if ($notice !== ''): ?>
  <script>Swal.fire({ icon: 'success', title: 'Success', text: <?php echo json_encode($notice); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
</body>
</html>
