<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../database/config.php';

$brochures = [];
$result = $conn->query("SELECT id, title, image_path, download_name, sort_order, is_active FROM landing_brochures ORDER BY sort_order ASC, id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $brochures[] = $row;
    }
    $result->free();
}

// Prepare section header variables
$sectionTitle = 'Landing Brochure Management';
$sectionIcon = 'fa-file-pdf';
$sectionSubtitle = 'Manage brochure files for the landing page';
?>

<link rel="stylesheet" href="Components/Admin/Brochure/brochure_partners_management.css">

<div class="brochure-partners-section">
    <?php include __DIR__ . '/../Shared/SectionHeader.php'; ?>

    <div style="padding: 2rem;">
        <!-- Add Brochure Form -->
        <form method="POST" action="Components/Admin/data_processing.php" enctype="multipart/form-data"
            class="brochure-partners-form-wrapper" data-popup-action="true">
            <input type="hidden" name="action" value="add_brochure">
            <div class="brochure-partners-form-grid">
                <div class="brochure-form-group">
                    <label class="brochure-partners-label required">Title</label>
                    <input type="text" name="title" class="brochure-partners-input" placeholder="e.g., Main Brochure"
                        required>
                </div>
                <div class="brochure-form-group">
                    <label class="brochure-partners-label required">Image</label>
                    <input type="file" name="image_file" class="brochure-partners-input" accept="image/*" required>
                </div>
                <div class="brochure-form-group">
                    <label class="brochure-partners-label">Download Name</label>
                    <input type="text" name="download_name" class="brochure-partners-input"
                        placeholder="BarCIE-Brochure-Page-1.png">
                </div>
                <div class="brochure-form-group">
                    <label class="brochure-partners-label">Sort Order</label>
                    <input type="number" name="sort_order" class="brochure-partners-input input-number-sm" value="0"
                        min="0">
                </div>
                <div class="brochure-form-group full-width">
                    <div class="brochure-partners-btn-group">
                        <button type="submit" class="btn-brochure-add">
                            <i class="fas fa-plus"></i> Add Brochure
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Brochures Table -->
        <?php if (!empty($brochures)): ?>
            <div class="brochure-partners-table-wrapper">
                <table class="brochure-partners-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Download Name</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brochures as $brochure): ?>
                            <tr>
                                <form method="POST" action="Components/Admin/data_processing.php" enctype="multipart/form-data"
                                    style="display: contents;" data-popup-action="true">
                                    <input type="hidden" name="action" value="update_brochure">
                                    <input type="hidden" name="id" value="<?php echo (int) $brochure['id']; ?>">
                                    <input type="hidden" name="existing_image_path"
                                        value="<?php echo htmlspecialchars((string) ($brochure['image_path'] ?? '')); ?>">
                                    <td>
                                        <input type="text" name="title" class="brochure-partners-input"
                                            value="<?php echo htmlspecialchars($brochure['title']); ?>" required>
                                    </td>
                                    <td>
                                        <?php if (!empty($brochure['image_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($brochure['image_path']); ?>" target="_blank"
                                                rel="noopener"
                                                style="display: inline-block; margin-bottom: 0.35rem; font-size: 0.8rem;">Current
                                                image</a>
                                        <?php endif; ?>
                                        <input type="file" name="image_file" class="brochure-partners-input" accept="image/*">
                                    </td>
                                    <td>
                                        <input type="text" name="download_name" class="brochure-partners-input"
                                            value="<?php echo htmlspecialchars($brochure['download_name'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="sort_order" class="brochure-partners-input input-number-sm"
                                            value="<?php echo (int) $brochure['sort_order']; ?>" min="0">
                                    </td>
                                    <td style="text-align: center;">
                                        <label
                                            style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                            <input type="checkbox" name="is_active" value="1" class="brochure-partners-checkbox"
                                                <?php echo (int) $brochure['is_active'] === 1 ? 'checked' : ''; ?>>
                                            <span
                                                class="badge-status <?php echo (int) $brochure['is_active'] === 1 ? 'active' : 'inactive'; ?>">
                                                <?php echo (int) $brochure['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </label>
                                    </td>
                                    <td style="white-space: nowrap; display: flex; gap: 0.5rem;">
                                        <button type="submit" class="btn-table-save">
                                            <i class="fas fa-save"></i> Save
                                        </button>
                                </form>
                                <form method="POST" action="Components/Admin/data_processing.php" data-popup-action="true"
                                    data-confirm-message="Delete this brochure?" style="display: contents;">
                                    <input type="hidden" name="action" value="delete_brochure">
                                    <input type="hidden" name="id" value="<?php echo (int) $brochure['id']; ?>">
                                    <button type="submit" class="btn-table-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="brochure-partners-empty">
                <div class="brochure-partners-empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <p class="brochure-partners-empty-text">No brochures found. Create one to get started!</p>
            </div>
        <?php endif; ?>
    </div>
</div>