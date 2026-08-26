<?php
require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();
require_once __DIR__ . '/backend/config/clinic.php';
require_once __DIR__ . '/backend/lib/escape.php';

$validRoles = ['admin', 'doctor', 'reception', 'lab', 'cashier'];
if (isset($_SESSION['user_id']) && in_array($_SESSION['user_role'] ?? '', $validRoles, true)) {
  header('Location: Dashboard.php');
  exit;
}

$clinic = clinic_info();
$publicName = clinic_public_name();
$contactLines = clinic_contact_lines();
$pageTitle = $publicName . ' - checkup, laboratory and OFW medical services';
$metaDescription = 'Asclepius Clinic & Laboratory: annual physical exams, blood work, X-ray and imaging, and OFW pre-employment medical examinations from a DOH-licensed facility in the Philippines.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

  <main>
    <section class="hero">
      <div class="wrap hero-grid">
        <div class="hero-copy">
          <p class="eyebrow">DOH-licensed clinic &amp; diagnostic laboratory</p>
          <h1>Among the Philippines' most trusted checkup and laboratory centres</h1>
          <p class="hero-lead">
            Annual physical exams, blood work, imaging and OFW pre-deployment medicals, handled by
            licensed physicians and medical technologists, with results you can read online.
          </p>
          <div class="hero-actions">
            <a href="#book" class="btn btn-primary">Book a checkup</a>
            <a href="#ofw" class="btn btn-outline">OFW medical exam</a>
          </div>
          <ul class="hero-points">
            <li>Walk-in and scheduled appointments</li>
            <li>Results released through the patient portal</li>
          </ul>
        </div>
        <div class="hero-media">
          <img src="frontend/assets/img/Doctors.webp" alt="Clinic doctors and medical technologists at work" width="720" height="480">
          <div class="hero-badge">
            <span class="hero-badge-icon" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l8 4v6c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10V6z"/><polyline points="9 12 11 14 15 10"/></svg>
            </span>
            <div>
              <strong>DOH-licensed facility</strong>
              <?php if (trim($clinic['dohLicense']) !== ''): ?>
              <small><?php echo h($clinic['dohLicense']); ?></small>
              <?php else: ?>
              <small>Licensed physicians and med techs</small>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="trust-strip">
      <div class="wrap trust-grid">
        <article class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l8 4v6c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10V6z"/><polyline points="9 12 11 14 15 10"/></svg>
          </span>
          <div>
            <h3>DOH-licensed facility</h3>
            <p>Operated under Department of Health licensing for clinical laboratory services.</p>
          </div>
        </article>
        <article class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8"/><path d="M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18z"/></svg>
          </span>
          <div>
            <h3>OFW medical examinations</h3>
            <p>Pre-employment and pre-deployment exams for workers bound for posts abroad.</p>
          </div>
        </article>
        <article class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
          </span>
          <div>
            <h3>Same-day routine results</h3>
            <p>Most routine laboratory panels are released on the same working day.</p>
          </div>
        </article>
        <article class="trust-item">
          <span class="trust-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          <div>
            <h3>Licensed clinical team</h3>
            <p>Physicians, pathologists, radiologic technologists and medical technologists.</p>
          </div>
        </article>
      </div>
    </section>

    <?php // TODO(owner): these four figures are placeholders. Replace them with the clinic's real
          // numbers, or delete the section, before the site goes public. ?>
    <section class="stats-band">
      <div class="wrap stats-grid">
        <div class="stat"><strong>15+</strong><span>Years serving patients</span></div>
        <div class="stat"><strong>120k+</strong><span>Laboratory tests processed</span></div>
        <div class="stat"><strong>40+</strong><span>Partner manning agencies</span></div>
        <div class="stat"><strong>98%</strong><span>Patients who would return</span></div>
      </div>
    </section>

    <section class="section" id="services">
      <div class="wrap">
        <p class="eyebrow eyebrow-center">What we do</p>
        <h2 class="block-title block-title-center">Checkup, laboratory and diagnostic services</h2>
        <div class="service-grid">
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.8 6.6a5 5 0 0 0-7.1 0L12 8.3l-1.7-1.7a5 5 0 1 0-7.1 7.1L12 22l8.8-8.3a5 5 0 0 0 0-7.1z"/></svg>
            </span>
            <h3>Annual physical exam</h3>
            <p>Complete checkup packages for individuals, families and company employees.</p>
            <a href="#book" class="link-more">Book a checkup</a>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 2v6.5L4.8 16A3.5 3.5 0 0 0 7.8 21h8.4a3.5 3.5 0 0 0 3-5L15 8.5V2"/><line x1="8" y1="2" x2="16" y2="2"/><line x1="6" y1="15" x2="18" y2="15"/></svg>
            </span>
            <h3>Laboratory &amp; blood work</h3>
            <p>CBC, chemistry, urinalysis, fecalysis and other clinical panels with printed reports.</p>
            <a href="#book" class="link-more">Request a test</a>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 7v10M8 10c2.5 1.5 5.5 1.5 8 0M8 14c2.5 1.5 5.5 1.5 8 0"/></svg>
            </span>
            <h3>X-ray &amp; imaging</h3>
            <p>Chest X-ray and diagnostic imaging read by our radiologists.</p>
            <a href="#book" class="link-more">Schedule imaging</a>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
            </span>
            <h3>Pre-employment &amp; OFW medical</h3>
            <p>Fit-to-work examinations for local hires and for workers deploying abroad.</p>
            <a href="#ofw" class="link-more">See what is included</a>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5.5c-1.5-1.6-4-2.3-5.7-1C4.4 5.9 4 8.6 5 11.4c.7 2 .8 3.5 1 5.4.2 1.6 1.6 2.2 2.4 1 .8-1.2.9-3.2 3.6-3.2s2.8 2 3.6 3.2c.8 1.2 2.2.6 2.4-1 .2-1.9.3-3.4 1-5.4 1-2.8.6-5.5-1.3-6.9-1.7-1.3-4.2-.6-5.7 1z"/></svg>
            </span>
            <h3>Dental care</h3>
            <p>Dental screening and records, including the dental clearance an OFW exam requires.</p>
            <a href="#book" class="link-more">Book dental</a>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="12" y1="7" x2="12" y2="13"/><line x1="9" y1="10" x2="15" y2="10"/></svg>
            </span>
            <h3>Doctor consultation</h3>
            <p>Consult a physician about your results, treatment plan and prescriptions.</p>
            <a href="#book" class="link-more">See a doctor</a>
          </article>
        </div>
      </div>
    </section>

    <section class="ofw" id="ofw">
      <div class="wrap ofw-grid">
        <div class="ofw-copy">
          <p class="eyebrow">For workers deploying abroad</p>
          <h2>OFW pre-employment and pre-deployment medical examinations</h2>
          <p>
            We handle the fit-to-work medical required before deployment, for both land-based
            workers and seafarers. Applicants are processed in one visit wherever the package
            allows, and the clinic coordinates directly with your recruitment or manning agency
            so the results reach them without you chasing paperwork.
          </p>
          <ul class="ofw-list">
            <li>Physical examination and medical history</li>
            <li>Chest X-ray and standard laboratory panels</li>
            <li>Dental screening and clearance</li>
            <li>Vision, hearing and vital-sign assessment</li>
            <li>Agency-referred processing and result endorsement</li>
          </ul>
          <?php if (trim($clinic['ofwAccreditation']) !== ''): ?>
          <p class="ofw-note"><?php echo h($clinic['ofwAccreditation']); ?></p>
          <?php endif; ?>
          <div class="hero-actions">
            <a href="#book" class="btn btn-primary">Book an OFW medical</a>
            <a href="#contact" class="btn btn-outline">Talk to the clinic</a>
          </div>
        </div>
        <aside class="ofw-card">
          <h3>Recruitment and manning agencies</h3>
          <p>
            We accept agency-referred applicants in batches and endorse the completed results back
            to your coordinator. Contact the clinic to set up a referral arrangement.
          </p>
          <a href="#contact" class="link-more link-more-light">Arrange agency referrals</a>
        </aside>
      </div>
    </section>

    <section class="section section-muted">
      <div class="wrap">
        <p class="eyebrow eyebrow-center">Simple process</p>
        <h2 class="block-title block-title-center">How it works</h2>
        <ol class="steps-grid">
          <li class="step">
            <span class="step-number">1</span>
            <h3>Book or walk in</h3>
            <p>Reserve a slot with the clinic, or come in during clinic hours for routine tests.</p>
          </li>
          <li class="step">
            <span class="step-number">2</span>
            <h3>Get examined</h3>
            <p>Our physicians and medical technologists carry out your consultation and tests.</p>
          </li>
          <li class="step">
            <span class="step-number">3</span>
            <h3>Read your results online</h3>
            <p>Signed-off results, prescriptions and bills appear in your patient portal account.</p>
          </li>
        </ol>
      </div>
    </section>

    <section class="section" id="about">
      <div class="wrap about-block">
        <div class="about-copy">
          <p class="eyebrow">About us</p>
          <h2 class="block-title"><?php echo h($publicName); ?></h2>
          <p>
            <strong><?php echo h($clinic['name']); ?></strong> is a medical and diagnostic group
            committed to accessible, reliable healthcare. We combine modern laboratory equipment
            with careful clinical practice so that every patient, whether here for a routine
            checkup or a deployment medical, gets an accurate result and a straight answer.
          </p>
          <p>
            Consultation, laboratory, imaging, dental and billing run on one clinic information
            system, which is why your records stay together and your results reach you and your
            doctor quickly.
          </p>
          <?php if (trim($clinic['dohLicense']) !== ''): ?>
          <p class="about-license"><?php echo h($clinic['dohLicense']); ?></p>
          <?php endif; ?>
        </div>
        <div class="about-badge">
          <img src="<?php echo h($clinic['logoWeb']); ?>" alt="">
          <p><?php echo h($clinic['name']); ?></p>
        </div>
      </div>
    </section>

    <section class="book-band" id="book">
      <div class="wrap book-inner">
        <div class="book-copy">
          <h2>Ready to book your checkup?</h2>
          <p>
            Create a patient portal account to request an appointment and follow your results, or
            call the clinic and our reception team will book you in.
          </p>
        </div>
        <div class="book-actions">
