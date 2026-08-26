<?php
// Shared footer for public website pages. The patient portal's own footer partial reuses
// .site-footer / .footer-links / .footer-bottom, so those class names must stay.
require_once __DIR__ . '/../../backend/lib/escape.php';
require_once __DIR__ . '/../../backend/config/clinic.php';

$footerClinic = clinic_info();
$footerContact = clinic_contact_lines();
?>
  <footer class="site-footer">
    <div class="wrap footer-columns">
      <div class="footer-brand">
        <p class="footer-wordmark"><?php echo h(clinic_public_name()); ?></p>
        <p class="footer-blurb">
          Checkup, laboratory, imaging and OFW medical services from a DOH-licensed clinic and
          diagnostic laboratory.
        </p>
        <?php if (trim($footerClinic['dohLicense']) !== ''): ?>
        <p class="footer-license"><?php echo h($footerClinic['dohLicense']); ?></p>
        <?php endif; ?>
      </div>
      <div class="footer-col">
        <h2>Clinic</h2>
        <ul>
          <li><a href="index.php#about">About us</a></li>
          <li><a href="index.php#services">Services</a></li>
          <li><a href="index.php#ofw">OFW medical</a></li>
          <li><a href="index.php#book">Book a checkup</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h2>Services</h2>
        <ul>
          <li><a href="index.php#services">Annual physical exam</a></li>
          <li><a href="index.php#services">Laboratory &amp; blood work</a></li>
          <li><a href="index.php#services">X-ray &amp; imaging</a></li>
          <li><a href="index.php#services">Dental care</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h2>Contact</h2>
        <?php if ($footerContact !== []): ?>
        <ul class="footer-contact">
          <?php foreach ($footerContact as $line): ?>
          <li><?php echo h($line); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="footer-blurb">Phone and email details coming soon.</p>
        <?php endif; ?>
        <?php if (trim($footerClinic['hours']) !== ''): ?>
        <p class="footer-blurb"><?php echo h($footerClinic['hours']); ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="wrap footer-links">
      <a href="index.php#about">About us</a>
      <a href="privacy.php">Privacy policy</a>
      <a href="cookies.php">Cookie preferences</a>
      <a href="terms.php">Terms of use</a>
      <a href="faq.php">FAQs</a>
      <a href="index.php#contact">Contact us</a>
      <a href="Portal.php">Patient portal</a>
      <a href="login.php">Staff login</a>
    </div>
    <div class="wrap footer-bottom">
      <?php // the registered name already ends in "Inc." -- rtrim avoids "Inc.. All rights reserved." ?>
      <p>&copy; <?php echo date('Y'); ?> <?php echo h(rtrim($footerClinic['name'], '.')); ?>. All rights reserved.</p>
    </div>
  </footer>

  <div class="cookie-banner" id="cookie-banner" hidden>
    <div class="cookie-banner-inner wrap">
      <p>
        We use cookies to keep the site working and to remember your preferences.
        <a href="cookies.php">Learn more</a>
      </p>
      <div class="cookie-banner-actions">
        <button type="button" class="btn btn-outline-light" id="cookie-reject">Essential only</button>
        <button type="button" class="btn btn-primary" id="cookie-accept">Accept all</button>
      </div>
    </div>
  </div>

  <script>
    document.querySelector('.nav-toggle')?.addEventListener('click', function () {
      const nav = document.querySelector('.site-nav');
      const open = nav.classList.toggle('is-open');
      this.setAttribute('aria-expanded', open ? 'true' : 'false');
      this.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
  </script>
  <script src="frontend/assets/js/cookies.js"></script>
</body>
</html>
