  <footer class="site-footer">
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
      <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($clinic['name'], ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
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
    });
  </script>
  <script src="frontend/assets/js/cookies.js"></script>
</body>
</html>