<?php if (($_SESSION['user_role'] ?? '') === 'patient'): ?>
          <a href="Portal%20Appointments.php" class="btn btn-primary">Request an appointment</a>
          <a href="Portal.php" class="btn btn-outline-light">Go to my portal</a>
<?php else: ?>
          <a href="Portal%20Register.php" class="btn btn-primary">Create a patient account</a>
          <a href="Portal.php" class="btn btn-outline-light">I already have an account</a>
<?php endif; ?>
<?php if (trim($clinic['phone']) !== ''): ?>
          <p class="book-phone">Or call <strong><?php echo h($clinic['phone']); ?></strong></p>
<?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section" id="contact">
      <div class="wrap contact-grid">
        <div>
          <p class="eyebrow">Get in touch</p>
          <h2 class="block-title">Contact us</h2>
          <p class="contact-lead">For inquiries about our services, packages or agency referrals, please reach out to the clinic.</p>
          <?php if ($contactLines !== []): ?>
          <ul class="contact-list">
            <?php foreach ($contactLines as $line): ?>
            <li><?php echo h($line); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="contact-note">Phone and email details coming soon.</p>
          <?php endif; ?>
          <?php if (trim($clinic['hours']) !== ''): ?>
          <p class="contact-hours">Clinic hours: <?php echo h($clinic['hours']); ?></p>
          <?php endif; ?>
        </div>
        <aside class="staff-box">
          <h3>Clinic staff</h3>
          <p>Authorized personnel may sign in to the hospital information system.</p>
          <a href="login.php" class="btn btn-primary">Staff login</a>
        </aside>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/frontend/partials/public-footer.php'; ?>
