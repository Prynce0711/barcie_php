<?php
require_once __DIR__ . '/../../../database/config.php';

$brochures = [];
$result = $conn->query("SELECT title, image_path, download_name FROM landing_brochures WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $brochures[] = $row;
  }
  $result->free();
}

if (count($brochures) === 0) {
  $brochures[] = [
    'title' => 'Brochure 1',
    'image_path' => 'public/images/brochure/brochure 1.png',
    'download_name' => 'BarCIE-Brochure-Page-1.png'
  ];
}

$totalBrochures = count($brochures);
$firstDownload = $brochures[0]['download_name'] ?: basename($brochures[0]['image_path']);
?>

<!-- Brochure Section -->
<section id="brochure" class="section-padding bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-5 fw-bold text-dark mb-3">Our Brochure</h2>
      <p class="lead text-muted">Explore our complete services and offerings</p>
    </div>

    <div class="brochure-container">
      <!-- Navigation Buttons -->
      <div class="brochure-nav d-flex justify-content-center gap-3 mb-4">
        <?php foreach ($brochures as $index => $brochure): ?>
          <?php $page = $index + 1; ?>
          <button class="btn <?php echo $page === 1 ? 'btn-primary-custom active' : 'btn-outline-custom'; ?>"
            data-brochure="<?php echo $page; ?>" onclick="switchBrochure(<?php echo $page; ?>)">
            <i
              class="fas fa-book-open me-2"></i><?php echo htmlspecialchars($brochure['title'] ?: ('Brochure ' . $page)); ?>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Brochure Images -->
      <div class="brochure-viewer text-center position-relative">
        <div class="brochure-image-wrapper position-relative overflow-hidden">
          <div class="brochure-carousel d-flex" style="transition: transform 0.6s ease-in-out;">
            <?php foreach ($brochures as $index => $brochure): ?>
              <?php $page = $index + 1; ?>
              <img id="brochure-<?php echo $page; ?>" src="<?php echo htmlspecialchars($brochure['image_path']); ?>"
                alt="<?php echo htmlspecialchars($brochure['title'] ?: ('BarCIE Brochure Page ' . $page)); ?>"
                class="img-fluid rounded-3 shadow-lg brochure-image<?php echo $page === 1 ? ' active' : ''; ?>"
                style="min-width: 100%; height: auto;">
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Navigation Arrows -->
        <button class="brochure-arrow brochure-arrow-left" onclick="previousBrochure()" aria-label="Previous brochure">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="brochure-arrow brochure-arrow-right" onclick="nextBrochure()" aria-label="Next brochure">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      <!-- Page Indicator -->
      <div class="brochure-indicator text-center mt-4">
        <span class="badge bg-primary-custom" id="page-indicator">Page 1 of <?php echo $totalBrochures; ?></span>
      </div>

      <!-- Download Button -->
      <div class="text-center mt-4">
        <a href="<?php echo htmlspecialchars($brochures[0]['image_path']); ?>"
          download="<?php echo htmlspecialchars($firstDownload); ?>" class="btn btn-primary-custom me-2"
          id="download-btn">
          <i class="fas fa-download me-2"></i>Download Current Page
        </a>
      </div>
    </div>
  </div>
</section>

<style>
  .brochure-image-wrapper {
    position: relative;
    width: 100%;
  }

  .brochure-carousel {
    display: flex;
    transition: transform 0.6s ease-in-out;
    will-change: transform;
  }

  .brochure-image {
    flex-shrink: 0;
    width: 100%;
  }
</style>

<script>
  // Brochure navigation functions
  let currentBrochure = 1;
  const totalBrochures = <?php echo (int) $totalBrochures; ?>;
  const brochureData = <?php echo json_encode(array_map(static function ($b) {
    return [
      'image' => $b['image_path'],
      'download' => ($b['download_name'] ?: basename($b['image_path']))
    ];
  }, $brochures), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  let autoPlayInterval;
  let isAutoPlaying = true;

  function switchBrochure(pageNum) {
    currentBrochure = pageNum;

    // Slide the carousel
    const carousel = document.querySelector('.brochure-carousel');
    const offset = (pageNum - 1) * -100;
    carousel.style.transform = `translateX(${offset}%)`;

    // Update button states
    document.querySelectorAll('.brochure-nav button').forEach(btn => {
      btn.classList.remove('active', 'btn-primary-custom');
      btn.classList.add('btn-outline-custom');
    });

    const activeBtn = document.querySelector(`[data-brochure="${pageNum}"]`);
    activeBtn.classList.remove('btn-outline-custom');
    activeBtn.classList.add('btn-primary-custom', 'active');

    // Update page indicator
    document.getElementById('page-indicator').textContent = `Page ${pageNum} of ${totalBrochures}`;

    // Update download button
    const downloadBtn = document.getElementById('download-btn');
    if (brochureData[pageNum - 1]) {
      downloadBtn.href = brochureData[pageNum - 1].image;
      downloadBtn.download = brochureData[pageNum - 1].download;
    }
  }

  function nextBrochure() {
    currentBrochure = currentBrochure === totalBrochures ? 1 : currentBrochure + 1;
    switchBrochure(currentBrochure);
  }

  function previousBrochure() {
    currentBrochure = currentBrochure === 1 ? totalBrochures : currentBrochure - 1;
    switchBrochure(currentBrochure);
  }

  function startAutoPlay() {
    isAutoPlaying = true;
    autoPlayInterval = setInterval(() => {
      nextBrochure();
    }, 5000); // Change every 5 seconds
  }

  function stopAutoPlay() {
    isAutoPlaying = false;
    if (autoPlayInterval) {
      clearInterval(autoPlayInterval);
    }
  }

  function resetAutoPlay() {
    stopAutoPlay();
    startAutoPlay();
  }

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      previousBrochure();
      resetAutoPlay();
    } else if (e.key === 'ArrowRight') {
      nextBrochure();
      resetAutoPlay();
    }
  });

  // Stop autoplay when user interacts with buttons
  document.querySelectorAll('.brochure-nav button').forEach(btn => {
    btn.addEventListener('click', () => {
      resetAutoPlay();
    });
  });

  document.querySelectorAll('.brochure-arrow').forEach(arrow => {
    arrow.addEventListener('click', () => {
      resetAutoPlay();
    });
  });

  // Pause autoplay when user hovers over brochure
  document.querySelector('.brochure-viewer').addEventListener('mouseenter', () => {
    stopAutoPlay();
  });

  document.querySelector('.brochure-viewer').addEventListener('mouseleave', () => {
    startAutoPlay();
  });

  // Start autoplay on page load
  document.addEventListener('DOMContentLoaded', () => {
    startAutoPlay();
  });
</script>