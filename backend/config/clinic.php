<?php
// Single source of clinic identity / branding, used by the website header (frontend/partials/
// sidebar.php) and the PDF reports (backend/lib/report.php).
//
// FILL IN the address / phone / email below when you have them. They render automatically only
// when non-empty, so leaving them blank simply shows nothing (no address on the site or PDFs yet).
// The logo is already wired.

function clinic_info(): array
{
    return [
        'name'    => 'ASCLEPIUS Medical & Diagnostic Group Inc.',
        'tagline' => 'Laboratory Information System',

        // TODO(owner): add the real values; they will then appear on the website header and every PDF.
        'addressLines' => [
            // 'Unit 1, 123 Example Street, Barangay, City, Province 0000',
        ],
        'phone' => '',   // e.g. '(02) 8123 4567'
        'email' => '',   // e.g. 'info@asclepius.example'

        // Logo. logoWeb is relative to the web root (for <img>); logoFile is the filesystem path
        // used to embed the JPEG into PDFs.
        'logoWeb'  => 'frontend/assets/img/ASCLEPIUS.jpg',
        'logoFile' => __DIR__ . '/../../frontend/assets/img/ASCLEPIUS.jpg',
    ];
}

// Convenience: the address block as an array of non-empty lines (address lines + phone + email).
// Returns [] when nothing is configured, so callers can skip rendering entirely.
function clinic_contact_lines(): array
{
    $c = clinic_info();
    $lines = array_values(array_filter(array_map('trim', $c['addressLines'] ?? [])));
    if (!empty($c['phone'])) { $lines[] = 'Tel: ' . $c['phone']; }
    if (!empty($c['email'])) { $lines[] = $c['email']; }
    return $lines;
}
