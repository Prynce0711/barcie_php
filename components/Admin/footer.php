<!-- Footer -->
<div class="footer">
  <p>&copy; <?php echo date("Y"); ?> Hotel Management System</p>
</div>

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

</body>

</html>