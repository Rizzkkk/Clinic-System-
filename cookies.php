<?php
require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();
require_once __DIR__ . '/backend/config/clinic.php';

$validRoles = ['admin', 'doctor', 'reception', 'lab', 'cashier'];
if (isset($_SESSION['user_id']) && in_array($_SESSION['user_role'] ?? '', $validRoles, true)) {
  header('Location: Dashboard.php');
  exit;
}

$clinic = clinic_info();
$publicName = clinic_public_name();
$pageTitle = 'Cookie preferences | ' . $publicName;
$metaDescription = 'Manage cookie preferences for the Asclepius Clinic & Laboratory website.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow">Legal</p>
    <h1>Cookie preferences</h1>
    <p>Cookies are small text files stored on your device. They help this website remember your settings.</p>
  </div>
</section>

<main class="legal-page">
  <div class="wrap">
    <p class="legal-updated">Last updated: <?php echo date('F j, Y'); ?></p>

    <section class="cookie-prefs">
      <h2>Manage your preferences</h2>

      <div class="cookie-option">
        <div>
          <strong>Essential cookies</strong>
          <p>Required for the website and staff portal to function. Always enabled.</p>
        </div>
        <span class="cookie-badge">Always on</span>
      </div>

      <div class="cookie-option">
        <div>
          <strong>Analytics cookies</strong>
          <p>Help us understand how visitors use the site so we can improve it.</p>
        </div>
        <label class="cookie-toggle">
          <input type="checkbox" id="pref-analytics">
          <span>Allow analytics</span>
        </label>
      </div>

      <div class="cookie-actions">
        <button type="button" class="btn btn-primary" id="cookie-save">Save preferences</button>
        <p class="cookie-status" id="cookie-save-status" aria-live="polite"></p>
      </div>
    </section>

    <div class="legal-content">
      <section>
        <h2>More information</h2>
        <p>
          For details on how we handle personal data, see our
          <a href="privacy.php">privacy policy</a>.
        </p>
      </section>
    </div>
  </div>
</main>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
