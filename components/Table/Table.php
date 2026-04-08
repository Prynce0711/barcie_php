<?php
/**
 * Unified Table Component
 *
 * Usage:
 *   $tableId       = 'bookingsTable';       // required - unique table ID
 *   $tableScope    = 'bookings';            // required - pagination/filter scope
 *   $tableColumns  = [                      // required - column definitions
 *       ['label' => 'Name',   'width' => '20%'],
 *       ['label' => 'Status', 'width' => '10%'],
 *   ];
 *   $tablePageSize = 10;                    // optional - items per page (default 10)
 *   $tableClass    = '';                    // optional - extra classes on <table>
 *   $tableEmpty    = 'No records found.';   // optional - empty state message
 *   include __DIR__ . '/../Table/Table.php'; // outputs opening wrapper
 *
 *   // --- render your <tr> rows here (or include a content file) ---
 *
 *   $tableClose = true;
 *   include __DIR__ . '/../Table/Table.php'; // outputs closing wrapper + pagination
 */

if (!empty($tableClose)) {
    // ── CLOSING PART ──────────────────────────────────────────
    $tableClose = false; // reset for next use
    ?>
            </tbody>
        </table>
    </div>
    <!-- Unified Pagination -->
    <div class="barcie-table-pagination" data-table-pagination data-scope="<?= htmlspecialchars($tableScope ?? 'default') ?>" data-page-size="<?= (int)($tablePageSize ?? 10) ?>">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <small class="text-muted" data-pagination-info>No items</small>
            <div class="d-flex align-items-center gap-1">
                <div class="btn-group btn-group-sm" role="group" aria-label="Pagination">
                    <button type="button" class="btn btn-light btn-sm" data-pagination-prev disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="btn btn-light btn-sm disabled" data-pagination-current style="min-width:70px; pointer-events:none;">
                        Page 1
                    </span>
                    <button type="button" class="btn btn-light btn-sm" data-pagination-next disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    return; // stop here for the closing part
}

// ── OPENING PART ──────────────────────────────────────────
$tableId       = $tableId       ?? 'dataTable';
$tableScope    = $tableScope    ?? 'default';
$tableColumns  = $tableColumns  ?? [];
$tablePageSize = $tablePageSize ?? 10;
$tableClass    = $tableClass    ?? '';
$tableEmpty    = $tableEmpty    ?? 'No records found.';
?>
<div class="barcie-table-wrapper" data-table-wrapper data-scope="<?= htmlspecialchars($tableScope) ?>">
    <div class="table-responsive">
        <table class="table table-hover align-middle barcie-table <?= htmlspecialchars($tableClass) ?>" id="<?= htmlspecialchars($tableId) ?>">
            <thead>
                <tr>
                    <?php foreach ($tableColumns as $col): ?>
                        <th <?php if (!empty($col['width'])): ?>style="width:<?= htmlspecialchars($col['width']) ?>"<?php endif; ?>><?= $col['label'] ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
