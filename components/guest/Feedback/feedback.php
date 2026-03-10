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

<section id="feedback"
  class="content-section bg-white/95 border-2 border-[rgba(52,152,219,0.2)] p-[30px] mb-[30px] rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] relative z-[1]">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <i class="fas fa-star me-2"></i>Share Your Experience
          </h5>
          <small class="text-white-50">Help us improve by rating your experience</small>
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
        </div>
      </div>
    </div>
  </div>
</section>