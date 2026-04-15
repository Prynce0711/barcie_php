<style>
  /* Star rating scoped styles */
  .star-rating {
    display: flex;
    gap: 8px;
    font-size: 2rem;
    cursor: pointer;
    user-select: none;
  }

  .star-rating .star {
    color: #ddd;
    transition: all 0.2s ease;
    transform-origin: center;
  }

  .star-rating .star:hover {
    transform: scale(1.2);
  }

  .star-rating .star.active {
    color: #ffc107;
    text-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
  }

  .star-rating .star.hover {
    color: #ffb300;
  }

  .rating-text {
    font-weight: 500;
    margin-left: 15px;
  }

  .star-display {
    font-size: 1.1rem;
  }

  .star-display .fas.fa-star {
    color: #ffc107;
    margin-right: 2px;
  }

  .star-display .far.fa-star {
    color: #ddd;
    margin-right: 2px;
  }

  #feedback-form {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 0;
  }

  .feedback-item {
    padding: 15px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .feedback-item:last-child {
    border-bottom: none;
  }

  #submit-feedback:disabled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    cursor: not-allowed;
    opacity: 0.6;
  }

  @media (max-width: 768px) {
    .star-rating {
      font-size: 1.8rem;
      gap: 6px;
    }

    .star-display {
      font-size: 1rem;
    }
  }
</style>

<?php
$reviewSummary = [
  'total_reviews' => 0,
  'average_rating' => 0.0,
];

$recentReviews = [];

if (isset($conn) && $conn instanceof mysqli) {
  $summaryResult = $conn->query('SELECT COUNT(*) AS total_reviews, COALESCE(ROUND(AVG(rating), 1), 0) AS average_rating FROM feedback WHERE rating IS NOT NULL');
  if ($summaryResult) {
    $row = $summaryResult->fetch_assoc();
    $reviewSummary['total_reviews'] = (int) ($row['total_reviews'] ?? 0);
    $reviewSummary['average_rating'] = (float) ($row['average_rating'] ?? 0);
    $summaryResult->free();
  }

  $reviewsResult = $conn->query("SELECT f.rating, f.message, f.feedback_name, f.is_anonymous, f.created_at,
      COALESCE(i.name, 'General Feedback') AS room_name,
      LOWER(TRIM(COALESCE(i.item_type, ''))) AS item_type
    FROM feedback f
    LEFT JOIN items i ON i.id = f.room_id
    WHERE f.rating IS NOT NULL
    ORDER BY f.created_at DESC
    LIMIT 6");

  if ($reviewsResult) {
    while ($review = $reviewsResult->fetch_assoc()) {
      $recentReviews[] = $review;
    }
    $reviewsResult->free();
  }
}

$reviewAverageDisplay = $reviewSummary['total_reviews'] > 0
  ? number_format($reviewSummary['average_rating'], 1)
  : '0.0';
?>

<section id="feedback"
  class="content-section bg-white/95 border-2 border-[rgba(52,152,219,0.2)] p-[30px] mb-[30px] rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] relative z-[1]">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <i class="fas fa-star me-2"></i>Share Your Experience
          </h5>
          <small class="text-white-50">
            <?php echo htmlspecialchars($reviewAverageDisplay, ENT_QUOTES, 'UTF-8'); ?> / 5 average from
            <?php echo number_format((int) $reviewSummary['total_reviews']); ?> review<?php echo ((int) $reviewSummary['total_reviews']) === 1 ? '' : 's'; ?>
          </small>
        </div>
        <div class="card-body">
          <?php
          if (!empty($success))
            echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>$success</div>";
          if (!empty($error))
            echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle me-2'></i>$error</div>";
          ?>

          <form method="post" id="feedback-form">
            <input type="hidden" name="action" value="feedback">
            <input type="hidden" name="rating" id="rating-value" value="">

            <div class="mb-4">
              <label class="form-label fw-bold">Rate Your Experience</label>
              <div class="d-flex align-items-center">
                <div class="star-rating me-3" id="star-rating">
                  <span class="star" data-rating="1"><i class="fas fa-star"></i></span>
                  <span class="star" data-rating="2"><i class="fas fa-star"></i></span>
                  <span class="star" data-rating="3"><i class="fas fa-star"></i></span>
                  <span class="star" data-rating="4"><i class="fas fa-star"></i></span>
                  <span class="star" data-rating="5"><i class="fas fa-star"></i></span>
                </div>
                <small class="text-muted" id="rating-text">Click to rate</small>
              </div>
            </div>

            <div class="mb-4">
              <label for="feedback-message" class="form-label fw-bold">Tell us more (optional)</label>
              <textarea class="form-control" name="message" id="feedback-message" rows="4"
                placeholder="Share specific details about your experience..."></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Your feedback helps us serve you better
              </small>
              <button type="submit" class="btn btn-primary" id="submit-feedback" disabled>
                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
              </button>
            </div>
          </form>

          <hr class="my-4">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-comments me-2 text-primary"></i>Recent Reviews</h6>
            <small class="text-muted">Live from database</small>
          </div>

          <?php if (empty($recentReviews)): ?>
            <div class="alert alert-light border text-muted mb-0">
              <i class="fas fa-info-circle me-2"></i>No reviews yet. Be the first to share your experience.
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recentReviews as $review): ?>
                <?php
                $rawName = trim((string) ($review['feedback_name'] ?? ''));
                $isAnonymous = (int) ($review['is_anonymous'] ?? 0) === 1;
                $displayName = $isAnonymous || $rawName === '' ? 'Anonymous' : $rawName;
                $ratingValue = (int) ($review['rating'] ?? 0);
                if ($ratingValue < 0) {
                  $ratingValue = 0;
                }
                if ($ratingValue > 5) {
                  $ratingValue = 5;
                }
                $filledStars = str_repeat('★', $ratingValue);
                $emptyStars = str_repeat('☆', 5 - $ratingValue);
                $reviewDate = !empty($review['created_at'])
                  ? date('M d, Y', strtotime((string) $review['created_at']))
                  : '';
                $itemTypeRaw = (string) ($review['item_type'] ?? '');
                $itemTypeLabel = 'Room/Facility';
                if (
                  in_array($itemTypeRaw, ['facility', 'facilities', 'facilitys', 'fac', 'facil'], true)
                  || strpos($itemTypeRaw, 'facil') !== false
                ) {
                  $itemTypeLabel = 'Facility';
                } elseif (
                  in_array($itemTypeRaw, ['room', 'rooms', 'rm', 'r'], true)
                  || strpos($itemTypeRaw, 'room') !== false
                ) {
                  $itemTypeLabel = 'Room';
                }
                ?>
                <div class="feedback-item">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                      <strong><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>
                      <div class="text-warning" style="letter-spacing:1px;"><?php echo htmlspecialchars($filledStars . $emptyStars, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <small class="text-muted"><?php echo htmlspecialchars($reviewDate, ENT_QUOTES, 'UTF-8'); ?></small>
                  </div>
                  <div class="small text-muted mb-1">
                    <i class="fas fa-door-open me-1"></i>
                    <?php echo htmlspecialchars($itemTypeLabel, ENT_QUOTES, 'UTF-8'); ?>:
                    <?php echo htmlspecialchars((string) ($review['room_name'] ?? 'General Feedback'), ENT_QUOTES, 'UTF-8'); ?>
                  </div>
                  <?php if (!empty($review['message'])): ?>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) $review['message'], ENT_QUOTES, 'UTF-8')); ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>