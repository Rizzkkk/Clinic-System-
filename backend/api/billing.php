<?php
// Billing module API (cashier).
//   GET  ?api=get_bills  -> JSON array (joined to patient)
//   GET  ?api=get_stats  -> { total, pending, paid, totalAmount, paidAmount }
//   POST action=add_bill -> { success, message }
//   POST action=delete_bill (id) -> { success }
//   POST action=update_payment_status (id, status, paymentDate, paymentMethod) -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('billing');

// Writes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_bill') {
        $patientId     = (int) ($_POST['patientId'] ?? 0);
        $appointmentId = !empty($_POST['appointmentId']) ? (int) $_POST['appointmentId'] : null;
        $description   = $_POST['description']   ?? '';
        $amount        = $_POST['amount']        ?? 0;
        $status        = $_POST['status']        ?? 'Pending';
        $billingDate   = ($_POST['billingDate'] ?? '') !== '' ? $_POST['billingDate'] : null;
        $paymentMethod = $_POST['paymentMethod'] ?? '';
        $notes         = $_POST['notes']         ?? '';

        $stmt = $conn->prepare('
            INSERT INTO billing (patientId, appointmentId, description, amount, status, billingDate, paymentMethod, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('iisdssss', $patientId, $appointmentId, $description, $amount, $status, $billingDate, $paymentMethod, $notes);

        if ($stmt->execute()) {
            json_ok(['message' => 'Bill created successfully']);
        }
        error_log('add_bill failed: ' . $stmt->error);
        json_fail('Could not create the bill.');
    }

    if ($action === 'update_bill') {
        $id            = (int) ($_POST['id'] ?? 0);
        $description   = $_POST['description']   ?? '';
        $amount        = $_POST['amount']        ?? 0;
        $status        = $_POST['status']        ?? 'Pending';
        $billingDate   = ($_POST['billingDate'] ?? '') !== '' ? $_POST['billingDate'] : null;
        $paymentDate   = ($_POST['paymentDate'] ?? '') !== '' ? $_POST['paymentDate'] : null;
        $paymentMethod = $_POST['paymentMethod'] ?? '';
        $notes         = $_POST['notes']         ?? '';
        if (!$id) {
            json_fail('The bill record is required.');
        }
        $stmt = $conn->prepare('UPDATE billing SET description=?, amount=?, status=?, billingDate=?, paymentDate=?, paymentMethod=?, notes=? WHERE id=?');
        $stmt->bind_param('sdsssssi', $description, $amount, $status, $billingDate, $paymentDate, $paymentMethod, $notes, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Bill updated successfully']);
        }
        error_log('update_bill failed: ' . $stmt->error);
        json_fail('Could not update the bill.');
    }

    if ($action === 'delete_bill') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM billing WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_bill failed: ' . $stmt->error);
        json_fail('Could not delete the bill.');
    }

    if ($action === 'update_payment_status') {
        $id            = (int) ($_POST['id'] ?? 0);
        $status        = $_POST['status']        ?? '';
        $paymentDate   = ($_POST['paymentDate'] ?? '') !== '' ? $_POST['paymentDate'] : null;
        $paymentMethod = $_POST['paymentMethod'] ?? '';

        $stmt = $conn->prepare('UPDATE billing SET status = ?, paymentDate = ?, paymentMethod = ? WHERE id = ?');
        $stmt->bind_param('sssi', $status, $paymentDate, $paymentMethod, $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('update_payment_status failed: ' . $stmt->error);
        json_fail('Could not update the payment status.');
    }

    json_fail('Unknown action.');
}

// Reads
$api = $_GET['api'] ?? '';

if ($api === 'get_bills') {
    $result = $conn->query('
        SELECT b.id, b.patientId, b.description, b.amount, b.status, b.billingDate, b.paymentDate, b.paymentMethod, b.notes,
               p.firstName AS patientFirstName, p.lastName AS patientLastName
        FROM billing b
        JOIN patients p ON b.patientId = p.id
        ORDER BY b.billingDate DESC
    ');
    $bills = [];
    while ($row = $result->fetch_assoc()) {
        $bills[] = $row;
    }
    json_response($bills);
}

if ($api === 'get_stats') {
    $total       = (int) $conn->query('SELECT COUNT(*) AS c FROM billing')->fetch_assoc()['c'];
    $pending     = (int) $conn->query("SELECT COUNT(*) AS c FROM billing WHERE status = 'Pending'")->fetch_assoc()['c'];
    $paid        = (int) $conn->query("SELECT COUNT(*) AS c FROM billing WHERE status = 'Paid'")->fetch_assoc()['c'];
    $totalAmount = $conn->query('SELECT COALESCE(SUM(amount), 0) AS t FROM billing')->fetch_assoc()['t'];
    $paidAmount  = $conn->query("SELECT COALESCE(SUM(amount), 0) AS t FROM billing WHERE status = 'Paid'")->fetch_assoc()['t'];
    json_response([
        'total'       => $total,
        'pending'     => $pending,
        'paid'        => $paid,
        'totalAmount' => $totalAmount,
        'paidAmount'  => $paidAmount,
    ]);
}

// Printable receipt / invoice for one bill. Read op, so RBAC (above) allows reception/cashier/admin.
if ($api === 'bill_pdf') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $conn->prepare('
        SELECT b.id, b.description, b.amount, b.status, b.billingDate, b.paymentDate, b.paymentMethod, b.notes,
               p.firstName AS pFirst, p.lastName AS pLast
        FROM billing b
        JOIN patients p ON b.patientId = p.id
        WHERE b.id = ? LIMIT 1
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        json_fail('Bill not found.', 404);
    }

    require_once __DIR__ . '/../lib/pdf.php';
    require_once __DIR__ . '/../lib/report.php';
    $pdf = new SimplePdf();
    $L = 56; $R = 539;
    $y = report_header($pdf, 'Billing Statement / Receipt', $L, $R);
    report_field($pdf, $L, $y, 'Invoice No.', 'INV-' . str_pad((string) $row['id'], 4, '0', STR_PAD_LEFT));
    report_field($pdf, $L, $y, 'Patient', trim($row['pFirst'] . ' ' . $row['pLast']));
    report_field($pdf, $L, $y, 'Billing Date', $row['billingDate']);
    report_field($pdf, $L, $y, 'Status', $row['status']);
    report_section($pdf, $L, $R, $y, 'Charges');
    report_field($pdf, $L, $y, 'Description', $row['description'] ?: 'General service');
    report_field($pdf, $L, $y, 'Amount', 'PHP ' . number_format((float) $row['amount'], 2));
    report_field($pdf, $L, $y, 'Payment Method', $row['paymentMethod']);
    report_field($pdf, $L, $y, 'Payment Date', $row['paymentDate']);
    $y = $pdf->textBlock($L, $y + 6, 'Notes: ' . ($row['notes'] ?: '-'), 10, 95, 14);
    report_footer($pdf, $L, $R);

    $out = $pdf->output();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="bill-' . $id . '.pdf"');
    header('Content-Length: ' . strlen($out));
    echo $out;
    exit;
}

json_fail('Unknown endpoint.', 404);
