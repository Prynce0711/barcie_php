<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../database/config.php';

$partners = [];
$result = $conn->query("SELECT id, category, name, facebook_url, phones, image_path, sort_order, is_active FROM landing_partners ORDER BY category ASC, sort_order ASC, id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $partners[] = $row;
    }
    $result->free();
}

// Prepare section header variables
$sectionTitle = 'Landing Partners Management';
$sectionIcon = 'fa-handshake';
$sectionSubtitle = 'Manage catering and event styling partners';
?>

<link rel="stylesheet" href="Components/Admin/Brochure/brochure_partners_management.css">

<div class="brochure-partners-section">
    <?php include __DIR__ . '/../Shared/SectionHeader.php'; ?>

    <div style="padding: 2rem;">
        <!-- Add Partner Form -->
        <form method="POST" action="Components/Admin/data_processing.php" class="brochure-partners-form-wrapper">
            <input type="hidden" name="action" value="add_partner">
            <div class="brochure-partners-form-grid">
                <div class="partners-form-group">
                    <label class="brochure-partners-label required">Category</label>
                    <select name="category" class="brochure-partners-select" required>
                        <option value="">Select Category</option>
                        <option value="catering">Catering</option>
                        <option value="event_stylist">Event Stylist</option>
                    </select>
                </div>
                <div class="partners-form-group">
                    <label class="brochure-partners-label required">Name</label>
                    <input type="text" name="name" class="brochure-partners-input" placeholder="Partner name" required>
                </div>
                <div class="partners-form-group">
                    <label class="brochure-partners-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="brochure-partners-input"
                        placeholder="https://facebook.com/...">
                </div>
                <div class="partners-form-group">
                    <label class="brochure-partners-label">Phones</label>
                    <input type="text" name="phones" class="brochure-partners-input"
                        placeholder="Comma or newline separated">
                </div>
                <div class="partners-form-group">
                    <label class="brochure-partners-label">Image Path</label>
                    <input type="text" name="image_path" class="brochure-partners-input"
                        placeholder="public/images/partners/...">
                </div>
                <div class="partners-form-group">
                    <label class="brochure-partners-label">Sort Order</label>
                    <input type="number" name="sort_order" class="brochure-partners-input input-number-sm" value="0"
                        min="0">
                </div>
                <div class="partners-form-group full-width">
                    <div class="brochure-partners-btn-group">
                        <button type="submit" class="btn-partner-add">
                            <i class="fas fa-plus"></i> Add Partner
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Partners Table -->
        <?php if (!empty($partners)): ?>
            <div class="brochure-partners-table-wrapper">
                <table class="brochure-partners-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Facebook</th>
                            <th>Phones</th>
                            <th>Image Path</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partners as $partner): ?>
                            <tr>
                                <form method="POST" action="Components/Admin/data_processing.php" style="display: contents;">
                                    <input type="hidden" name="action" value="update_partner">
                                    <input type="hidden" name="id" value="<?php echo (int) $partner['id']; ?>">
                                    <td>
                                        <select name="category" class="brochure-partners-select" required>
                                            <option value="catering" <?php echo $partner['category'] === 'catering' ? 'selected' : ''; ?>>Catering</option>
                                            <option value="event_stylist" <?php echo $partner['category'] === 'event_stylist' ? 'selected' : ''; ?>>Event Stylist</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="name" class="brochure-partners-input"
                                            value="<?php echo htmlspecialchars($partner['name']); ?>" required>
                                    </td>
                                    <td>
                                        <input type="url" name="facebook_url" class="brochure-partners-input"
                                            value="<?php echo htmlspecialchars($partner['facebook_url'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="phones" class="brochure-partners-input"
                                            value="<?php echo htmlspecialchars($partner['phones'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="image_path" class="brochure-partners-input"
                                            value="<?php echo htmlspecialchars($partner['image_path'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="sort_order" class="brochure-partners-input input-number-sm"
                                            value="<?php echo (int) $partner['sort_order']; ?>" min="0">
                                    </td>
                                    <td style="text-align: center;">
                                        <label
                                            style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                            <input type="checkbox" name="is_active" value="1" class="brochure-partners-checkbox"
                                                <?php echo (int) $partner['is_active'] === 1 ? 'checked' : ''; ?>>
                                            <span
                                                class="badge-status <?php echo (int) $partner['is_active'] === 1 ? 'active' : 'inactive'; ?>">
                                                <?php echo (int) $partner['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </label>
                                    </td>
                                    <td style="white-space: nowrap; display: flex; gap: 0.5rem;">
                                        <button type="submit" class="btn-table-save">
                                            <i class="fas fa-save"></i> Save
                                        </button>
                                </form>
                                <form method="POST" action="Components/Admin/data_processing.php"
                                    onsubmit="return confirm('Delete this partner?');" style="display: contents;">
                                    <input type="hidden" name="action" value="delete_partner">
                                    <input type="hidden" name="id" value="<?php echo (int) $partner['id']; ?>">
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
                <p class="brochure-partners-empty-text">No partners found. Create one to get started!</p>
            </div>
        <?php endif; ?>
    </div>
</div>