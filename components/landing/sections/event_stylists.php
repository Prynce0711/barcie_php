<?php
require __DIR__ . '/../../../database/db_connect.php';

$partners = [];
$result = $conn->query("SELECT name, facebook_url, phones, image_path FROM landing_partners WHERE category = 'event_stylist' AND is_active = 1 ORDER BY sort_order ASC, id ASC");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $partners[] = $row;
  }
  $result->free();
}

$partnerFallbackLogo = defined('BARCIE_LOGO_URL')
  ? (string) BARCIE_LOGO_URL
  : ((defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/public/images/imageBg/barcie_logo.jpg');
?>
<section id="event-stylists" class="partners-section section-padding">
  <div class="container">
    <h2 class="section-title text-center mb-5">Partner Event Stylists</h2>

    <div class="row partners-grid g-4">
      <?php foreach ($partners as $partner): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="card glass-card h-100">
            <div class="partner-logo-wrap">
              <img
                src="<?php echo htmlspecialchars($partner['image_path'] ?: $partnerFallbackLogo, ENT_QUOTES, 'UTF-8'); ?>"
                class="card-img-top" alt="<?php echo htmlspecialchars($partner['name']); ?>"
                onerror="this.onerror=null;this.src=(window.BARCIE_LOGO_ALT_URL || window.BARCIE_LOGO_URL || '<?php echo htmlspecialchars($partnerFallbackLogo, ENT_QUOTES, 'UTF-8'); ?>');">
            </div>
            <div class="card-body">
              <h5 class="card-title partner-title">
                <?php if (!empty($partner['facebook_url'])): ?>
                  <a href="<?php echo htmlspecialchars($partner['facebook_url']); ?>" target="_blank"
                    rel="noopener"><?php echo htmlspecialchars($partner['name']); ?></a>
                <?php else: ?>
                  <?php echo htmlspecialchars($partner['name']); ?>
                <?php endif; ?>
              </h5>
              <p class="partner-phones mb-0"><?php echo nl2br(htmlspecialchars((string) ($partner['phones'] ?? ''))); ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>