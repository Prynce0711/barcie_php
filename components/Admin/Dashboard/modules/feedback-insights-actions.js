function generateStarDisplay(rating) {
  const fullStars = Math.floor(rating);
  const emptyStars = 5 - fullStars;

  return "â˜…".repeat(fullStars) + "â˜†".repeat(emptyStars);
}

function updateRatingChart(stats) {
  const ctx = document.getElementById("ratingChart");
  if (!ctx) {
    return;
  }

  // Destroy existing chart if it exists
  if (window.ratingChartInstance) {
    window.ratingChartInstance.destroy();
  }

  window.ratingChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["1 Star", "2 Stars", "3 Stars", "4 Stars", "5 Stars"],
      datasets: [
        {
          label: "Number of Reviews",
          data: [
            stats.one_star || 0,
            stats.two_star || 0,
            stats.three_star || 0,
            stats.four_star || 0,
            stats.five_star || 0,
          ],
          backgroundColor: [
            "#dc3545", // Red for 1 star
            "#fd7e14", // Orange for 2 stars
            "#ffc107", // Yellow for 3 stars
            "#20c997", // Teal for 4 stars
            "#28a745", // Green for 5 stars
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
      },
    },
  });
}

function updateFeedbackInsights(stats) {
  const insights = document.getElementById("feedback-insights");
  const totalFeedback = parseInt(stats.total_feedback || 0);
  const avgRating = parseFloat(stats.avg_rating || 0);
  const fiveStarPercent =
    totalFeedback > 0
      ? (((stats.five_star || 0) / totalFeedback) * 100).toFixed(1)
      : 0;
  const lowRatingCount =
    parseInt(stats.one_star || 0) + parseInt(stats.two_star || 0);

  if (totalFeedback === 0) {
    insights.innerHTML = `
      <div class="text-center text-muted">
        <i class="fas fa-star-o fa-2x mb-3 opacity-50"></i>
        <h6>No Feedback Yet</h6>
        <p class="mb-0">Encourage guests to share their experiences!</p>
      </div>
    `;
    return;
  }

  let insightClass = "success";
  let insightIcon = "fa-smile";
  let insightTitle = "Excellent Performance!";
  let insightMessage = "Guests are highly satisfied with their experience.";

  if (avgRating < 3) {
    insightClass = "danger";
    insightIcon = "fa-frown";
    insightTitle = "Needs Improvement";
    insightMessage = "Consider addressing common concerns in feedback.";
  } else if (avgRating < 4) {
    insightClass = "warning";
    insightIcon = "fa-meh";
    insightTitle = "Good but Room for Growth";
    insightMessage = "Focus on enhancing guest satisfaction areas.";
  }

  insights.innerHTML = `
    <div class="text-center">
      <div class="text-${insightClass} mb-3">
        <i class="fas ${insightIcon} fa-3x"></i>
      </div>
      <h6 class="text-${insightClass}">${insightTitle}</h6>
      <p class="mb-3">${insightMessage}</p>
      <div class="row text-center">
        <div class="col-6">
          <h5 class="text-${insightClass} mb-1">${fiveStarPercent}%</h5>
          <small class="text-muted">5-Star Reviews</small>
        </div>
        <div class="col-6">
          <h5 class="text-${
            lowRatingCount > 0 ? "warning" : "success"
          } mb-1">${lowRatingCount}</h5>
          <small class="text-muted">Low Ratings</small>
        </div>
      </div>
    </div>
  `;
}

function showFeedbackError(message) {
  const tbody = document.getElementById("feedback-tbody");
  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="text-center text-danger py-4">
        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
        <br>${message}
      </td>
    </tr>
  `;
}

function refreshFeedback() {
  loadFeedbackData();
}

function exportFeedback() {
  // Implementation for exporting feedback data
  showToast("Export functionality would be implemented here", "info");
}

function viewFeedbackDetails(feedbackId) {
  // Implementation for viewing detailed feedback
  showToast("View details for feedback ID: " + feedbackId, "info");
}

function respondToFeedback(feedbackId) {
  // Implementation for responding to feedback
  showToast("Respond to feedback ID: " + feedbackId, "info");
}

// Export feedback functions globally
window.refreshFeedback = refreshFeedback;
window.exportFeedback = exportFeedback;
window.viewFeedbackDetails = viewFeedbackDetails;
window.respondToFeedback = respondToFeedback;

// Calendar & Items Navigation Functions
