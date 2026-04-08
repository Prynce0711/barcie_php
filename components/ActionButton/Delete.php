<?php
$deleteLabel = $deleteLabel ?? 'Delete';
$deleteIcon = $deleteIcon ?? 'fa-trash';
$deleteClass = $deleteClass ?? 'btn-outline-danger';
$deleteSize = $deleteSize ?? 'btn-sm';
$deleteOnclick = $deleteOnclick ?? '';
$deleteId = $deleteId ?? '';
$deleteDataId = $deleteDataId ?? '';
$deleteItemName = $deleteItemName ?? '';
$deleteTitle = $deleteTitle ?? 'Delete';
?>
<button type="button"
    class="btn <?= htmlspecialchars($deleteClass) ?> <?= htmlspecialchars($deleteSize) ?> barcie-action-btn barcie-action-delete"
    <?= $deleteId ? 'id="' . htmlspecialchars($deleteId) . '"' : '' ?>
    <?= $deleteDataId ? 'data-item-id="' . htmlspecialchars($deleteDataId) . '"' : '' ?>
    <?= $deleteItemName ? 'data-item-name="' . htmlspecialchars($deleteItemName) . '"' : '' ?>
    <?= $deleteOnclick ? 'onclick="' . htmlspecialchars($deleteOnclick) . '"' : '' ?>
    title="<?= htmlspecialchars($deleteTitle) ?>">
    <i class="fas <?= htmlspecialchars($deleteIcon) ?><?= $deleteLabel ? ' me-1' : '' ?>"></i><?= htmlspecialchars($deleteLabel) ?>
</button>
<?php unset($deleteLabel, $deleteIcon, $deleteClass, $deleteSize, $deleteOnclick, $deleteId, $deleteDataId, $deleteItemName, $deleteTitle); ?>