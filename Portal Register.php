<?php
// Public patient portal signup. Creates a users row with role 'patient_pending' and NO link to a
// patients record -- reception matches and approves the claim in Portal Accounts.php before any
// medical information becomes visible. Nothing here grants access to anything.
//
// The date of birth and mobile number are the point of this form. Matching an applicant to a
// clinic record on name alone is how the wrong person is handed someone else's medical history,
// so both are captured as an unverified *claim* for reception to check against the real record.

require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/backend/config/clinic.php';
require_once __DIR__ . '/backend/lib/validation.php';
require_once __DIR__ . '/backend/lib/escape.php';
require_once __DIR__ . '/backend/auth/roles.php';

if (isset($_SESSION['user_id'])) {
    $sessionRole = $_SESSION['user_role'] ?? '';
    if (in_array($sessionRole, PORTAL_ROLES, true)) {
        header('Location: Portal.php');
        exit;
    }
    if (in_array($sessionRole, ['admin', 'doctor', 'reception', 'lab', 'cashier'], true)) {
        header('Location: Dashboard.php');
        exit;
    }
}

$registerError = '';
$registerSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['portal_register_submit'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $dob = trim($_POST['date_of_birth'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $confirmedOwn = isset($_POST['confirm_own_details']);

    $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
    $dobIsValid = $dobDate !== false && $dobDate->format('Y-m-d') === $dob;

    if ($fullName === '' || $email === '' || $dob === '' || $phone === '' || $password === '' || $confirmPassword === '') {
        $registerError = 'Please fill in all fields before creating an account.';
    } elseif (!is_valid_name($fullName)) {
        $registerError = 'Please enter a valid full name (letters, spaces, hyphens, apostrophes, and periods only).';
    } elseif (!is_valid_email($email)) {
        $registerError = 'Please enter a valid email address.';
    } elseif (!$dobIsValid) {
        $registerError = 'Please enter a valid date of birth.';
    } elseif ($dobDate >= new DateTime('today')) {
        $registerError = 'Date of birth must be in the past.';
    } elseif (!preg_match('/^[0-9 +\-()]{7,20}$/', $phone)) {
        $registerError = 'Please enter a valid mobile number (digits, spaces, and + - ( ) only).';
    } elseif (strlen($password) < 8) {
        // These accounts are internet-facing and sit in front of medical records, so unlike the
        // staff form this one enforces a minimum length (security issue S-9).
        $registerError = 'Your password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $registerError = 'Passwords do not match. Please confirm your password.';
    } elseif (!$confirmedOwn) {
        $registerError = 'Please confirm that the details you entered are your own.';
    } else {
        $checkStatement = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $checkStatement->bind_param('s', $email);
        $checkStatement->execute();
        $existingUser = $checkStatement->get_result()->fetch_assoc();
        $checkStatement->close();

        if ($existingUser) {
            $registerError = 'An account with this email already exists. Please sign in instead, or use a different email.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'patient_pending';
            $insertStatement = $conn->prepare(
                'INSERT INTO users (full_name, email, password_hash, role, claimedDob, claimedPhone)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $insertStatement->bind_param('ssssss', $fullName, $email, $passwordHash, $role, $dob, $phone);

            if ($insertStatement->execute()) {
                // Deliberately no session is created here: signing up must not sign you in, since
                // the account has not been verified against a clinic record yet.
                $registerSuccess = 'Account created. Our reception team will verify your details against your '
                    . 'clinic record before your information becomes visible. You can sign in now to check your status.';
                $_POST = [];
            } else {
                error_log('portal signup insert failed: ' . $insertStatement->error);
                $registerError = 'Unable to create your account right now. Please try again.';
            }

            $insertStatement->close();
        }
    }
}

$clinic = clinic_info();
$pageTitle = 'Create a patient account - ' . $clinic['name'];
$metaDescription = 'Create an account to view your own appointments, results, prescriptions, and bills.';
$extraStyles = ['frontend/assets/css/portal.css'];
require __DIR__ . '/frontend/partials/public-header.php';
?>

  <main class="legal-page">
    <div class="wrap legal-content">
      <h1>Create a patient account</h1>
      <p>
        Use this form to request access to your own records. To protect your privacy, our reception
        team checks your details against your clinic record by hand before any medical information
        is shown. You will not see any records until that check is done.
      </p>

      <section>
        <form method="post" action="" class="portal-form" autocomplete="on">
          <div>
            <label for="portal-fullname">Full name</label>
            <input id="portal-fullname" name="full_name" type="text" required
                   pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ '.-]*"
                   title="Letters, spaces, hyphens, apostrophes, and periods only."
                   value="<?php echo h($_POST['full_name'] ?? ''); ?>">
            <p class="portal-hint">Enter your name as the clinic has it on file.</p>
          </div>

          <div>
            <label for="portal-email">Email</label>
            <input id="portal-email" name="email" type="email" required
                   value="<?php echo h($_POST['email'] ?? ''); ?>">
          </div>

          <div>
            <label for="portal-dob">Date of birth</label>
            <input id="portal-dob" name="date_of_birth" type="date" required
                   max="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo h($_POST['date_of_birth'] ?? ''); ?>">
          </div>

          <div>
            <label for="portal-phone">Mobile number</label>
            <input id="portal-phone" name="phone" type="tel" required
                   pattern="[0-9 +\-()]{7,20}"
                   title="Digits, spaces, and + - ( ) only."
                   value="<?php echo h($_POST['phone'] ?? ''); ?>">
            <p class="portal-hint">Reception may call this number to confirm your identity.</p>
          </div>

          <div>
            <label for="portal-password">Password</label>
            <input id="portal-password" name="password" type="password" required minlength="8">
            <p class="portal-hint">At least 8 characters.</p>
          </div>

          <div>
            <label for="portal-confirm-password">Confirm password</label>
            <input id="portal-confirm-password" name="confirm_password" type="password" required minlength="8">
          </div>

          <div class="field-wide">
            <label for="portal-confirm-own">
              <input id="portal-confirm-own" name="confirm_own_details" type="checkbox" value="1" required
                     style="width:auto;margin-right:.5rem;vertical-align:middle">
              I confirm these are my own details and I am requesting access to my own records.
            </label>
          </div>

          <div class="portal-form-actions">
            <button class="btn btn-primary" type="submit" name="portal_register_submit" value="1">Create account</button>
          </div>
        </form>
      </section>

      <section>
        <h2>Already have an account?</h2>
        <p><a href="login.php">Sign in here</a>. If reception has not verified you yet, signing in will show your current status.</p>
      </section>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($registerError !== ''): ?>
  <script>Swal.fire({ icon: 'error', title: 'Something went wrong', text: <?php echo json_encode($registerError); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
  <?php if ($registerSuccess !== ''): ?>
  <script>Swal.fire({ icon: 'success', title: 'Account created', text: <?php echo json_encode($registerSuccess); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
