<?php
/**
 * Shared Admin Section Header Component (v3 — Fancy)
 *
 * Variables (set before including this file):
 *   $sectionTitle    (string, required) - Main heading text
 *   $sectionIcon     (string, optional) - FontAwesome icon class e.g. 'fa-users'
 *   $sectionSubtitle (string, optional) - Short description shown below the title
 *   $sectionActions  (string, optional) - Raw HTML for right-side action buttons in the title bar
 *   $sectionBadge    (string, optional) - Raw HTML badge rendered inline after the title
 *   $sectionFilters  (string, optional) - Raw HTML for the filter bar rendered below the title
 *   $sectionId       (string, optional) - id="" attribute on the root element
 */

$_sh_title    = $sectionTitle    ?? '';
$_sh_icon     = $sectionIcon     ?? '';
$_sh_subtitle = $sectionSubtitle ?? '';
$_sh_actions  = $sectionActions  ?? '';
$_sh_id       = $sectionId       ?? '';
$_sh_badge    = $sectionBadge    ?? '';
$_sh_filters  = $sectionFilters  ?? '';

unset($sectionTitle, $sectionIcon, $sectionSubtitle, $sectionActions, $sectionId, $sectionBadge, $sectionFilters);

$_sh_has_filters = trim($_sh_filters) !== '';
?>
<div class="admin-section-header mb-4"<?= $_sh_id ? ' id="' . htmlspecialchars($_sh_id) . '"' : '' ?>
     style="border-radius:1rem;
            box-shadow:0 20px 40px rgba(30,58,138,.2),0 8px 16px rgba(37,99,235,.14),0 2px 6px rgba(0,0,0,.08);
            overflow:hidden;">

    <!-- ░░ Title bar ░░ -->
    <div class="position-relative d-flex justify-content-between align-items-center px-4"
         style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 48%,#0ea5e9 100%);
                min-height:76px;overflow:hidden;padding-top:1rem;padding-bottom:1rem;">

        <!-- Dot-grid texture overlay -->
        <div style="position:absolute;inset:0;
                    background-image:radial-gradient(circle at 1.5px 1.5px,rgba(255,255,255,0.09) 1.5px,transparent 0);
                    background-size:22px 22px;pointer-events:none;"></div>

        <!-- Top gloss line -->
        <div style="position:absolute;top:0;left:0;right:0;height:1px;
                    background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.55) 25%,rgba(255,255,255,.55) 75%,transparent 100%);
                    pointer-events:none;"></div>

        <!-- Decorative bubbles (top-right) -->
        <div style="position:absolute;right:-35px;top:-60px;width:200px;height:200px;
                    background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;right:75px;bottom:-70px;width:160px;height:160px;
                    background:rgba(255,255,255,.04);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;right:180px;top:-30px;width:90px;height:90px;
                    background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;"></div>

        <!-- Left: icon + text -->
        <div class="d-flex align-items-center gap-3 position-relative">

            <?php if ($_sh_icon): ?>
            <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:50px;height:50px;
                        background:rgba(255,255,255,.15);
                        border-radius:14px;
                        box-shadow:0 0 0 1px rgba(255,255,255,.22),
                                   inset 0 1px 0 rgba(255,255,255,.35),
                                   0 6px 16px rgba(0,0,0,.2);
                        backdrop-filter:blur(8px);">
                <i class="fas <?= htmlspecialchars($_sh_icon) ?> text-white"
                   style="font-size:1.25rem;text-shadow:0 2px 6px rgba(0,0,0,.25);"></i>
            </div>
            <?php endif; ?>

            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0 fw-bold text-white"
                        style="font-size:1.1rem;letter-spacing:-0.025em;line-height:1.2;
                               text-shadow:0 1px 4px rgba(0,0,0,.2);">
                        <?= htmlspecialchars($_sh_title) ?>
                    </h5>
                    <?= $_sh_badge ?>
                </div>
                <?php if ($_sh_subtitle): ?>
                <p class="mb-0 mt-1 text-white"
                   style="font-size:0.775rem;line-height:1.4;opacity:.72;letter-spacing:.01em;">
                    <?= htmlspecialchars($_sh_subtitle) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: action buttons -->
        <?php if ($_sh_actions): ?>
        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-3 position-relative">
            <?= $_sh_actions ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($_sh_has_filters): ?>
    <!-- ░░ Filter bar ░░ -->
    <div class="admin-section-filters px-4"
         style="background:#f4f8ff;border-top:1px solid rgba(37,99,235,.14);">
        <?= $_sh_filters ?>
    </div>
    <?php endif; ?>

</div>
