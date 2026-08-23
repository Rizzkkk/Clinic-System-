<?php
// Patient portal: the patient's own details.
//
// Only phone, email, and address are editable. Name, date of birth, gender, blood type,
// allergies, medical history, and insurance are shown read-only: those are the fields reception
// matches an applicant against, and letting a patient rewrite them would both break future
// matching and corrupt the provenance of clinical data.

require_once __DIR__ . '/backend/auth/portal.php';
$patientId = require_patient();

require_once __DIR__ . '/backend/lib/validation.php';

$conn = asclepius_db();
$portalError = '';
$portalSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // is_valid_name() is deliberately NOT used on phone or address -- validation.php warns against
    // it for fields that legitimately contain digits and symbols.
    if ($phone !== '' && !preg_match('/^[0-9 +\-()]{7,20}$/', $phone)) {
        $portalError = 'Please enter a valid phone number (digits, spaces, and + - ( ) only).';
    } elseif ($email !== '' && !is_valid_email($email)) {
        $portalError = 'Please enter a valid email address.';
    } elseif (strlen($address) > 500) {
        $portalError = 'Please keep your address under 500 characters.';
    } else {
        // Fixed column list bound to the id from require_patient(). No dynamic column names, and
        // no id from the request body.
        $stmt = $conn->prepare('UPDATE patients SET phone = ?, email = ?, address = ? WHERE id = ?');
        $stmt->bind_param('sssi', $phone, $email, $address, $patientId);

        if ($stmt->execute()) {
            $portalSuccess = 'Your contact details have been updated.';
        } else {
            error_log('portal contact update failed: ' . $stmt->error);
            $portalError = 'We could not save your details right now. Please try again.';
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare('
    SELECT firstName, lastName, middleName, dateOfBirth, gender, bloodType,
           phone, email, address, allergies, insurance_provider, insurance_number
    FROM patients
    WHERE id = ?
    LIMIT 1
');
$stmt->bind_param('i', $patientId);
$patient = $stmt->execute() ? $stmt->get_result()->fetch_assoc() : null;
$stmt->close();

if (!$patient) {
    // The link pointed at a record that no longer exists. Fail closed rather than render a shell.
    portal_deny();
}

$active = 'profile';
$pageTitle = 'My details';
require __DIR__ . '/frontend/partials/portal-nav.php';
?>

      <h1>My details</h1>
      <p class="portal-lead">Check what the clinic holds for you, and keep your contact details current.</p>

      <div class="portal-card">
        <h2>Your record</h2>
        <div class="portal-form">
          <div>
            <label for="pf-name">Full name</label>
            <input id="pf-name" type="text" readonly
                   value="<?php echo h(trim($patient['firstName'] . ' ' . $patient['middleName'] . ' ' . $patient['lastName'])); ?>">
          </div>
          <div>
            <label for="pf-dob">Date of birth</label>
            <input id="pf-dob" type="text" readonly value="<?php echo h($patient['dateOfBirth'] ?? ''); ?>">
          </div>
          <div>
            <label for="pf-gender">Gender</label>
            <input id="pf-gender" type="text" readonly value="<?php echo h($patient['gender'] ?? ''); ?>">
          </div>
          <div>
            <label for="pf-blood">Blood type</label>
            <input id="pf-blood" type="text" readonly value="<?php echo h($patient['bloodType'] ?? ''); ?>">
          </div>
          <div>
            <label for="pf-allergies">Allergies</label>
            <input id="pf-allergies" type="text" readonly value="<?php echo h($patient['allergies'] ?? ''); ?>">
          </div>
          <div>
            <label for="pf-insurer">Insurance</label>
            <input id="pf-insurer" type="text" readonly
                   value="<?php echo h(trim(($patient['insurance_provider'] ?? '') . ' ' . ($patient['insurance_number'] ?? ''))); ?>">
          </div>
          <div class="field-wide">
            <p class="portal-hint">
              These details can only be changed by the clinic. If something here is wrong, please
              <a href="index.php#contact">contact reception</a> so they can correct your record.
            </p>
          </div>
        </div>
      </div>

      <div class="portal-card portal-no-print">
        <h2>Contact details</h2>
        <form method="post" action="" class="portal-form">
          <div>
            <label for="pf-phone">Phone number</label>
            <input id="pf-phone" name="phone" type="tel" pattern="[0-9 +\-()]{7,20}"
                   title="Digits, spaces, and + - ( ) only."
                   value="<?php echo h($patient['phone'] ?? ''); ?>">
          </div>
          <div>
            <label for="pf-email">Email</label>
            <input id="pf-email" name="email" type="email" value="<?php echo h($patient['email'] ?? ''); ?>">
          </div>
          <div class="field-wide">
            <label for="pf-address">Address</label>
            <textarea id="pf-address" name="address" maxlength="500"><?php echo h($patient['address'] ?? ''); ?></textarea>
          </div>
          <div class="portal-form-actions">
            <button class="btn btn-primary" type="submit" name="update_contact" value="1">Save changes</button>
          </div>
        </form>
      </div>

<?php require __DIR__ . '/frontend/partials/portal-footer.php'; ?>
