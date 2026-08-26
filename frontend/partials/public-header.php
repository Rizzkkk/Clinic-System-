<?php
// Shared header for public website pages (landing, privacy, terms, etc.).
// Expects $clinic (from clinic_info()) and optional $pageTitle, $metaDescription.
// $extraStyles lets one page pull in an additional stylesheet (the patient signup form needs
// portal.css) without loading it on every marketing page.

require_once __DIR__ . '/../../backend/lib/escape.php';
require_once __DIR__ . '/../../backend/config/clinic.php';

$publicName = clinic_public_name();
$headerContact = clinic_contact_lines();

$pageTitle = $pageTitle ?? $publicName;
$metaDescription = $metaDescription ?? 'Checkup, laboratory, imaging and OFW medical examinations from a DOH-licensed clinic and diagnostic laboratory.';
$extraStyles = $extraStyles ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo h($metaDescription); ?>">
  <title><?php echo h($pageTitle); ?></title>
  <link rel="icon" href="frontend/assets/img/ASCLEPIUS.jpg">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?php echo h($publicName); ?>">
  <meta property="og:title" content="<?php echo h($pageTitle); ?>">
  <meta property="og:description" content="<?php echo h($metaDescription); ?>">
  <meta name="twitter:card" content="summary">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/assets/css/landing.css">
<?php foreach ($extraStyles as $href): ?>
  <link rel="stylesheet" href="<?php echo h($href); ?>">
<?php endforeach; ?>
</head>
<body>

  <div class="top-bar">
    <div class="wrap top-bar-inner">
<?php if ($headerContact !== []): ?>
      <span class="top-bar-contact"><?php echo h(implode('  |  ', $headerContact)); ?></span>
<?php else: ?>
      <span class="top-bar-contact">DOH-licensed clinic and diagnostic laboratory</span>
<?php endif; ?>
      <span class="top-bar-links">
        <a href="Portal.php">Patient portal</a>
        <a href="login.php">Staff login</a>
      </span>
    </div>
  </div>

  <header class="site-header">
    <div class="wrap header-inner">
      <a href="index.php" class="brand">
        <img src="<?php echo h($clinic['logoWeb']); ?>" alt="">
        <span class="brand-text">
          <strong><?php echo h($publicName); ?></strong>
          <small><?php echo h($clinic['tagline']); ?></small>
        </span>
      </a>
      <nav class="site-nav" aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="index.php#services">Services</a>
        <a href="index.php#ofw">OFW medical</a>
        <a href="index.php#about">About us</a>
        <a href="faq.php">FAQs</a>
        <a href="index.php#contact">Contact</a>
<?php if (($_SESSION['user_role'] ?? '') === 'patient'): ?>
        <a href="Portal.php" class="nav-cta">My portal</a>
<?php else: ?>
        <a href="index.php#book" class="nav-cta">Book a checkup</a>
<?php endif; ?>
      </nav>
      <button type="button" class="nav-toggle" aria-label="Open menu" aria-expanded="false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
    </div>
  </header>
