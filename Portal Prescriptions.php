<?php
// Patient portal: the patient's own prescriptions.

require_once __DIR__ . '/backend/auth/portal.php';
$patientId = require_patient();

$conn = asclepius_db();

$stmt = $conn->prepare('
    SELECT p.id, p.medicationName, p.dosage, p.frequency, p.duration,
           p.prescriptionDate, p.expiryDate, p.status, p.notes,
           d.firstName AS doctorFirstName, d.lastName AS doctorLastName
    FROM prescriptions p
    JOIN doctors d ON p.doctorId = d.id
    WHERE p.patientId = ?
    ORDER BY p.prescriptionDate DESC, p.id DESC
');
$stmt->bind_param('i', $patientId);
$prescriptions = $stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$active = 'prescriptions';
$pageTitle = 'My prescriptions';
require __DIR__ . '/frontend/partials/portal-nav.php';
?>

      <h1>My prescriptions</h1>
      <p class="portal-lead">What you have been prescribed, and how to take it.</p>

      <div class="portal-card">
        <h2>Prescriptions</h2>
        <?php if (!$prescriptions): ?>
          <p class="portal-empty">You have no prescriptions on record yet.</p>
        <?php else: ?>
          <div class="portal-table-scroll">
            <table class="portal-table">
              <thead>
                <tr>
                  <th>Prescribed</th>
                  <th>Medication</th>
                  <th>Dosage</th>
                  <th>How often</th>
                  <th>For how long</th>
                  <th>Prescribed by</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($prescriptions as $rx): ?>
                <?php $isActive = strtolower((string) $rx['status']) === 'active'; ?>
                <tr>
                  <td>
                    <?php echo h($rx['prescriptionDate']); ?>
                    <?php if (!empty($rx['expiryDate'])): ?>
                      <br><span class="portal-hint">expires <?php echo h($rx['expiryDate']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?php echo h($rx['medicationName']); ?></strong>
                    <?php if (!empty($rx['notes'])): ?>
                      <br><span class="portal-hint"><?php echo h($rx['notes']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo h($rx['dosage']); ?></td>
                  <td><?php echo h($rx['frequency']); ?></td>
                  <td><?php echo h($rx['duration']); ?></td>
                  <td>Dr. <?php echo h($rx['doctorFirstName'] . ' ' . $rx['doctorLastName']); ?></td>
                  <td><span class="pill <?php echo $isActive ? 'pill-ok' : ''; ?>"><?php echo h($rx['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="portal-hint" style="margin-top:1rem">
            Always follow the instructions given to you by your doctor or pharmacist. If anything here
            does not match what you were told, <a href="index.php#contact">contact the clinic</a> before
            taking it.
          </p>
        <?php endif; ?>
      </div>

<?php require __DIR__ . '/frontend/partials/portal-footer.php'; ?>
