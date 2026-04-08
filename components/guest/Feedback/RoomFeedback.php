<!-- Room Feedback Modal (extracted) -->
<div class="modal fade" id="roomFeedbackModal" tabindex="-1" aria-labelledby="roomFeedbackLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%); color: white;">
                <h5 class="modal-title" id="roomFeedbackLabel">
                    <i class="fas fa-star me-2"></i>Leave a Review
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="roomFeedbackForm">
                    <input type="hidden" name="action" value="room_feedback">
                    <input type="hidden" name="room_id" id="feedbackRoomId" value="">
                    <input type="hidden" name="rating" id="feedbackRatingValue" value="">
                    <input type="hidden" name="google_id" id="feedbackGoogleId" value="">
                    <input type="hidden" name="google_email" id="feedbackGoogleEmail" value="">
                    <input type="hidden" name="guest_name" id="feedbackGuestName" value="">

                    <!-- Google Sign-In Required -->
                    <div id="googleSignInOption" class="alert alert-primary mb-3">
                        <div class="d-flex align-items-start mb-3">
                            <i class="fab fa-google fa-2x text-danger me-3"></i>
                            <div class="flex-fill">
                                <h6 class="mb-1"><i class="fas fa-shield-alt me-1"></i>Google Sign-In Required</h6>
                                <small class="text-muted">Please sign in with your Google account to verify your review
                                    and build trust
                                    with future guests.</small>
                            </div>
                        </div>
                        <div id="g_id_onload"
                            data-client_id="173306587840-ine8ao88f5a8r5mnjnuc7fa8sdmo110c.apps.googleusercontent.com"
                            data-callback="handleGoogleSignIn" data-auto_prompt="false">
                        </div>
                        <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline"
                            data-text="signin_with" data-shape="rectangular" data-logo_alignment="left">
                        </div>
                    </div>

                    <!-- Signed In User Info -->
                    <div class="mb-3 p-3 bg-light rounded" id="signedInUserInfo" style="display:none;">
                        <div class="d-flex align-items-center">
                            <img id="userGooglePhoto" src="" alt="Profile" class="roundzed-circle me-3" width="50"
                                height="50" style="display:none;">
                            <div>
                                <p class="mb-0 fw-bold">Signed in as: <span id="userGoogleName"></span></p>
                                <small class="text-muted"><span id="userGoogleEmail"></span></small>
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="is_anonymous" id="feedbackAnonymous"
                                value="1">
                            <label class="form-check-label" for="feedbackAnonymous">
                                <i class="fas fa-user-secret me-1"></i>Post as Anonymous (Only first 2 letters of name
                                will be shown)
                            </label>
                        </div>
                    </div>

                    <!-- Star Rating -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Rating *</label>
                        <div class="d-flex align-items-center">
                            <div class="star-rating-input me-3" id="feedbackStarRating">
                                <span class="star-input" data-rating="1"><i class="far fa-star fa-2x"></i></span>
                                <span class="star-input" data-rating="2"><i class="far fa-star fa-2x"></i></span>
                                <span class="star-input" data-rating="3"><i class="far fa-star fa-2x"></i></span>
                                <span class="star-input" data-rating="4"><i class="far fa-star fa-2x"></i></span>
                                <span class="star-input" data-rating="5"><i class="far fa-star fa-2x"></i></span>
                            </div>
                            <small class="text-muted" id="feedbackRatingText">Click to rate</small>
                        </div>
                    </div>

                    <!-- Comment -->
                    <div class="mb-4">
                        <label for="feedbackComment" class="form-label fw-bold">Your Review</label>
                        <textarea class="form-control" name="comment" id="feedbackComment" rows="4"
                            placeholder="Share your experience with this room..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>Help others by sharing your honest feedback
                        </small>
                        <button type="submit" class="btn btn-warning" id="submitRoomFeedback" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Star Rating Styles */
    .star-rating-input {
        display: inline-flex;
        gap: 5px;
    }

    .star-input {
        cursor: pointer;
        transition: all 0.2s ease;
        color: #ddd;
    }

    .star-input:hover,
    .star-input.active {
        color: #ffc107;
    }

    .star-input:hover i,
    .star-input.active i {
        transform: scale(1.2);
    }

    .star-input i {
        transition: all 0.2s ease;
    }

    /* Room Rating Display */
    .room-rating .stars-display i {
        font-size: 0.9rem;
    }

    .room-rating-modal .stars-display-modal i {
        font-size: 1rem;
    }

    /* Review Card Styles */
    .review-card {
        border-left: 3px solid #2a5298;
        transition: all 0.3s ease;
    }

    .review-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Feedback modal loading overlay */
    /* removed feedback loading overlay styling */

    .review-stars {
        color: #ffc107;
    }
</style>