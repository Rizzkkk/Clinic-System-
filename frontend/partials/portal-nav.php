<?php
// Shared chrome for the patient portal: doctype through the end of the nav strip. The staff
// sidebar (frontend/partials/sidebar.php) is deliberately NOT reused -- it is built from staff
// module keys and filtered with can_access(), which is meaningless for a patient, and it would
// show clinic staff chrome to a member of the public.
//
// Usage, after require_patient() has run:
//     $active = 'results';
//     require __DIR__ . '/frontend/partials/portal-nav.php';
// Close the page with frontend/partials/portal-footer.php.

require_once __DIR__ . '/../../backend/config/clinic.php';
require_once __DIR__ . '/../../backend/lib/escape.php';

$clinic = clinic_info();
$active = $active ?? '';
$pageTitle = $pageTitle ?? 'Patient portal';

// [nav key, href, label]
$portalLinks = [
    ['home',          'Portal.php',               'Home'],
    ['appointments',  'Portal Appointments.php',  'Appointments'],
    ['results',       'Portal Results.php',       'Lab results'],
    ['prescriptions', 'Portal Prescriptions.php', 'Prescriptions'],
    ['billing',       'Portal Billing.php',       'Bills'],
    ['profile',       'Portal Profile.php',       'My details'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?php echo h($pageTitle); ?> - <?php echo h($clinic['name']); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/assets/css/landing.css">
  <link rel="stylesheet" href="frontend/assets/css/portal.css">
</head>
<body class="portal-body">

  <header class="portal-header">
    <div class="wrap portal-header-inner">
      <a href="Portal.php" class="portal-brand">
        <img src="<?php echo h($clinic['logoWeb']); ?>" alt="">
        <span><?php echo h($clinic['name']); ?><br>Patient portal</span>
      </a>
      <span class="portal-who">Signed in as <?php echo h($_SESSION['user_name'] ?? ''); ?></span>
    </div>
  </header>

  <nav class="portal-nav" aria-label="Patient portal">
    <div class="wrap portal-nav-inner">
      <?php foreach ($portalLinks as $link): ?>
        <?php list($key, $href, $label) = $link; ?>
        <a href="<?php echo h($href); ?>"<?php echo $active === $key ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo h($label); ?></a>
      <?php endforeach; ?>
      <a href="logout.php" class="portal-signout">Sign out</a>
    </div>
  </nav>

  <main class="portal-main">
    <div class="wrap">
