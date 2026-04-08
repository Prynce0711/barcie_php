<?php
$editLabel = $editLabel ?? 'Edit';
$editIcon = $editIcon ?? 'fa-edit';
$editClass = $editClass ?? 'btn-outline-primary';
$editSize = $editSize ?? 'btn-sm';
$editOnclick = $editOnclick ?? '';
$editId = $editId ?? '';
$editDataId = $editDataId ?? '';
$editTitle = $editTitle ?? 'Edit';
?>
<button type="button"
    class="btn <?= htmlspecialchars($editClass) ?> <?= htmlspecialchars($editSize) ?> barcie-action-btn barcie-action-edit"
    <?= $editId ? 'id="' . htmlspecialchars($editId) . '"' : '' ?>
    <?= $editDataId ? 'data-item-id="' . htmlspecialchars($editDataId) . '"' : '' ?>
    <?= $editOnclick ? 'onclick="' . htmlspecialchars($editOnclick) . '"' : '' ?>
    title="<?= htmlspecialchars($editTitle) ?>">
    <i class="fas <?= htmlspecialchars($editIcon) ?><?= $editLabel ? ' me-1' : '' ?>"></i><?= htmlspecialchars($editLabel) ?>
</button>
<?php unset($editLabel, $editIcon, $editClass, $editSize, $editOnclick, $editId, $editDataId, $editTitle); ?>