<?php
// Closes a patient portal page opened by portal-nav.php, and carries the shared SweetAlert2
// feedback overlay. Portal pages are plain form POSTs, so a page sets $portalError /
// $portalSuccess before including this and the result is shown after the reload -- the same
// convention login.php and register.php use (never an inline message box).

$portalError = $portalError ?? '';
$portalSuccess = $portalSuccess ?? '';
?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="wrap footer-links">
      <a href="index.php">Clinic website</a>
      <a href="privacy.php">Privacy policy</a>
      <a href="terms.php">Terms of use</a>
      <a href="index.php#contact">Contact us</a>
    </div>
    <div class="wrap footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> <?php echo h($clinic['name']); ?>. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($portalError !== ''): ?>
  <script>Swal.fire({ icon: 'error', title: 'Something went wrong', text: <?php echo json_encode($portalError); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
  <?php if ($portalSuccess !== ''): ?>
  <script>Swal.fire({ icon: 'success', title: 'Saved', text: <?php echo json_encode($portalSuccess); ?>, confirmButtonColor: '#00685d' });</script>
  <?php endif; ?>
</body>
</html>
