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
$pageTitle = 'Privacy policy | ' . $publicName;
$metaDescription = 'How Asclepius Clinic & Laboratory collects, uses and protects personal and health information.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow">Legal</p>
    <h1>Privacy policy</h1>
    <p>How we handle the personal and health information you entrust to the clinic.</p>
  </div>
</section>

<main class="legal-page">
  <div class="wrap legal-content">
    <p class="legal-updated">Last updated: <?php echo date('F j, Y'); ?></p>

    <section>
      <h2>1. Introduction</h2>
      <p>
        <?php echo htmlspecialchars($clinic['name'], ENT_QUOTES, 'UTF-8'); ?>, operating as
        <?php echo htmlspecialchars($publicName, ENT_QUOTES, 'UTF-8'); ?> ("we", "us"),
        respects your privacy. This policy explains how we collect, use, and protect personal
        information when you visit our website or use our clinic services.
      </p>
    </section>

    <section>
      <h2>2. Information we collect</h2>
      <p>We may collect the following types of information:</p>
      <ul>
        <li>Contact details you provide when reaching out to our clinic</li>
        <li>Patient and medical information when you receive our services</li>
        <li>Website usage data through cookies (see our cookie preferences page)</li>
        <li>Staff account information for authorized clinic personnel</li>
      </ul>
    </section>

    <section>
      <h2>3. How we use your information</h2>
      <p>We use personal information to:</p>
      <ul>
        <li>Provide medical and diagnostic services</li>
        <li>Manage appointments, records, laboratory results, and billing</li>
        <li>Operate and improve our website and clinic systems</li>
        <li>Comply with applicable laws and regulatory requirements</li>
      </ul>
    </section>

    <section>
      <h2>4. Data protection</h2>
      <p>
        We apply reasonable technical and organizational safeguards to protect personal and
        health information. Access to patient data is restricted to authorized staff based on
        their role in the clinic.
      </p>
    </section>

    <section>
      <h2>5. Sharing of information</h2>
      <p>
        We do not sell personal information. We may share data only when required for patient care,
        with your consent, or as required by law.
      </p>
    </section>

    <section>
      <h2>6. Your rights</h2>
      <p>
        You may request access to, correction of, or deletion of your personal information where
        applicable. Contact us using the details on our website.
      </p>
    </section>

    <section>
      <h2>7. Contact</h2>
      <p>
        For privacy related questions, please reach out through the contact section on our
        <a href="index.php#contact">homepage</a>.
      </p>
    </section>
  </div>
</main>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
