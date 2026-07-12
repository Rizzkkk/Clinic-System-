<?php
// Shared building blocks for clinical PDF reports, layered on SimplePdf. Keeps the clinic
// letterhead, labelled fields, and the doctor signature block consistent across reports
// (medical record, prescription, billing, lab). Include after backend/lib/pdf.php.

require_once __DIR__ . '/../config/clinic.php';

// Clinic letterhead (logo + name + optional address) + report subtitle + rule. Returns the yTop
// just below the header. The address renders only when configured in backend/config/clinic.php.
function report_header(SimplePdf $pdf, string $subtitle, float $L, float $R, float $y = 50): float
{
    $c = clinic_info();
    $textX = $L;
    if (!empty($c['logoFile']) && is_file($c['logoFile'])) {
        $data = @file_get_contents($c['logoFile']);
        if ($data !== false && $data !== '' && $pdf->image($data, $L, $y, 54, 54)) {
            $textX = $L + 66;
        }
    }
    $ty = $y + 6;
    $pdf->text($textX, $ty, $c['name'], 15, true); $ty += 17;
    foreach (clinic_contact_lines() as $line) { $pdf->text($textX, $ty, $line, 9); $ty += 12; }
    $y = max($y + 58, $ty + 4);
    $pdf->text($L, $y, $subtitle, 12, true); $y += 8;
    $pdf->line($L, $y, $R, $y); $y += 24;
    return $y;
}

// A labelled field row ("Label:   value"). Advances $y.
function report_field(SimplePdf $pdf, float $L, float &$y, string $label, ?string $value): void
{
    $pdf->text($L, $y, $label, 10, true);
    $pdf->text($L + 140, $y, ($value === null || $value === '') ? '-' : $value, 10);
    $y += 18;
}

// Section heading with a rule under it. Advances $y.
function report_section(SimplePdf $pdf, float $L, float $R, float &$y, string $title): void
{
    $y += 6;
    $pdf->text($L, $y, $title, 11, true); $y += 6;
    $pdf->line($L, $y, $R, $y); $y += 18;
}

// Doctor signature block near the bottom: embeds the JPEG signature above the line when
// $signatureAbsPath points at a readable file; otherwise just the line. Then the printed name.
function report_signature(SimplePdf $pdf, float $L, float $y, ?string $doctorName, ?string $signatureAbsPath): void
{
    if ($signatureAbsPath !== null && is_file($signatureAbsPath)) {
        $data = @file_get_contents($signatureAbsPath);
        if ($data !== false && $data !== '') {
            $pdf->image($data, $L, $y - 36, 120, 34); // sits just above the signature line
        }
    }
    $pdf->line($L, $y, $L + 220, $y);
    $name = ($doctorName !== null && trim($doctorName) !== '') ? ('Dr. ' . trim($doctorName)) : 'Attending Physician';
    $pdf->text($L, $y + 14, $name, 10, true);
    $pdf->text($L, $y + 28, 'Signature over printed name', 8);
}

// Footer line + generated stamp (call last).
function report_footer(SimplePdf $pdf, float $L, float $R): void
{
    $pdf->line($L, 806, $R, 806);
    $pdf->text($L, 820, 'Generated ' . date('Y-m-d H:i') . '  -  system-generated report, ASCLEPIUS Medical & Diagnostic Group Inc.', 8);
}
