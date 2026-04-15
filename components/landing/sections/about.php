<?php
require __DIR__ . '/../../../database/db_connect.php';

$defaultAbout = [
  'section_badge' => 'About Us',
  'section_title' => 'BarCIE International Center',
  'section_subtitle' => 'Your gateway to hospitality excellence',
  'content_title' => 'Hospitality Management Training Facility',
  'content_text_1' => "BarCIE International Center stands as La Consolacion University Philippines' premier state-of-the-art laboratory facility, specifically designed for BS Tourism Management students.",
  'content_text_2' => 'We provide comprehensive hands-on training in hotel operations, guest services, event management, and professional hospitality standards. Our facility bridges the gap between classroom theory and real-world industry experience.',
  'image_path' => 'public/images/about/barcie image about.jpg',
  'students_trained_value' => '1000+',
  'service_hours_value' => '24/7',
];

$about = $defaultAbout;

$conn->query("CREATE TABLE IF NOT EXISTS landing_about_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_badge VARCHAR(120) NOT NULL DEFAULT 'About Us',
  section_title VARCHAR(255) NOT NULL DEFAULT 'BarCIE International Center',
  section_subtitle VARCHAR(255) NOT NULL DEFAULT 'Your gateway to hospitality excellence',
  content_title VARCHAR(255) NOT NULL,
  content_text_1 TEXT NOT NULL,
  content_text_2 TEXT NOT NULL,
  image_path VARCHAR(500) NOT NULL DEFAULT 'public/images/about/barcie image about.jpg',
  students_trained_value VARCHAR(50) NOT NULL DEFAULT '1000+',
  service_hours_value VARCHAR(50) NOT NULL DEFAULT '24/7',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$countResult = $conn->query('SELECT COUNT(*) AS total FROM landing_about_content');
$hasRows = false;
if ($countResult) {
  $countRow = $countResult->fetch_assoc();
  $hasRows = ((int) ($countRow['total'] ?? 0)) > 0;
  $countResult->free();
}

if (!$hasRows) {
  $insertStmt = $conn->prepare('INSERT INTO landing_about_content
    (section_badge, section_title, section_subtitle, content_title, content_text_1, content_text_2, image_path, students_trained_value, service_hours_value, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
  if ($insertStmt) {
    $insertStmt->bind_param(
      'sssssssss',
      $defaultAbout['section_badge'],
      $defaultAbout['section_title'],
      $defaultAbout['section_subtitle'],
      $defaultAbout['content_title'],
      $defaultAbout['content_text_1'],
      $defaultAbout['content_text_2'],
      $defaultAbout['image_path'],
      $defaultAbout['students_trained_value'],
      $defaultAbout['service_hours_value']
    );
    $insertStmt->execute();
    $insertStmt->close();
  }
}

$aboutResult = $conn->query('SELECT section_badge, section_title, section_subtitle, content_title, content_text_1, content_text_2, image_path, students_trained_value, service_hours_value
  FROM landing_about_content
  WHERE is_active = 1
  ORDER BY updated_at DESC, id DESC
  LIMIT 1');
if ($aboutResult && $aboutResult->num_rows > 0) {
  $aboutRow = $aboutResult->fetch_assoc();
  foreach ($aboutRow as $key => $value) {
    if (is_string($value) && trim($value) !== '') {
      $about[$key] = $value;
    }
  }
  $aboutResult->free();
}

$roomsFacilitiesCount = 0;
$roomsFacilitiesResult = $conn->query("SELECT
    SUM(
      CASE
        WHEN LOWER(TRIM(item_type)) IN ('room', 'rooms', 'rm', 'r') OR LOWER(TRIM(item_type)) LIKE '%room%'
          THEN 1
        ELSE 0
      END
    ) AS total_rooms,
    SUM(
      CASE
        WHEN LOWER(TRIM(item_type)) IN ('facility', 'facilities', 'facilitys', 'fac', 'facil')
          OR LOWER(TRIM(item_type)) LIKE '%facil%'
          THEN 1
        ELSE 0
      END
    ) AS total_facilities
  FROM items");
if ($roomsFacilitiesResult) {
  $countRow = $roomsFacilitiesResult->fetch_assoc();
  $roomsFacilitiesCount = (int) ($countRow['total_rooms'] ?? 0) + (int) ($countRow['total_facilities'] ?? 0);
  $roomsFacilitiesResult->free();
}

$avgRating = 0.0;
$reviewsTotal = 0;
$ratingResult = $conn->query('SELECT COALESCE(ROUND(AVG(rating), 1), 0) AS avg_rating, COUNT(*) AS total_reviews FROM feedback WHERE rating IS NOT NULL');
if ($ratingResult) {
  $ratingRow = $ratingResult->fetch_assoc();
  $avgRating = (float) ($ratingRow['avg_rating'] ?? 0);
  $reviewsTotal = (int) ($ratingRow['total_reviews'] ?? 0);
  $ratingResult->free();
}

$roomsFacilitiesDisplay = $roomsFacilitiesCount > 0 ? number_format($roomsFacilitiesCount) . '+' : '0';
$serviceRatingDisplay = $reviewsTotal > 0 ? number_format($avgRating, 1) . '★' : 'N/A';
?>

<!-- About Section -->
<section id="about" class="about-section section-padding">
  <div class="container">
    <!-- Section Header -->
    <div class="row">
      <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
        <div class="section-badge mb-3">
          <i class="fas fa-star text-warning me-2"></i>
          <span><?php echo htmlspecialchars($about['section_badge'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <h2 class="section-title display-4 fw-bold mb-3"><?php echo htmlspecialchars($about['section_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="section-subtitle text-muted"><?php echo htmlspecialchars($about['section_subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
    </div>

    <!-- Content Layout -->
    <div class="row g-4 align-items-center">
      <!-- Left Side - Image -->
      <div class="col-lg-6" data-aos="fade-right">
        <div class="about-image-wrapper">
          <img src="<?php echo htmlspecialchars($about['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($about['section_title'], ENT_QUOTES, 'UTF-8'); ?>"
            class="img-fluid rounded-4 shadow-lg">
        </div>
      </div>

      <!-- Right Side - Content -->
      <div class="col-lg-6" data-aos="fade-left">
        <div class="about-content">
          <h3 class="content-title h2 fw-bold mb-4"><?php echo htmlspecialchars($about['content_title'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p class="content-text">
            <?php echo htmlspecialchars($about['content_text_1'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
          <p class="content-text">
            <?php echo htmlspecialchars($about['content_text_2'], ENT_QUOTES, 'UTF-8'); ?>
          </p>

          <!-- Stats Grid -->
          <div class="stats-grid mt-4">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-building"></i>
              </div>
              <div class="stat-info">
                <div class="stat-number"><?php echo htmlspecialchars($roomsFacilitiesDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="stat-label">Rooms & Facilities</div>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-users"></i>
              </div>
              <div class="stat-info">
                <div class="stat-number"><?php echo htmlspecialchars($about['students_trained_value'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="stat-label">Students Trained</div>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="stat-info">
                <div class="stat-number"><?php echo htmlspecialchars($about['service_hours_value'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="stat-label">Guest Services</div>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-star"></i>
              </div>
              <div class="stat-info">
                <div class="stat-number"><?php echo htmlspecialchars($serviceRatingDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="stat-label">Service Rating</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>