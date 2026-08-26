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
        // 'name' is the registered entity: it is what the logo artwork says, what the footer
        // copyright claims, and what is stamped on every PDF report -- do not swap it for the
        // marketing brand. 'shortName' is the public-facing brand used across the website.
        'name'      => 'ASCLEPIUS Medical & Diagnostic Group Inc.',
        'shortName' => 'Asclepius Clinic & Laboratory',
        'tagline'   => 'Checkup, laboratory and OFW medical services',

        // TODO(owner): add the real values; they will then appear on the website header and every PDF.
        'addressLines' => [
            // 'Unit 1, 123 Example Street, Barangay, City, Province 0000',
        ],
        'phone' => '',   // e.g. '(02) 8123 4567'
        'email' => '',   // e.g. 'info@asclepius.example'
        'hours' => '',   // e.g. 'Mon-Sat, 6:00 AM - 5:00 PM'

        // Accreditation references. Blank means the website renders the plain claim with no
        // number; fill these in and the number is shown alongside it. Do not state a licence
        // number here that the clinic does not actually hold.
        'dohLicense'       => '',   // e.g. 'DOH LTO No. 00-0000'
        'ofwAccreditation' => '',   // e.g. 'DOH-accredited for OFW medical examinations, Ref. 0000'

        // Logo. logoWeb is relative to the web root (for <img>); logoFile is the filesystem path
        // used to embed the JPEG into PDFs.
        'logoWeb'  => 'frontend/assets/img/ASCLEPIUS.jpg',
        'logoFile' => __DIR__ . '/../../frontend/assets/img/ASCLEPIUS.jpg',
    ];
}

// The brand to show the public. Falls back to the registered name so blanking 'shortName' can
// never leave a page with an empty heading.
function clinic_public_name(): string
{
    $c = clinic_info();
    $short = trim($c['shortName'] ?? '');
    return $short !== '' ? $short : $c['name'];
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
