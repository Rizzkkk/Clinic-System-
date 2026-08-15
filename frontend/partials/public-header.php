<?php
// Shared header for public website pages (landing, privacy, terms, etc.).
// Expects $clinic (from clinic_info()) and optional $pageTitle, $metaDescription.

$pageTitle = $pageTitle ?? 'ASCLEPIUS Medical & Diagnostic Group Inc.';
$metaDescription = $metaDescription ?? 'Asclepius Medical and Diagnostic Group Inc.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/assets/css/landing.css">
</head>
<body>

  <div class="top-bar">
    <div class="wrap top-bar-inner">
      <span>Clinic staff portal</span>
      <a href="login.php">Staff login</a>
    </div>
  </div>

  <header class="site-header">
    <div class="wrap header-inner">
      <a href="index.php" class="brand">
        <img src="<?php echo htmlspecialchars($clinic['logoWeb'], ENT_QUOTES, 'UTF-8'); ?>" alt="Asclepius logo">
      </a>
      <nav class="site-nav" aria-label="Main navigation">
        <a href="index.php">Home</a>
        <a href="index.php#services">Services</a>
        <a href="index.php#about">About us</a>
        <a href="index.php#contact">Contact</a>
        <a href="login.php" class="nav-cta">Staff login</a>
      </nav>
      <button type="button" class="nav-toggle" aria-label="Open menu" aria-expanded="false">Menu</button>
    </div>
  </header>
