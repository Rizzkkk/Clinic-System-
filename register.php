<?php
require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/backend/lib/validation.php';

$registerError = '';
$registerSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
  $fullName = trim($_POST['full_name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $registerError = 'Please fill in all fields before creating an account.';
  } elseif (!is_valid_name($fullName)) {
    $registerError = 'Please enter a valid full name (letters, spaces, hyphens, apostrophes, and periods only).';
  } elseif (!is_valid_email($email)) {
    $registerError = 'Please enter a valid email address.';
  } elseif ($password !== $confirmPassword) {
    $registerError = 'Passwords do not match. Please confirm your password.';
  } else {
    $checkStatement = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $checkStatement->bind_param('s', $email);
    $checkStatement->execute();
    $existingUser = $checkStatement->get_result()->fetch_assoc();

    if ($existingUser) {
      $registerError = 'An account with this email already exists. Please use a different email or login instead.';
    } else {
      $passwordHash = password_hash($password, PASSWORD_DEFAULT);
      // New self-service accounts are created with no access ('pending'); an administrator must
      // assign a real role (admin/doctor/reception/lab/cashier) before the account can be used.
      $role = 'pending';
      $insertStatement = $conn->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
      $insertStatement->bind_param('ssss', $fullName, $email, $passwordHash, $role);

      if ($insertStatement->execute()) {
        $registerSuccess = 'Account created. An administrator must assign your role before you can access the system.';
        $_POST = [];
      } else {
        $registerError = 'Unable to create your account right now. Please try again.';
      }

      $insertStatement->close();
    }

    $checkStatement->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account - ASCLEPIUS</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family: 'Poppins', sans-serif;
    }

    body{
      background: #f4f6fb;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow-y: auto;
    }

    .container{
      width: 100vw;
      max-width: none;
      min-height: 100vh;
      background: #fff;
      position: relative;
      overflow: hidden;
      display: flex;
      border-radius: 0;
      box-shadow: none;
    }

    .left-section{
      width: 50%;
      padding: 50px 60px;
      position: relative;
      z-index: 2;
    }

    .logo{
      width: 110px;
      margin-bottom: 20px;
    }

    .branding{
      display: flex;
      align-items: center;
      gap: 18px;
      margin-bottom: 18px;
    }

    .company-text{
      display: flex;
      flex-direction: column;
      line-height: 1;
    }

    .company-main{
      color: #0aa6a6;
      font-size: 34px;
      font-weight: 800;
      letter-spacing: 2px;
    }

    .company-sub{
      color: #0aa6a6;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      margin-top: 6px;
    }

    .system-title{
      display: inline-block;
      border: 3px solid #1d2433;
      border-radius: 40px;
      padding: 10px 35px;
      font-size: 42px;
      font-weight: 600;
      color: #1d2433;
      margin-bottom: 50px;
    }

    .form-container{
      width: 320px;
      margin-left: 40px;
    }

    .input-group{
      margin-bottom: 12px;
    }

    .input-group label{
      display: block;
      font-size: 12px;
      color: #555;
      margin-bottom: 6px;
    }

    .input-group input{
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #c9ced8;
      border-radius: 6px;
      outline: none;
      font-size: 14px;
      transition: border-color 0.3s ease;
    }

    .input-group input:hover{
      border-color: #2bb18f;
    }

    .create-btn{
      width: 100%;
      padding: 15px;
      border: none;
      border-radius: 5px;
      background: #2bb18f;
      color: #fff;
      font-size: 22px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .create-btn:hover{
      background: #259676;
      transform: scale(1.02) translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .create-btn:active{
      transform: scale(0.98);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .account-link{
      margin-top: 18px;
      text-align: center;
      font-size: 14px;
      color: #5f6f8d;
    }

    .account-link a{
      color: #2bb18f;
      text-decoration: none;
      font-weight: 600;
      margin-left: 6px;
      transition: all 0.3s ease;
    }

    .account-link a:hover{
      color: #259676;
      text-decoration: underline;
      transform: translateY(-1px);
    }

    .message{
      margin-bottom: 14px;
      padding: 10px 12px;
      border-radius: 6px;
      font-size: 13px;
      line-height: 1.4;
    }

    .message.error{
      background: #fde8e8;
      color: #b42318;
      border: 1px solid #f5c2c7;
    }

    .message.success{
      background: #e8f8f0;
      color: #146c43;
      border: 1px solid #b6e2cd;
    }

    .right-section{
      width: 50%;
      position: relative;
      overflow: hidden;
    }

    .right-section img{
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .curve{
      position: absolute;
      top: -120px;
      right: 35%;
      width: 700px;
      height: 130%;
      background: white;
      border-radius: 50%;
      z-index: 1;
    }

    .blue-shape-top{
      position: absolute;
      top: -80px;
      left: 38%;
      width: 220px;
      height: 250px;
      background: rgba(70, 95, 170, 0.7);
      border-radius: 50%;
      transform: rotate(20deg);
      z-index: 0;
    }

    .blue-shape-bottom{
      position: absolute;
      bottom: -120px;
      right: -70px;
      width: 350px;
      height: 280px;
      background: rgba(70, 95, 170, 0.7);
      border-radius: 50%;
      z-index: 0;
    }

    @media(max-width: 1024px){
      .container{
        flex-direction: column;
        height: auto;
      }

      .left-section,
      .right-section{
        width: 100%;
      }

      .form-container{
        width: 100%;
        margin-left: 0;
      }

      .system-title{
        font-size: 28px;
      }

      .curve{
        display: none;
      }
    }
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
          <div class="company-sub">Medical & Diagnostic Group Inc.</div>
        </div>
      </div>

      <div class="form-container">
        <form method="post" action="" autocomplete="on">
          <div class="input-group">
            <label for="register-fullname">Full Name</label>
            <input id="register-fullname" name="full_name" type="text" placeholder="John Doe" required pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ '.-]*" title="Letters, spaces, hyphens, apostrophes, and periods only." value="<?php echo htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>

          <div class="input-group">
            <label for="register-email">Email</label>
            <input id="register-email" name="email" type="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>

          <div class="input-group">
            <label for="register-password">Password</label>
            <input id="register-password" name="password" type="password" placeholder="Create a password" required>
          </div>

          <div class="input-group">
            <label for="register-confirm-password">Confirm Password</label>
            <input id="register-confirm-password" name="confirm_password" type="password" placeholder="Confirm password" required>
          </div>

          <button class="create-btn" type="submit" name="register_submit" value="1">CREATE ACCOUNT</button>
        </form>

        <div class="account-link">
          <span>Already have an account?</span>
          <a href="login.php">Login</a>
        </div>

      </div>
    </div>

    <div class="right-section">
      <img src="frontend/assets/img/Doctors.webp" alt="Doctor">
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($registerError !== ''): ?>
  <script>Swal.fire({ icon: 'error', title: 'Something went wrong', text: <?php echo json_encode($registerError); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
  <?php if ($registerSuccess !== ''): ?>
  <script>Swal.fire({ icon: 'success', title: 'Success', text: <?php echo json_encode($registerSuccess); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
</body>
</html>

