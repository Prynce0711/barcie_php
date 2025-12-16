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
        <button class="btn btn-primary-custom active" data-brochure="1" onclick="switchBrochure(1)">
          <i class="fas fa-book-open me-2"></i>Brochure 1
        </button>
        <button class="btn btn-outline-custom" data-brochure="2" onclick="switchBrochure(2)">
          <i class="fas fa-book me-2"></i>Brochure 2
        </button>
      </div>

      <!-- Brochure Images -->
      <div class="brochure-viewer text-center position-relative">
        <div class="brochure-image-wrapper position-relative overflow-hidden">
          <div class="brochure-carousel d-flex" style="transition: transform 0.6s ease-in-out;">
            <img id="brochure-1" src="assets/images/brochure/brochure 1.png" alt="BarCIE Brochure Page 1" class="img-fluid rounded-3 shadow-lg brochure-image active" style="min-width: 100%; height: auto;">
            <img id="brochure-2" src="assets/images/brochure/brochure 2.png" alt="BarCIE Brochure Page 2" class="img-fluid rounded-3 shadow-lg brochure-image" style="min-width: 100%; height: auto;">
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
        <span class="badge bg-primary-custom" id="page-indicator">Page 1 of 2</span>
      </div>

      <!-- Download Button -->
      <div class="text-center mt-4">
        <a href="assets/images/brochure/brochure 1.png" download="BarCIE-Brochure-Page-1.png" class="btn btn-primary-custom me-2" id="download-btn">
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
  document.getElementById('page-indicator').textContent = `Page ${pageNum} of 2`;
  
  // Update download button
  const downloadBtn = document.getElementById('download-btn');
  downloadBtn.href = `assets/images/brochure/brochure ${pageNum}.png`;
  downloadBtn.download = `BarCIE-Brochure-Page-${pageNum}.png`;
}

function nextBrochure() {
  currentBrochure = currentBrochure === 2 ? 1 : currentBrochure + 1;
  switchBrochure(currentBrochure);
}

function previousBrochure() {
  currentBrochure = currentBrochure === 1 ? 2 : currentBrochure - 1;
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
