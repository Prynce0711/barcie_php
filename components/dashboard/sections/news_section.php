<?php
// Fetch news for display
$news_query = "SELECT * FROM news_updates ORDER BY published_date DESC, created_at DESC";
$news_result = $conn->query($news_query);
$news_items = [];
if ($news_result && $news_result->num_rows > 0) {
    while ($row = $news_result->fetch_assoc()) {
        $news_items[] = $row;
    }
}
?>

<div class="section-header d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-newspaper me-2"></i>News & Updates</h2>
    <button class="btn btn-primary" onclick="openAddNewsModal()">
        <i class="fas fa-plus me-1"></i> Add News
    </button>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Filter and Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="newsSearchInput" placeholder="Search news by title or content...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="newsStatusFilter">
                            <option value="all">All Status</option>
                            <option value="published" selected>Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="newsSortOrder">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="title">Title A-Z</option>
                        </select>
                    </div>
                </div>

                <!-- News Cards Grid -->
                <div id="newsGrid" class="row g-3">
                    <?php if (empty($news_items)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No news items yet. Click "Add News" to create your first post.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($news_items as $news): ?>
                            <div class="col-md-6 col-lg-4 news-card-item" 
                                 data-status="<?php echo htmlspecialchars($news['status']); ?>"
                                 data-title="<?php echo htmlspecialchars($news['title']); ?>"
                                 data-content="<?php echo htmlspecialchars($news['content']); ?>">
                                <div class="card h-100 news-card">
                                    <?php if (!empty($news['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($news['image_path']); ?>" 
                                             class="card-img-top news-card-image" 
                                             alt="<?php echo htmlspecialchars($news['title']); ?>"
                                             onerror="this.src='assets/images/imageBg/barcie_logo.jpg'">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-<?php 
                                                echo $news['status'] === 'published' ? 'success' : 
                                                    ($news['status'] === 'draft' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($news['status']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($news['published_date'] ?? $news['created_at'])); ?>
                                            </small>
                                        </div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h5>
                                        <p class="card-text text-muted news-excerpt">
                                            <?php echo htmlspecialchars(substr($news['content'], 0, 120)) . (strlen($news['content']) > 120 ? '...' : ''); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($news['author']); ?>
                                            </small>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewNews(<?php echo $news['id']; ?>)" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" onclick="editNews(<?php echo $news['id']; ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="deleteNews(<?php echo $news['id']; ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit News Modal -->
<div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newsModalLabel">Add News</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newsForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="newsId" name="news_id">
                    
                    <div class="mb-3">
                        <label for="newsTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newsTitle" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newsContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="newsContent" name="content" rows="6" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newsImage" class="form-label">Featured Image</label>
                        <input type="file" class="form-control" id="newsImage" name="image" accept="image/*">
                        <small class="text-muted">Recommended size: 800x600px</small>
                        <div id="currentImagePreview" class="mt-2" style="display: none;">
                            <img src="" alt="Current image" style="max-width: 200px; max-height: 150px;">
                            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeCurrentImage()">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="newsAuthor" class="form-label">Author</label>
                            <input type="text" class="form-control" id="newsAuthor" name="author" value="Admin">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="newsStatus" class="form-label">Status</label>
                            <select class="form-select" id="newsStatus" name="status">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newsPublishedDate" class="form-label">Published Date</label>
                        <input type="date" class="form-control" id="newsPublishedDate" name="published_date" 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save News
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View News Modal -->
<div class="modal fade" id="viewNewsModal" tabindex="-1" aria-labelledby="viewNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNewsModalLabel">News Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewNewsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
