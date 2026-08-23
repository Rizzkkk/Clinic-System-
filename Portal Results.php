<?php
// Patient portal: the patient's own laboratory orders and the tests inside each one.
//
// Note the second query. The staff handler fetches every laboratory_result_items row with no
// WHERE clause at all and merges them in PHP, which is safe there only because staff may read all
// records. Copying that here would hand this patient every other patient's test results, so the
// child rows are re-filtered through their parent order with the SAME bound patient id rather
// than by a list of order ids gathered a moment earlier.

require_once __DIR__ . '/backend/auth/portal.php';
$patientId = require_patient();

$conn = asclepius_db();

$stmt = $conn->prepare('
    SELECT lr.id, lr.testDate, lr.remarks,
           d.firstName AS doctorFirstName, d.lastName AS doctorLastName
    FROM laboratory_results lr
    LEFT JOIN doctors d ON lr.orderedBy = d.id
    WHERE lr.patientId = ?
    ORDER BY lr.testDate DESC, lr.id DESC
');
$stmt->bind_param('i', $patientId);
$orders = $stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$stmt = $conn->prepare('
    SELECT i.resultId, i.testType, i.results, i.referenceRange, i.abnormalFlag
    FROM laboratory_result_items i
    JOIN laboratory_results lr ON i.resultId = lr.id
    WHERE lr.patientId = ?
    ORDER BY i.id
');
$stmt->bind_param('i', $patientId);
$itemRows = $stmt->execute() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

$itemsByOrder = [];
foreach ($itemRows as $item) {
    $itemsByOrder[(int) $item['resultId']][] = $item;
}

$active = 'results';
$pageTitle = 'My lab results';
require __DIR__ . '/frontend/partials/portal-nav.php';
?>

      <h1>My lab results</h1>
      <p class="portal-lead">The laboratory tests the clinic has run for you.</p>

      <div class="portal-banner">
        A result outside the usual range is not a diagnosis on its own. Your doctor reads it
        alongside everything else they know about you, so please talk to them before drawing any
        conclusion from what you see here.
      </div>

      <?php if (!$orders): ?>
        <div class="portal-card">
          <p class="portal-empty">You have no laboratory results on record yet.</p>
        </div>
      <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <?php $items = $itemsByOrder[(int) $order['id']] ?? []; ?>
        <div class="portal-card">
          <h2>Laboratory order of <?php echo h($order['testDate']); ?></h2>
          <?php if (!empty($order['doctorLastName'])): ?>
            <p class="portal-hint">Ordered by Dr. <?php echo h($order['doctorFirstName'] . ' ' . $order['doctorLastName']); ?></p>
          <?php endif; ?>

          <?php if (!$items): ?>
            <p class="portal-empty">No test details have been released for this order yet.</p>
          <?php else: ?>
            <div class="portal-table-scroll">
              <table class="portal-table">
                <thead>
                  <tr>
                    <th>Test</th>
                    <th>Result</th>
                    <th>Reference range</th>
                    <th>Flag</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $item): ?>
                  <?php $isAbnormal = strtoupper((string) $item['abnormalFlag']) === 'Y'; ?>
                  <tr>
                    <td><?php echo h($item['testType']); ?></td>
                    <td><strong><?php echo h($item['results']); ?></strong></td>
                    <td><?php echo $item['referenceRange'] ? h($item['referenceRange']) : '&mdash;'; ?></td>
                    <td>
                      <span class="pill <?php echo $isAbnormal ? 'pill-alert' : 'pill-ok'; ?>">
                        <?php echo $isAbnormal ? 'Outside range' : 'Normal'; ?>
                      </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

          <?php if (!empty($order['remarks'])): ?>
            <p class="portal-hint" style="margin-top:1rem"><strong>Remarks:</strong> <?php echo h($order['remarks']); ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

<?php require __DIR__ . '/frontend/partials/portal-footer.php'; ?>
