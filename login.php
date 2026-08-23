<?php
require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/backend/config/clinic.php';
require_once __DIR__ . '/backend/auth/roles.php';

// Staff and patients share this one form but belong in different applications, so an
// already-signed-in visitor is sent to whichever one is theirs.
$validRoles = ['admin', 'doctor', 'reception', 'lab', 'cashier'];
if (isset($_SESSION['user_id'])) {
  $sessionRole = $_SESSION['user_role'] ?? '';
  if (in_array($sessionRole, $validRoles, true)) {
    header('Location: Dashboard.php');
    exit;
  }
  if (in_array($sessionRole, PORTAL_ROLES, true)) {
    header('Location: Portal.php');
    exit;
  }
}

$clinic = clinic_info();
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $loginError = 'Please enter both email and password.';
  } else {
    $statement = $conn->prepare('SELECT id, full_name, password_hash, role FROM users WHERE email = ? LIMIT 1');
    $statement->bind_param('s', $email);
    $statement->execute();
    $result = $statement->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['full_name'];
      // Never default an unknown role to 'admin'. role is NOT NULL so this cannot fire today,
      // but these accounts are now internet-facing and must fail closed, not open.
      $_SESSION['user_role'] = $user['role'] ?? '';
      // A patient_pending / patient_rejected account still lands on Portal.php; require_patient()
      // there shows them their verification status instead of any records.
      $isPortalAccount = in_array($user['role'] ?? '', PORTAL_ROLES, true);
      header('Location: ' . ($isPortalAccount ? 'Portal.php' : 'Dashboard.php'));
      exit;
    }

    $loginError = 'Invalid email or password.';
    $statement->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Staff Login | ASCLEPIUS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/assets/css/login.css">
</head>
<body>

  <main class="login-page">
    <a href="index.php" class="back-link">&larr; Back to website</a>

    <div class="login-card">
      <div class="branding">
        <img src="<?php echo htmlspecialchars($clinic['logoWeb'], ENT_QUOTES, 'UTF-8'); ?>" class="logo" alt="Asclepius logo">
        <div class="company-text">
          <div class="company-main">ASCLEPIUS</div>
          <div class="company-sub"><?php echo htmlspecialchars($clinic['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
      </div>

      <h1 class="login-title">Sign in</h1>

      <form method="post" action="" autocomplete="on">
        <div class="input-group">
          <label for="login-email">Email</label>
          <input id="login-email" name="email" type="text" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="input-group">
          <label for="login-password">Password</label>
          <div class="password-box">
            <input id="login-password" name="password" type="password" placeholder="Password">
            <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
          </div>
        </div>

        <div class="options">
          <div class="remember">
            <input type="checkbox" checked>
            <span>Remember me</span>
          </div>
          <a href="ForgotPassword.php" class="forgot">Forgot password?</a>
        </div>

        <button class="login-btn" type="submit" name="login_submit" value="1">Sign in</button>
      </form>

      <div class="create-account">
        <span>Clinic staff need an account?</span>
        <a href="register.php">Create staff account</a>
      </div>

      <div class="create-account">
        <span>Are you a patient?</span>
        <a href="Portal Register.php">Create a patient account</a>
      </div>
    </div>
  </main>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('login-password');
      const toggleBtn = document.querySelector('.toggle-password');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = 'Hide';
      } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = 'Show';
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($loginError !== ''): ?>
  <script>Swal.fire({ icon: 'error', title: 'Something went wrong', text: <?php echo json_encode($loginError); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
</body>
</html>
