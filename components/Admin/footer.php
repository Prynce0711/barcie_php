<?php
$logoUrl = defined('BARCIE_LOGO_URL')
  ? (string) BARCIE_LOGO_URL
  : ((defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/public/images/imageBg/barcie_logo.jpg');
?>
<footer class="footer border-top py-3 bg-light">
  <div
    class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 small text-muted">
    <div class="d-flex align-items-center gap-2">
      <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Barcie" width="28" height="28"
        class="rounded-circle">
      <span class="fw-semibold text-dark">Hotel Management Management</span>
      <span class="text-secondary">© <?php echo date('Y'); ?> Barcie</span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span class="badge bg-success-subtle text-success border border-success-subtle">Status: Online</span>
    </div>
  </div>
</footer>

<!-- Load JavaScript files at the end of body for better performance -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<?php include __DIR__ . '/../Popup/ConfirmPopup.php'; ?>
<?php include __DIR__ . '/../Popup/ErrorPopup.php'; ?>
<?php include __DIR__ . '/../Popup/LoadingPopup.php'; ?>
<?php include __DIR__ . '/../Popup/SuccessPopup.php'; ?>
<script src="Components/Popup/popup-manager.js"></script>
<script src="Components/Admin/Dashboard/dashboard-bootstrap.js"></script>
<!-- Unified Table Pagination -->
<script src="Components/Table/table.js"></script>

<!-- Motion One (vanilla JS animation library) -->
<script src="https://cdn.jsdelivr.net/npm/motion@latest/dist/motion.min.js"></script>
<script src="assets/js/motion-animations.js"></script>

</body>

</html>