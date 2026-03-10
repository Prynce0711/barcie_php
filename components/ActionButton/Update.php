<?php
$updateLabel = $updateLabel ?? 'Update';
$updateIcon = $updateIcon ?? 'fa-save';
$updateClass = $updateClass ?? 'btn-success';
$updateSize = $updateSize ?? 'btn-sm';
$updateOnclick = $updateOnclick ?? '';
$updateId = $updateId ?? '';
$updateType = $updateType ?? 'button';
$updateTitle = $updateTitle ?? 'Update';
?>
<button type="<?= htmlspecialchars($updateType) ?>"
    class="btn <?= htmlspecialchars($updateClass) ?> <?= htmlspecialchars($updateSize) ?> barcie-action-btn barcie-action-update"
    <?= $updateId ? 'id="' . htmlspecialchars($updateId) . '"' : '' ?>
    <?= $updateOnclick ? 'onclick="' . htmlspecialchars($updateOnclick) . '"' : '' ?>
    title="<?= htmlspecialchars($updateTitle) ?>">
    <i class="fas <?= htmlspecialchars($updateIcon) ?><?= $updateLabel ? ' me-1' : '' ?>"></i><?= htmlspecialchars($updateLabel) ?>
</button>
<?php unset($updateLabel, $updateIcon, $updateClass, $updateSize, $updateOnclick, $updateId, $updateType, $updateTitle); ?>