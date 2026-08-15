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
$contactLines = clinic_contact_lines();
$pageTitle = 'ASCLEPIUS Medical & Diagnostic Group Inc.';
$metaDescription = 'Asclepius Medical and Diagnostic Group Inc. Quality medical and diagnostic services.';

require __DIR__ . '/frontend/partials/public-header.php';
?>

  <main>
    <section class="hero-banner">
      <img src="frontend/assets/img/Doctors.webp" alt="Asclepius medical team">
      <div class="hero-overlay">
        <div class="wrap hero-caption">
          <p class="hero-eyebrow">Medical & Diagnostic Group Inc.</p>
          <h1>Quality care you can trust</h1>
          <p>Accurate diagnostics and compassionate medical services for every patient.</p>
        </div>
      </div>
    </section>

    <section class="promo-row">
      <div class="wrap promo-grid">
        <a href="#services" class="promo-tile promo-tile-primary">
          <span class="promo-label">Services offered</span>
          <strong>View our services</strong>
        </a>
        <a href="#contact" class="promo-tile promo-tile-secondary">
          <span class="promo-label">Get in touch</span>
          <strong>Contact us</strong>
        </a>
      </div>
    </section>

    <section class="whats-new">
      <div class="wrap">
        <h2 class="block-title">What's new?</h2>
        <div class="news-grid">
          <article class="news-card">
            <p class="news-tag">Clinic update</p>
            <h3>Expanded laboratory services</h3>
            <p>ASCLEPIUS now supports multi test laboratory orders with printable PDF reports.</p>
          </article>
          <article class="news-card">
            <p class="news-tag">Patient care</p>
            <h3>Walk in and online appointments</h3>
            <p>Book visits with our doctors at your convenience through our clinic team.</p>
          </article>
          <article class="news-card">
            <p class="news-tag">Diagnostics</p>
            <h3>X-ray and imaging records</h3>
            <p>Secure storage and access for diagnostic imaging linked to patient care.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="highlights">
      <div class="wrap">
        <h2 class="highlights-headline">Trusted medical & diagnostic center</h2>
        <div class="highlights-grid">
          <article class="highlight-item">
            <div class="highlight-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h3>Complete clinic services</h3>
            <p>Consultation, laboratory, imaging, dental, and billing under one group.</p>
          </article>
          <article class="highlight-item">
            <div class="highlight-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <h3>Accurate results</h3>
            <p>Laboratory and diagnostic results managed with care and professional reporting.</p>
          </article>
          <article class="highlight-item">
            <div class="highlight-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Experienced team</h3>
            <p>Doctors, lab staff, and clinic personnel working together for your health.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section" id="services">
      <div class="wrap">
        <h2 class="block-title">Services offered</h2>
        <div class="service-grid">
          <article class="service-card">
            <h3>General consultation</h3>
            <p>Medical check ups, diagnosis, and treatment with our clinic physicians.</p>
          </article>
          <article class="service-card">
            <h3>Laboratory</h3>
            <p>Clinical tests, blood work, and laboratory results for accurate diagnosis.</p>
          </article>
          <article class="service-card">
            <h3>X-ray & imaging</h3>
            <p>Diagnostic imaging to support timely medical decisions.</p>
          </article>
          <article class="service-card">
            <h3>Dental care</h3>
            <p>Dental records and diagnostic support for oral health.</p>
          </article>
          <article class="service-card">
            <h3>Prescriptions</h3>
            <p>Medication orders prepared by our clinical team.</p>
          </article>
          <article class="service-card">
            <h3>Appointments</h3>
            <p>Walk in and scheduled visits with our doctors.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-muted" id="about">
      <div class="wrap about-block">
        <div class="about-copy">
          <h2 class="block-title">About us</h2>
          <p>
            <strong><?php echo htmlspecialchars($clinic['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            is a medical and diagnostic group committed to accessible, reliable healthcare. We combine
            modern technology with compassionate service so every patient receives quality care.
          </p>
          <p>
            Our clinic information system supports coordinated care across patients, appointments,
            records, laboratory, and billing for a smoother experience.
          </p>
        </div>
        <div class="about-badge">
          <img src="<?php echo htmlspecialchars($clinic['logoWeb'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
          <p>ASCLEPIUS<br>Medical & Diagnostic Group Inc.</p>
        </div>
      </div>
    </section>

    <section class="section" id="contact">
      <div class="wrap contact-grid">
        <div>
          <h2 class="block-title">Contact us</h2>
          <p class="contact-lead">For inquiries about our services, please reach out to our clinic.</p>
          <?php if ($contactLines !== []): ?>
          <ul class="contact-list">
            <?php foreach ($contactLines as $line): ?>
            <li><?php echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="contact-note">Phone and email details coming soon.</p>
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
