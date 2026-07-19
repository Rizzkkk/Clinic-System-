<?php
require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();

require_once __DIR__ . '/db.php';

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
      $_SESSION['user_role'] = $user['role'] ?? 'admin';
      header('Location: Dashboard.php');
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
  <title>Hospital Information System</title>

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
      height: 100vh;
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

    /* Left Section */
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
      color: #444;
      margin-bottom: 6px;
    }

    .input-group select,
    .input-group input{
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #c9ced8;
      border-radius: 6px;
      outline: none;
      font-size: 14px;
      transition: border-color 0.2s ease;
    }

    .input-group select:focus,
    .input-group input:focus{
      border-color: #2bb18f;
    }

    .input-group select:hover,
    .input-group input:hover{
      border-color: #2bb18f;
    }

    .password-box{
      position: relative;
    }

    .password-box .toggle-password{
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #2bb18f;
      font-size: 14px;
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0;
      font-weight: 600;
    }

    .password-box .toggle-password:hover{
      color: #1d8f76;
    }

    .options{
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      font-size: 14px;
    }

    .remember{
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .forgot{
      color: red;
      text-decoration: none;
      font-size: 13px;
    }

    .login-btn{
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 5px;
      background: #2bb18f;
      color: #fff;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .login-btn:hover{
      background: #259676;
      transform: scale(1.02) translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .login-btn:active{
      transform: scale(0.98);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .create-account{
      margin-top: 18px;
      text-align: center;
      font-size: 14px;
      color: #5f6f8d;
    }

    .create-account a{
      color: #2bb18f;
      text-decoration: none;
      font-weight: 600;
      margin-left: 6px;
    }

    .create-account a:hover{
      text-decoration: underline;
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

    /* Right Section */
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

    /* Curved White Shape */
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

    /* Blue Accent */
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

    <!-- Left -->
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
            <label for="login-email">Username</label>
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
              <span>Remember Me</span>
            </div>

            <a href="ForgotPassword.php" class="forgot">
              Forgot Password?
            </a>
          </div>

          <button class="login-btn" type="submit" name="login_submit" value="1">
            LOGIN
          </button>
        </form>

        <div class="create-account">
          <span>Don't have an account?</span>
          <a href="register.php">Create account</a>
        </div>

      </div>
    </div>

    <!-- Right -->
    <div class="right-section">
      <img src="frontend/assets/img/Doctors.webp" alt="Doctor">
    </div>

  </div>

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
