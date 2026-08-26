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
$pageTitle = 'FAQs | ' . $publicName;
$metaDescription = 'Answers to common questions about checkups, laboratory results, OFW medical examinations and the patient portal at Asclepius Clinic & Laboratory.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow">Help centre</p>
    <h1>Frequently asked questions</h1>
    <p>Checkups, laboratory results, OFW medicals and your patient portal account.</p>
  </div>
</section>

<main class="legal-page">
  <div class="wrap">
    <div class="faq-list">
      <details class="faq-item">
        <summary>What services does the clinic offer?</summary>
        <p>
          Annual physical exams, laboratory and blood work, X-ray and imaging, dental care,
          doctor consultation and prescriptions, plus pre-employment and OFW medical
          examinations. See the full list on our <a href="index.php#services">services section</a>.
        </p>
      </details>

      <details class="faq-item">
        <summary>How do I book an appointment?</summary>
        <p>
          Create a <a href="Portal%20Register.php">patient portal account</a> and request an
          appointment online, or contact the clinic using the details on our
          <a href="index.php#contact">contact section</a> and our reception team will book you in.
          Walk-ins are also accepted during clinic hours.
        </p>
      </details>

      <details class="faq-item">
        <summary>What is included in an OFW medical examination?</summary>
        <p>
          A pre-employment or pre-deployment exam typically covers a physical examination and
          medical history, chest X-ray, standard laboratory panels, dental screening, and vision,
          hearing and vital-sign assessment. The exact requirements depend on your destination
          country and employer, so bring your agency's requirement list with you. See the
          <a href="index.php#ofw">OFW medical section</a> for more.
        </p>
      </details>

      <details class="faq-item">
        <summary>Can my recruitment or manning agency refer applicants?</summary>
        <p>
          Yes. We accept agency-referred applicants and endorse completed results back to your
          coordinator. Contact the clinic to set up a referral arrangement.
        </p>
      </details>

      <details class="faq-item">
        <summary>How do I get my laboratory results?</summary>
        <p>
          Once your results are signed off, they appear in your patient portal account under
          Lab results, and you can print a copy from there. You may also collect a printed report
          at the clinic. Most routine panels are released on the same working day.
        </p>
      </details>

      <details class="faq-item">
        <summary>Who can access the staff portal?</summary>
        <p>
          Only authorized clinic staff with assigned accounts can sign in. Staff accounts cannot be
          created by signing up; if you are an employee and need access, ask your clinic
          administrator to create one for you.
        </p>
      </details>

      <details class="faq-item">
        <summary>How do you protect my data?</summary>
        <p>
          We use role-based access, secure login, and clinic policies to protect patient and staff
          information. Read our <a href="privacy.php">privacy policy</a> for more details.
        </p>
      </details>

      <details class="faq-item">
        <summary>How do I manage cookies on this website?</summary>
        <p>
          You can update your choices anytime on our
          <a href="cookies.php">cookie preferences</a> page.
        </p>
      </details>
    </div>
  </div>
</main>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
