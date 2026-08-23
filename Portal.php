<?php
// Patient portal home.
//
// require_patient() is the gate for every portal page: it stops unverified, rejected, and
// unlinked accounts here (showing them their status instead of any records) and returns the one
// patients.id this session may ever read. Every statement below binds that id.

require_once __DIR__ . '/backend/auth/portal.php';
$patientId = require_patient();

$conn = asclepius_db();

$stmt = $conn->prepare("
    SELECT a.appointmentDate, a.appointmentTime, a.status,
           d.firstName AS doctorFirstName, d.lastName AS doctorLastName
    FROM appointments a
    JOIN doctors d ON a.doctorId = d.id
    WHERE a.patientId = ?
      AND a.appointmentDate >= CURDATE()
      AND a.status IN ('Scheduled', 'Requested')
    ORDER BY a.appointmentDate ASC, a.appointmentTime ASC
    LIMIT 1
");
$stmt->bind_param('i', $patientId);
$nextAppointment = $stmt->execute() ? $stmt->get_result()->fetch_assoc() : null;
$stmt->close();

$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS due FROM billing WHERE patientId = ? AND status <> 'Paid'");
$stmt->bind_param('i', $patientId);
$outstanding = $stmt->execute() ? (float) ($stmt->get_result()->fetch_assoc()['due'] ?? 0) : 0.0;
$stmt->close();

$stmt = $conn->prepare('SELECT MAX(testDate) AS latest FROM laboratory_results WHERE patientId = ?');
$stmt->bind_param('i', $patientId);
$latestResult = $stmt->execute() ? ($stmt->get_result()->fetch_assoc()['latest'] ?? null) : null;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM prescriptions WHERE patientId = ? AND status = 'Active'");
$stmt->bind_param('i', $patientId);
$activeRx = $stmt->execute() ? (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0) : 0;
$stmt->close();

$active = 'home';
$pageTitle = 'Patient portal';
require __DIR__ . '/frontend/partials/portal-nav.php';
?>

      <h1>Welcome back, <?php echo h($_SESSION['user_name'] ?? ''); ?></h1>
      <p class="portal-lead">Here is a quick summary of your record with us.</p>

      <div class="portal-tiles">
        <div class="portal-tile">
          <p class="portal-tile-label">Next appointment</p>
          <?php if ($nextAppointment): ?>
            <p class="portal-tile-value" style="font-size:1.15rem"><?php echo h($nextAppointment['appointmentDate']); ?></p>
            <p class="portal-tile-note">
              <?php echo h(substr((string) $nextAppointment['appointmentTime'], 0, 5)); ?>
              with Dr. <?php echo h($nextAppointment['doctorLastName']); ?>
              <?php if (strtolower((string) $nextAppointment['status']) === 'requested'): ?>
                &middot; awaiting confirmation
              <?php endif; ?>
            </p>
          <?php else: ?>
            <p class="portal-tile-value" style="font-size:1.15rem">None booked</p>
            <p class="portal-tile-note"><a href="Portal Appointments.php">Request one</a></p>
          <?php endif; ?>
        </div>

        <div class="portal-tile">
          <p class="portal-tile-label">Outstanding balance</p>
          <p class="portal-tile-value">PHP <?php echo h(number_format($outstanding, 2)); ?></p>
          <p class="portal-tile-note"><a href="Portal Billing.php">View bills</a></p>
        </div>

        <div class="portal-tile">
          <p class="portal-tile-label">Latest lab result</p>
          <p class="portal-tile-value" style="font-size:1.15rem"><?php echo $latestResult ? h($latestResult) : 'None yet'; ?></p>
          <p class="portal-tile-note"><a href="Portal Results.php">View results</a></p>
        </div>

        <div class="portal-tile">
          <p class="portal-tile-label">Active prescriptions</p>
          <p class="portal-tile-value"><?php echo $activeRx; ?></p>
          <p class="portal-tile-note"><a href="Portal Prescriptions.php">View prescriptions</a></p>
        </div>
      </div>

      <div class="portal-card">
        <h2>Questions about your care</h2>
        <p class="portal-lead" style="margin-bottom:0">
          This portal shows the records the clinic holds for you, but it is not a way to reach a
          clinician. If something here looks wrong, or you need advice about a result, please
          <a href="index.php#contact">contact the clinic</a> directly. In an emergency, call your
          local emergency number.
        </p>
      </div>

<?php require __DIR__ . '/frontend/partials/portal-footer.php'; ?>
