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
$pageTitle = 'Terms of use | ' . $publicName;
$metaDescription = 'Terms of use for the Asclepius Clinic & Laboratory website and patient portal.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow">Legal</p>
    <h1>Terms of use</h1>
    <p>The rules that apply when you use this website and the patient portal.</p>
  </div>
</section>

<main class="legal-page">
  <div class="wrap legal-content">
    <p class="legal-updated">Last updated: <?php echo date('F j, Y'); ?></p>

    <section>
      <h2>1. Acceptance</h2>
      <p>
        By accessing this website, you agree to these terms. If you do not agree, please do not
        use the site.
      </p>
    </section>

    <section>
      <h2>2. Website purpose</h2>
      <p>
        This website provides general information about <?php echo htmlspecialchars($clinic['name'], ENT_QUOTES, 'UTF-8'); ?>
        and access to the staff portal for authorized clinic personnel. It does not replace
        professional medical advice.
      </p>
    </section>

    <section>
      <h2>3. Staff portal</h2>
      <p>
        The staff login area is for authorized clinic employees only. You must not share your
        login credentials or attempt to access areas outside your assigned role.
      </p>
    </section>

    <section>
      <h2>4. Acceptable use</h2>
      <p>You agree not to:</p>
      <ul>
        <li>Use the website for unlawful purposes</li>
        <li>Attempt to gain unauthorized access to systems or data</li>
        <li>Disrupt or interfere with the website or clinic services</li>
        <li>Misrepresent your identity or affiliation</li>
      </ul>
    </section>

    <section>
      <h2>5. Intellectual property</h2>
      <p>
        Content on this website, including logos, text, and design, belongs to ASCLEPIUS unless
        otherwise stated. You may not copy or reuse it without permission.
      </p>
    </section>

    <section>
      <h2>6. Disclaimer</h2>
      <p>
        Information on this website is provided in good faith. We do not guarantee that the site
        will be error free or always available. Medical decisions should be made with qualified
        healthcare professionals.
      </p>
    </section>

    <section>
      <h2>7. Changes</h2>
      <p>
        We may update these terms from time to time. Continued use of the website after changes
        means you accept the updated terms.
      </p>
    </section>

    <section>
      <h2>8. Contact</h2>
      <p>
        Questions about these terms? Visit our <a href="index.php#contact">contact page</a>.
      </p>
    </section>
  </div>
</main>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
