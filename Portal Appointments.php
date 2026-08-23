<?php
// Patient portal: the patient's own appointments, plus a form to ask reception for a new one.
//
// A request is an appointments row with status 'Requested'. It is NOT a booking: reception
// confirms it in Appointment.php by setting the status to 'Scheduled'. patientId and status are
// never taken from the request body -- patientId comes from require_patient() and status is a
// literal, so neither can be steered by whoever is posting.

require_once __DIR__ . '/backend/auth/portal.php';
$patientId = require_patient();

$conn = asclepius_db();
$portalError = '';
$portalSuccess = '';

// How many requests may be outstanding at once. Keeps reception's queue usable and stops a
// single account flooding the appointments table.
const PORTAL_MAX_OPEN_REQUESTS = 3;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_appointment'])) {
    $doctorId = (int) ($_POST['doctorId'] ?? 0);
    $date = trim($_POST['appointmentDate'] ?? '');
    $time = trim($_POST['appointmentTime'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    $dateIsValid = $dateObj !== false && $dateObj->format('Y-m-d') === $date;

    if (!$doctorId || $date === '' || $time === '' || $reason === '') {
        $portalError = 'Please choose a doctor, a preferred date and time, and give a reason.';
    } elseif (!$dateIsValid) {
        $portalError = 'Please enter a valid preferred date.';
    } elseif ($dateObj < new DateTime('today')) {
        $portalError = 'Please choose a date that is not in the past.';
    } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
        $portalError = 'Please enter a valid preferred time.';
    } elseif (strlen($reason) > 1000) {
        $portalError = 'Please keep the reason under 1000 characters.';
    } else {
        $check = $conn->prepare('SELECT id FROM doctors WHERE id = ? LIMIT 1');
        $check->bind_param('i', $doctorId);
        $check->execute();
        $doctorExists = (bool) $check->get_result()->fetch_assoc();
        $check->close();

        $count = $conn->prepare("SELECT COUNT(*) AS c FROM appointments WHERE patientId = ? AND status = 'Requested'");
        $count->bind_param('i', $patientId);
        $count->execute();
        $openRequests = (int) ($count->get_result()->fetch_assoc()['c'] ?? 0);
        $count->close();

        if (!$doctorExists) {
            $portalError = 'Please choose a doctor from the list.';
        } elseif ($openRequests >= PORTAL_MAX_OPEN_REQUESTS) {
            $portalError = 'You already have ' . PORTAL_MAX_OPEN_REQUESTS . ' requests waiting for confirmation. '
                . 'Please wait for reception to respond, or call the clinic.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO appointments (patientId, doctorId, appointmentDate, appointmentTime, reason, status, notes)
                 VALUES (?, ?, ?, ?, ?, 'Requested', '')"
            );
            $stmt->bind_param('iisss', $patientId, $doctorId, $date, $time, $reason);

            if ($stmt->execute()) {
                $portalSuccess = 'Your request has been sent. Reception will confirm your appointment, '
                    . 'and it will show as Requested here until they do.';
                $_POST = [];
            } else {
                error_log('portal appointment request failed: ' . $stmt->error);
                $portalError = 'We could not send your request right now. Please try again.';
            }
            $stmt->close();
        }
    }
}

// Own appointments only. No JOIN to patients: we already know who this is, and joining would risk
// rendering someone else's demographics if the filter were ever wrong.
$stmt = $conn->prepare('
    SELECT a.id, a.appointmentDate, a.appointmentTime, a.reason, a.status, a.notes,
           d.firstName AS doctorFirstName, d.lastName AS doctorLastName, d.specialty
    FROM appointments a
    JOIN doctors d ON a.doctorId = d.id
    WHERE a.patientId = ?
    ORDER BY a.appointmentDate DESC, a.appointmentTime DESC, a.id DESC
');
$stmt->bind_param('i', $patientId);
$appointments = $stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// The one statement on this page that is not patient-scoped: doctors is a staff directory, not a
// patient record. Only the fields needed to fill a dropdown are selected -- never the doctor's
// phone, email, address, or licence number. It is still a prepared statement so that the review
// rule "no $conn->query( in portal code" stays a clean, exception-free grep.
$doctors = [];
$stmt = $conn->prepare('SELECT id, firstName, lastName, specialty FROM doctors ORDER BY lastName, firstName');
if ($stmt->execute()) {
    $doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

function portal_appointment_pill(string $status): string
{
    switch (strtolower($status)) {
        case 'completed': return 'pill pill-ok';
        case 'cancelled': return 'pill pill-alert';
        case 'requested': return 'pill pill-warn';
        case 'scheduled': return 'pill pill-info';
        default: return 'pill';
    }
}

$active = 'appointments';
$pageTitle = 'My appointments';
require __DIR__ . '/frontend/partials/portal-nav.php';
?>

      <h1>My appointments</h1>
      <p class="portal-lead">Your upcoming and past visits with us.</p>

      <div class="portal-card">
        <h2>Appointments</h2>
        <?php if (!$appointments): ?>
          <p class="portal-empty">You have no appointments on record yet.</p>
        <?php else: ?>
          <div class="portal-table-scroll">
            <table class="portal-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Doctor</th>
                  <th>Reason</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $row): ?>
                <tr>
                  <td><?php echo h($row['appointmentDate']); ?></td>
                  <td><?php echo h(substr((string) $row['appointmentTime'], 0, 5)); ?></td>
                  <td>
                    Dr. <?php echo h($row['doctorFirstName'] . ' ' . $row['doctorLastName']); ?>
                    <?php if (!empty($row['specialty'])): ?>
                      <br><span class="portal-hint"><?php echo h($row['specialty']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo h($row['reason']); ?></td>
                  <td><span class="<?php echo portal_appointment_pill((string) $row['status']); ?>"><?php echo h($row['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div class="portal-card portal-no-print">
        <h2>Request an appointment</h2>
        <p class="portal-lead">
          This sends a request to reception &mdash; it is not a confirmed booking. They will contact
          you to agree a final date and time.
        </p>

        <?php if (!$doctors): ?>
          <p class="portal-empty">Online requests are not available right now. Please call the clinic.</p>
        <?php else: ?>
        <form method="post" action="" class="portal-form">
          <div>
            <label for="req-doctor">Doctor</label>
            <select id="req-doctor" name="doctorId" required>
              <option value="">Choose a doctor</option>
              <?php foreach ($doctors as $doctor): ?>
              <option value="<?php echo (int) $doctor['id']; ?>">
                Dr. <?php echo h($doctor['firstName'] . ' ' . $doctor['lastName']); ?><?php echo $doctor['specialty'] ? ' (' . h($doctor['specialty']) . ')' : ''; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="req-date">Preferred date</label>
            <input id="req-date" name="appointmentDate" type="date" required min="<?php echo date('Y-m-d'); ?>">
          </div>

          <div>
            <label for="req-time">Preferred time</label>
            <input id="req-time" name="appointmentTime" type="time" required>
          </div>

          <div class="field-wide">
            <label for="req-reason">Reason for the visit</label>
            <textarea id="req-reason" name="reason" required maxlength="1000"
                      placeholder="Briefly describe what you need to be seen about."></textarea>
            <p class="portal-hint">Do not use this form for anything urgent. In an emergency, call your local emergency number.</p>
          </div>

          <div class="portal-form-actions">
            <button class="btn btn-primary" type="submit" name="request_appointment" value="1">Send request</button>
          </div>
        </form>
        <?php endif; ?>
      </div>

<?php require __DIR__ . '/frontend/partials/portal-footer.php'; ?>
