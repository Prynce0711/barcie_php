<?php
$addLabel = $addLabel ?? 'Add New';
$addIcon = $addIcon ?? 'fa-plus';
$addTarget = $addTarget ?? '';
$addClass = $addClass ?? 'btn-primary';
$addSize = $addSize ?? 'btn-sm';
$addOnclick = $addOnclick ?? '';
$addId = $addId ?? '';
?>
<button type="button"
    class="btn <?= htmlspecialchars($addClass) ?> <?= htmlspecialchars($addSize) ?> barcie-action-btn barcie-action-add"
    <?= $addId ? 'id="' . htmlspecialchars($addId) . '"' : '' ?>
    <?= $addTarget ? 'data-bs-toggle="modal" data-bs-target="' . htmlspecialchars($addTarget) . '"' : '' ?>
    <?= $addOnclick ? 'onclick="' . htmlspecialchars($addOnclick) . '"' : '' ?>>
    <i class="fas <?= htmlspecialchars($addIcon) ?><?= $addLabel ? ' me-1' : '' ?>"></i><?= htmlspecialchars($addLabel) ?>
</button>
<?php unset($addLabel, $addIcon, $addTarget, $addClass, $addSize, $addOnclick, $addId); ?>