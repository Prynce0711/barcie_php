<!-- News & Updates Section -->
<section id="news" class="section-padding bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="display-4 fw-bold">News & Updates</h2>
            <div class="title-divider mx-auto"></div>
            <p class="lead text-muted mt-3">Stay informed with our latest announcements and updates</p>
        </div>

        <div id="newsContainer" class="row g-4">
            <!-- News items will be loaded here dynamically -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading news...</span>
                </div>
            </div>
        </div>

        <!-- View More Button -->
        <div class="text-center mt-5" id="viewMoreNewsBtn" style="display: none;">
            <button class="btn btn-outline-primary btn-lg" onclick="loadMoreNews()">
                <i class="fas fa-plus-circle me-2"></i>Load More News
            </button>
        </div>
    </div>
</section>

<!-- News Detail Modal -->
<div class="modal fade" id="newsDetailModal" tabindex="-1" aria-labelledby="newsDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="newsDetailContent">
                <!-- News detail will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Landing Page News Display
 */
(function() {
    'use strict';

    let currentPage = 1;
    let newsPerPage = 6;
    let allNews = [];
    let hasMoreNews = false;

    /**
     * Initialize news section on landing page
     */
    function initializeLandingNews() {
        loadPublishedNews();
    }

    /**
     * Load published news from API
     */
    function loadPublishedNews() {
        fetch(`api/news.php?action=fetch_published&limit=100`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allNews = data.data;
                    displayNews();
                } else {
                    showNoNewsMessage();
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
                showErrorMessage();
            });
    }

    /**
     * Display news items
     */
    function displayNews() {
        const container = document.getElementById('newsContainer');
        if (!container) return;

        const startIndex = 0;
        const endIndex = currentPage * newsPerPage;
        const newsToShow = allNews.slice(startIndex, endIndex);

        if (newsToShow.length === 0) {
            showNoNewsMessage();
            return;
        }

        container.innerHTML = newsToShow.map(news => createNewsCard(news)).join('');

        // Show/hide "Load More" button
        const viewMoreBtn = document.getElementById('viewMoreNewsBtn');
        if (viewMoreBtn) {
            hasMoreNews = endIndex < allNews.length;
            viewMoreBtn.style.display = hasMoreNews ? 'block' : 'none';
        }
    }

    /**
     * Create news card HTML for landing page
     */
    function createNewsCard(news) {
        const excerpt = news.content.length > 150 ? 
            news.content.substring(0, 150) + '...' : news.content;

        const dateStr = news.published_date ? 
            new Date(news.published_date).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            }) : '';

        const imageUrl = news.image_path || 'assets/images/imageBg/barcie_logo.jpg';

        return `
            <div class="col-md-6 col-lg-4">
                <div class="card news-card-landing h-100 shadow-sm hover-lift">
                    <div class="news-image-wrapper">
                        <img src="${escapeHtml(imageUrl)}" 
                             class="card-img-top" 
                             alt="${escapeHtml(news.title)}"
                             onerror="this.src='assets/images/imageBg/barcie_logo.jpg'">
                        <div class="news-date-badge">
                            <span class="date-day">${new Date(news.published_date || news.created_at).getDate()}</span>
                            <span class="date-month">${new Date(news.published_date || news.created_at).toLocaleDateString('en-US', { month: 'short' })}</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="news-meta mb-2">
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>${dateStr}
                                <span class="mx-2">•</span>
                                <i class="far fa-user me-1"></i>${escapeHtml(news.author)}
                            </small>
                        </div>
                        <h5 class="card-title news-title mb-3">${escapeHtml(news.title)}</h5>
                        <p class="card-text text-muted news-excerpt flex-grow-1">${escapeHtml(excerpt)}</p>
                        <button class="btn btn-outline-primary btn-sm mt-3" onclick="showNewsDetail(${news.id})">
                            Read More <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show news detail in modal
     */
    window.showNewsDetail = function(newsId) {
        const news = allNews.find(n => n.id === newsId);
        if (!news) return;

        const dateStr = news.published_date ? 
            new Date(news.published_date).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            }) : '';

        const imageHtml = news.image_path ? 
            `<img src="${escapeHtml(news.image_path)}" class="img-fluid rounded mb-4" alt="${escapeHtml(news.title)}">` : '';

        document.getElementById('newsDetailContent').innerHTML = `
            ${imageHtml}
            <h2 class="mb-3">${escapeHtml(news.title)}</h2>
            <div class="news-meta mb-4">
                <span class="badge bg-primary me-2">
                    <i class="far fa-calendar-alt me-1"></i>${dateStr}
                </span>
                <span class="badge bg-secondary">
                    <i class="far fa-user me-1"></i>${escapeHtml(news.author)}
                </span>
            </div>
            <div class="news-detail-content">
                ${escapeHtml(news.content).replace(/\n/g, '<br>')}
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('newsDetailModal'));
        modal.show();
    };

    /**
     * Load more news
     */
    window.loadMoreNews = function() {
        currentPage++;
        displayNews();
    };

    /**
     * Show no news message
     */
    function showNoNewsMessage() {
        const container = document.getElementById('newsContainer');
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No news available at the moment</h4>
                    <p class="text-muted">Check back later for updates!</p>
                </div>
            `;
        }
        const viewMoreBtn = document.getElementById('viewMoreNewsBtn');
        if (viewMoreBtn) viewMoreBtn.style.display = 'none';
    }

    /**
     * Show error message
     */
    function showErrorMessage() {
        const container = document.getElementById('newsContainer');
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h4 class="text-muted">Unable to load news</h4>
                    <p class="text-muted">Please try again later</p>
                </div>
            `;
        }
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLandingNews);
    } else {
        initializeLandingNews();
    }

})();
</script>
