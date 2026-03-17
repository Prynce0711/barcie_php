<?php
ob_start(); ?>
<div class="row g-3 py-2">

    <div class="col-md-3">
        <label for="reportStartDate" class="form-label fw-semibold small">
            <i class="fas fa-calendar-alt me-1 text-primary"></i>Start Date
        </label>
        <input type="date" class="form-control form-control-sm" id="reportStartDate" value="<?php echo date('Y-m-01'); ?>">
    </div>

    <div class="col-md-3">
        <label for="reportEndDate" class="form-label fw-semibold small">
            <i class="fas fa-calendar-alt me-1 text-primary"></i>End Date
        </label>
        <input type="date" class="form-control form-control-sm" id="reportEndDate" value="<?php echo date('Y-m-d'); ?>">
    </div>

    <div class="col-md-3">
        <label for="reportRoomType" class="form-label fw-semibold small">
            <i class="fas fa-door-open me-1 text-primary"></i>Room Type
        </label>
        <select class="form-select form-select-sm" id="reportRoomType">
            <option value="">All Room Types</option>
            <option value="PENTHOUSE">PENTHOUSE</option>
            <option value="SUITE">SUITE</option>
            <option value="TRIPLE">TRIPLE</option>
            <option value="TWIN">TWIN</option>
            <option value="SINGLE">SINGLE</option>
        </select>
    </div>

    <div class="col-md-3">
        <label for="reportType" class="form-label fw-semibold small">
            <i class="fas fa-file-alt me-1 text-primary"></i>Report Type
        </label>
        <select class="form-select form-select-sm" id="reportType">
            <option value="overview">Overview</option>
            <option value="booking">Booking Reports</option>
            <option value="occupancy">Occupancy Reports</option>
            <option value="revenue">Revenue Reports</option>
            <option value="guest">Guest Reports</option>
            <option value="room">Room Reports</option>
        </select>
    </div>

    <div class="col-12">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="generateReport()">
                <i class="fas fa-sync me-1"></i>Generate Report
            </button>
            <button type="button" class="btn btn-sm btn-success" onclick="exportReportPDF()">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
            </button>
            <button type="button" class="btn btn-sm btn-info" onclick="exportReportExcel()">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </button>
        </div>
    </div>

</div>
<?php $sectionFilters = ob_get_clean(); ?>
<?php
$sectionTitle    = 'Reports & Analytics';
$sectionIcon     = 'fa-chart-bar';
$sectionSubtitle = 'Generate comprehensive reports and insights';
include __DIR__ . '/../../Shared/SectionHeader.php';
?>
