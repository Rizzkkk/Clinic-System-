<?php
// Patient portal: the patient's own bills. View only -- there is no online payment.

require_once __DIR__ . '/backend/auth/portal.php';
$patientId = require_patient();

$conn = asclepius_db();

$stmt = $conn->prepare('
    SELECT b.id, b.description, b.amount, b.status, b.billingDate,
           b.paymentDate, b.paymentMethod, b.notes
    FROM billing b
    WHERE b.patientId = ?
    ORDER BY b.billingDate DESC, b.id DESC
');
$stmt->bind_param('i', $patientId);
$bills = $stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$outstanding = 0.0;
foreach ($bills as $bill) {
    if (strtolower((string) $bill['status']) !== 'paid') {
        $outstanding += (float) $bill['amount'];
    }
}

$active = 'billing';
$pageTitle = 'My bills';
require __DIR__ . '/frontend/partials/portal-nav.php';
?>

      <h1>My bills</h1>
      <p class="portal-lead">What you have been charged, and what is still outstanding.</p>

      <div class="portal-tiles">
        <div class="portal-tile">
          <p class="portal-tile-label">Outstanding</p>
          <p class="portal-tile-value">PHP <?php echo h(number_format($outstanding, 2)); ?></p>
          <p class="portal-tile-note">Across <?php echo count($bills); ?> bill<?php echo count($bills) === 1 ? '' : 's'; ?> on record</p>
        </div>
      </div>

      <div class="portal-card">
        <h2>Billing history</h2>
        <?php if (!$bills): ?>
          <p class="portal-empty">You have no bills on record yet.</p>
        <?php else: ?>
          <div class="portal-table-scroll">
            <table class="portal-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Paid on</th>
                  <th>Method</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bills as $bill): ?>
                <?php $isPaid = strtolower((string) $bill['status']) === 'paid'; ?>
                <tr>
                  <td><?php echo h($bill['billingDate']); ?></td>
                  <td>
                    <?php echo h($bill['description']); ?>
                    <?php if (!empty($bill['notes'])): ?>
                      <br><span class="portal-hint"><?php echo h($bill['notes']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td>PHP <?php echo h(number_format((float) $bill['amount'], 2)); ?></td>
                  <td><span class="pill <?php echo $isPaid ? 'pill-ok' : 'pill-warn'; ?>"><?php echo h($bill['status']); ?></span></td>
                  <td><?php echo $bill['paymentDate'] ? h($bill['paymentDate']) : '&mdash;'; ?></td>
                  <td><?php echo $bill['paymentMethod'] ? h($bill['paymentMethod']) : '&mdash;'; ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="portal-hint" style="margin-top:1rem">
            To settle an outstanding bill or query a charge, please
            <a href="index.php#contact">contact the clinic</a>. Payments are not taken through this portal.
          </p>
        <?php endif; ?>
      </div>

<?php require __DIR__ . '/frontend/partials/portal-footer.php'; ?>
