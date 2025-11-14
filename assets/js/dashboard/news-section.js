/**
 * News & Updates Management JavaScript
 * Handles CRUD operations for news section in dashboard
 */

(function() {
    'use strict';

    let currentNewsData = [];
    let editingNewsId = null;

    /**
     * Initialize news section
     */
    function initializeNewsSection() {
        console.log('📰 Initializing News Section...');

        // Setup event listeners
        setupNewsEventListeners();

        // Initialize filters
        initializeNewsFilters();

        // Load news data
        loadNews();
    }

    /**
     * Setup event listeners
     */
    function setupNewsEventListeners() {
        // Form submission
        const newsForm = document.getElementById('newsForm');
        if (newsForm) {
            newsForm.addEventListener('submit', handleNewsFormSubmit);
        }

        // Image preview
        const newsImage = document.getElementById('newsImage');
        if (newsImage) {
            newsImage.addEventListener('change', previewImage);
        }
    }

    /**
     * Initialize filters
     */
    function initializeNewsFilters() {
        const searchInput = document.getElementById('newsSearchInput');
        const statusFilter = document.getElementById('newsStatusFilter');
        const sortOrder = document.getElementById('newsSortOrder');

        if (searchInput) {
            searchInput.addEventListener('input', filterNews);
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', filterNews);
        }

        if (sortOrder) {
            sortOrder.addEventListener('change', filterNews);
        }
    }

    /**
     * Load news from API
     */
    function loadNews(status = 'all') {
        fetch(`api/news.php?action=fetch&status=${status}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentNewsData = data.data;
                    renderNewsGrid(currentNewsData);
                } else {
                    console.error('Failed to load news:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
            });
    }

    /**
     * Render news grid
     */
    function renderNewsGrid(newsItems) {
        const newsGrid = document.getElementById('newsGrid');
        if (!newsGrid) return;

        if (newsItems.length === 0) {
            newsGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No news items found. Try adjusting your filters.</p>
                </div>
            `;
            return;
        }

        newsGrid.innerHTML = newsItems.map(news => createNewsCard(news)).join('');
    }

    /**
     * Create news card HTML
     */
    function createNewsCard(news) {
        const statusBadge = {
            'published': 'success',
            'draft': 'warning',
            'archived': 'secondary'
        }[news.status] || 'secondary';

        const imageHtml = news.image_path ? 
            `<img src="${escapeHtml(news.image_path)}" class="card-img-top news-card-image" 
                  alt="${escapeHtml(news.title)}" onerror="this.src='assets/images/imageBg/barcie_logo.jpg'">` : '';

        const excerpt = news.content.length > 120 ? 
            news.content.substring(0, 120) + '...' : news.content;

        const dateStr = news.published_date ? 
            new Date(news.published_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) :
            new Date(news.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

        return `
            <div class="col-md-6 col-lg-4 news-card-item" 
                 data-status="${escapeHtml(news.status)}"
                 data-title="${escapeHtml(news.title)}"
                 data-content="${escapeHtml(news.content)}">
                <div class="card h-100 news-card">
                    ${imageHtml}
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-${statusBadge}">
                                ${escapeHtml(news.status.charAt(0).toUpperCase() + news.status.slice(1))}
                            </span>
                            <small class="text-muted">${dateStr}</small>
                        </div>
                        <h5 class="card-title">${escapeHtml(news.title)}</h5>
                        <p class="card-text text-muted news-excerpt">${escapeHtml(excerpt)}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>${escapeHtml(news.author)}
                            </small>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewNews(${news.id})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" onclick="editNews(${news.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteNews(${news.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Filter news based on search and filters
     */
    function filterNews() {
        const searchTerm = document.getElementById('newsSearchInput')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('newsStatusFilter')?.value || 'all';
        const sortOrder = document.getElementById('newsSortOrder')?.value || 'newest';

        let filtered = currentNewsData.filter(news => {
            const matchesSearch = news.title.toLowerCase().includes(searchTerm) || 
                                news.content.toLowerCase().includes(searchTerm);
            const matchesStatus = statusFilter === 'all' || news.status === statusFilter;
            return matchesSearch && matchesStatus;
        });

        // Sort
        filtered.sort((a, b) => {
            switch (sortOrder) {
                case 'newest':
                    return new Date(b.published_date || b.created_at) - new Date(a.published_date || a.created_at);
                case 'oldest':
                    return new Date(a.published_date || a.created_at) - new Date(b.published_date || b.created_at);
                case 'title':
                    return a.title.localeCompare(b.title);
                default:
                    return 0;
            }
        });

        renderNewsGrid(filtered);
    }

    /**
     * Open add news modal
     */
    window.openAddNewsModal = function() {
        editingNewsId = null;
        document.getElementById('newsModalLabel').textContent = 'Add News';
        document.getElementById('newsForm').reset();
        document.getElementById('newsId').value = '';
        document.getElementById('newsPublishedDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('currentImagePreview').style.display = 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('newsModal'));
        modal.show();
    };

    /**
     * View news details
     */
    window.viewNews = function(newsId) {
        fetch(`api/news.php?action=fetch_single&id=${newsId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayNewsDetails(data.data);
                } else {
                    alert('Failed to load news details');
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
                alert('An error occurred while loading news details');
            });
    };

    /**
     * Display news details in modal
     */
    function displayNewsDetails(news) {
        const imageHtml = news.image_path ? 
            `<img src="${escapeHtml(news.image_path)}" class="img-fluid mb-3" alt="${escapeHtml(news.title)}">` : '';

        const statusBadge = {
            'published': 'success',
            'draft': 'warning',
            'archived': 'secondary'
        }[news.status] || 'secondary';

        const dateStr = news.published_date ? 
            new Date(news.published_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) :
            new Date(news.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

        document.getElementById('viewNewsContent').innerHTML = `
            ${imageHtml}
            <div class="mb-3">
                <span class="badge bg-${statusBadge} mb-2">${escapeHtml(news.status.toUpperCase())}</span>
                <h3>${escapeHtml(news.title)}</h3>
                <p class="text-muted">
                    <i class="fas fa-user me-2"></i>${escapeHtml(news.author)}
                    <i class="fas fa-calendar ms-3 me-2"></i>${dateStr}
                </p>
            </div>
            <div class="news-content">
                ${escapeHtml(news.content).replace(/\n/g, '<br>')}
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('viewNewsModal'));
        modal.show();
    }

    /**
     * Edit news
     */
    window.editNews = function(newsId) {
        fetch(`api/news.php?action=fetch_single&id=${newsId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateEditForm(data.data);
                } else {
                    alert('Failed to load news for editing');
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
                alert('An error occurred while loading news');
            });
    };

    /**
     * Populate edit form
     */
    function populateEditForm(news) {
        editingNewsId = news.id;
        document.getElementById('newsModalLabel').textContent = 'Edit News';
        document.getElementById('newsId').value = news.id;
        document.getElementById('newsTitle').value = news.title;
        document.getElementById('newsContent').value = news.content;
        document.getElementById('newsAuthor').value = news.author;
        document.getElementById('newsStatus').value = news.status;
        document.getElementById('newsPublishedDate').value = news.published_date || '';

        // Show current image if exists
        if (news.image_path) {
            const preview = document.getElementById('currentImagePreview');
            preview.querySelector('img').src = news.image_path;
            preview.style.display = 'block';
        } else {
            document.getElementById('currentImagePreview').style.display = 'none';
        }

        const modal = new bootstrap.Modal(document.getElementById('newsModal'));
        modal.show();
    }

    /**
     * Handle form submission
     */
    function handleNewsFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const newsId = document.getElementById('newsId').value;
        
        formData.append('action', newsId ? 'update' : 'create');

        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch('api/news.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            if (data.success) {
                alert(data.message);
                bootstrap.Modal.getInstance(document.getElementById('newsModal')).hide();
                loadNews(); // Reload news list
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            console.error('Error saving news:', error);
            alert('An error occurred while saving news');
        });
    }

    /**
     * Delete news
     */
    window.deleteNews = function(newsId) {
        if (!confirm('Are you sure you want to delete this news item? This action cannot be undone.')) {
            return;
        }

        fetch('api/news.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&id=${newsId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadNews(); // Reload news list
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting news:', error);
            alert('An error occurred while deleting news');
        });
    };

    /**
     * Preview image before upload
     */
    function previewImage(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('currentImagePreview');
                preview.querySelector('img').src = event.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    /**
     * Remove current image
     */
    window.removeCurrentImage = function() {
        document.getElementById('newsImage').value = '';
        document.getElementById('currentImagePreview').style.display = 'none';
        
        // Add hidden field to indicate image removal
        if (editingNewsId) {
            let removeInput = document.querySelector('input[name="remove_image"]');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_image';
                document.getElementById('newsForm').appendChild(removeInput);
            }
            removeInput.value = '1';
        }
    };

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeNewsSection);
    } else {
        initializeNewsSection();
    }

})();
