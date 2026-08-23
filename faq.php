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
$pageTitle = 'FAQs | ASCLEPIUS';
$metaDescription = 'Frequently asked questions about ASCLEPIUS Medical and Diagnostic Group Inc.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

<main class="legal-page">
  <div class="wrap legal-content">
    <h1>Frequently asked questions</h1>

    <section class="faq-list">
      <article class="faq-item">
        <h2>What services does ASCLEPIUS offer?</h2>
        <p>
          We provide general consultation, laboratory testing, x-ray and imaging, dental care,
          prescriptions, and appointment scheduling. Visit our
          <a href="index.php#services">services page</a> for details.
        </p>
      </article>

      <article class="faq-item">
        <h2>How do I book an appointment?</h2>
        <p>
          Contact our clinic through the details on our
          <a href="index.php#contact">contact page</a>. Our reception team can help you schedule
          a walk in or booked visit.
        </p>
      </article>

      <article class="faq-item">
        <h2>How do I get my laboratory results?</h2>
        <p>
          Laboratory results are prepared by our clinic team. Please contact the clinic directly
          for result release and follow up with your doctor as advised.
        </p>
      </article>

      <article class="faq-item">
        <h2>Who can access the staff portal?</h2>
        <p>
          Only authorized clinic staff with assigned accounts can sign in. Staff accounts cannot be
          created by signing up; if you are an employee and need access, ask your clinic
          administrator to create one for you.
        </p>
      </article>

      <article class="faq-item">
        <h2>How do you protect my data?</h2>
        <p>
          We use role based access, secure login, and clinic policies to protect patient and staff
          information. Read our <a href="privacy.php">privacy policy</a> for more details.
        </p>
      </article>

      <article class="faq-item">
        <h2>How do I manage cookies on this website?</h2>
        <p>
          You can update your choices anytime on our
          <a href="cookies.php">cookie preferences</a> page.
        </p>
      </article>
    </section>
  </div>
</main>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
