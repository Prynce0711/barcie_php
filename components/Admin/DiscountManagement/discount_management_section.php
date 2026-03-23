<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

require_once __DIR__ . '/../../../database/config.php';
require_once __DIR__ . '/../../../database/modules/discount_rules.php';

$rules = discount_get_rules($conn, false);
$idCatalog = discount_allowed_id_types_catalog();

// Prepare section header variables
$sectionTitle = 'Discount Rules Management';
$sectionIcon = 'fa-tag';
$sectionSubtitle = 'Create and manage discount codes for customers';
?>

<link rel="stylesheet" href="Components/Admin/Brochure/brochure_partners_management.css">

<div class="brochure-partners-section">
  <?php include __DIR__ . '/../Shared/SectionHeader.php'; ?>

  <div style="padding: 2rem;">
    <!-- Add Discount Rule Form -->
    <form method="POST" action="Components/Admin/data_processing.php" class="brochure-partners-form-wrapper">
      <input type="hidden" name="action" value="add_discount_rule">
      <div class="brochure-partners-form-grid">
        <div class="partners-form-group">
          <label class="brochure-partners-label required">Code</label>
          <input type="text" name="code" class="brochure-partners-input" placeholder="lcupstudent" required>
        </div>
        <div class="partners-form-group">
          <label class="brochure-partners-label required">Label</label>
          <input type="text" name="label" class="brochure-partners-input" placeholder="e.g., LC UP Student" required>
        </div>
        <div class="partners-form-group">
          <label class="brochure-partners-label required">Percentage (%)</label>
          <input type="number" name="percentage" class="brochure-partners-input" step="0.01" min="0" max="100" required>
        </div>
        <div class="partners-form-group">
          <label class="brochure-partners-label">Keywords (comma separated)</label>
          <input type="text" name="keywords" class="brochure-partners-input" placeholder="lcup,student,alumni">
        </div>
        <div class="partners-form-group full-width">
          <label class="brochure-partners-label">Description</label>
          <textarea name="description" rows="2" class="brochure-partners-textarea" placeholder="Brief description of this discount rule..."></textarea>
        </div>
        <div class="partners-form-group full-width">
          <label class="brochure-partners-label">Accepted ID Types</label>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach ($idCatalog as $idKey => $idLabel): ?>
              <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.9375rem; cursor: pointer;">
                <input type="checkbox" name="accepted_id_types[]" value="<?php echo htmlspecialchars($idKey); ?>" style="width: 1.125rem; height: 1.125rem; cursor: pointer; accent-color: #3b82f6;">
                <span><?php echo htmlspecialchars($idLabel); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="partners-form-group full-width">
          <div class="brochure-partners-btn-group">
            <button type="submit" class="btn-brochure-add">
              <i class="fas fa-plus"></i> Add Discount Rule
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Discount Rules Table -->
    <?php if (!empty($rules)): ?>
      <div class="brochure-partners-table-wrapper">
        <table class="brochure-partners-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Label</th>
              <th>%</th>
              <th>Description</th>
              <th>Allowed IDs</th>
              <th>Keywords</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rules as $rule): ?>
              <tr>
                <form method="POST" action="Components/Admin/data_processing.php" style="display: contents;">
                  <input type="hidden" name="action" value="update_discount_rule">
                  <input type="hidden" name="id" value="<?php echo (int) $rule['id']; ?>">
                  <td>
                    <input type="text" name="code" class="brochure-partners-input"
                      value="<?php echo htmlspecialchars($rule['code']); ?>" required>
                  </td>
                  <td>
                    <input type="text" name="label" class="brochure-partners-input"
                      value="<?php echo htmlspecialchars($rule['label']); ?>" required>
                  </td>
                  <td>
                    <input type="number" name="percentage" class="brochure-partners-input input-number-sm"
                      value="<?php echo htmlspecialchars((string) $rule['percentage']); ?>" step="0.01" min="0" max="100" required>
                  </td>
                  <td>
                    <textarea name="description" rows="2" class="brochure-partners-textarea"><?php echo htmlspecialchars($rule['description'] ?? ''); ?></textarea>
                  </td>
                  <td>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.5rem; max-height: 150px; overflow-y: auto; padding: 0.5rem;">
                      <?php foreach ($idCatalog as $idKey => $idLabel): ?>
                        <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; cursor: pointer; white-space: nowrap;">
                          <input type="checkbox" name="accepted_id_types[]" value="<?php echo htmlspecialchars($idKey); ?>"
                            <?php echo in_array($idKey, $rule['accepted_id_types'], true) ? 'checked' : ''; ?> style="width: 1rem; height: 1rem; cursor: pointer; accent-color: #3b82f6;">
                          <span><?php echo htmlspecialchars($idLabel); ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </td>
                  <td>
                    <input type="text" name="keywords" class="brochure-partners-input"
                      value="<?php echo htmlspecialchars(implode(', ', $rule['keywords'])); ?>">
                  </td>
                  <td style="text-align: center;">
                    <label style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                      <input type="checkbox" name="is_active" value="1" class="brochure-partners-checkbox"
                        <?php echo (int) $rule['is_active'] === 1 ? 'checked' : ''; ?>>
                      <span class="badge-status <?php echo (int) $rule['is_active'] === 1 ? 'active' : 'inactive'; ?>">
                        <?php echo (int) $rule['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                      </span>
                    </label>
                  </td>
                  <td style="white-space: nowrap; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn-table-save">
                      <i class="fas fa-save"></i> Save
                    </button>
                </form>
                <form method="POST" action="Components/Admin/data_processing.php"
                  onsubmit="return confirm('Delete this discount rule?');" style="display: contents;">
                  <input type="hidden" name="action" value="delete_discount_rule">
                  <input type="hidden" name="id" value="<?php echo (int) $rule['id']; ?>">
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
        <p class="brochure-partners-empty-text">No discount rules found. Create one to get started!</p>
      </div>
    <?php endif; ?>
  </div>
</div>