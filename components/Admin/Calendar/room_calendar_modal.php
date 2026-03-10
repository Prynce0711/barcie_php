<?php
/**
 * Room Calendar Modal Component
 * Guest AvailabilityCalendar style — simple header with inline nav buttons
 */
?>

<!-- Room Calendar Modal (guest-style) -->
<div class="modal fade" id="roomCalendarModal" tabindex="-1" aria-labelledby="roomCalendarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 720px;">
    <div class="modal-content">

      <!-- Simple header with title, month navigation, close -->
      <div class="modal-header">
        <h5 class="modal-title" id="roomCalendarModalLabel">Room Calendar</h5>
        <span class="mx-2 fw-semibold text-muted" id="roomCalendarMonthTitle" style="font-size: 0.95rem;"></span>
        <div class="btn-group btn-group-sm ms-auto me-2" role="group" aria-label="Calendar navigation">
          <button type="button" class="btn btn-outline-secondary" title="Previous" onclick="if(window.adminCalPrev) adminCalPrev();">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button type="button" class="btn btn-outline-secondary" title="Today" onclick="if(window.adminCalToday) adminCalToday();">Today</button>
          <button type="button" class="btn btn-outline-secondary" title="Next" onclick="if(window.adminCalNext) adminCalNext();">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div id="roomCalendarInner" style="min-height: 220px; position: relative;">

          <!-- Inline legend bar (guest style) -->
          <div id="roomCalendarLegend" style="margin-bottom:12px;display:flex;gap:12px;align-items:center;padding:8px;background:#f8f9fa;border-radius:6px;flex-wrap:wrap;">
            <small class="text-muted fw-bold me-2">Legend:</small>
            <div style="display:flex;align-items:center;gap:6px;">
              <div style="width:16px;height:16px;background:#fd7e14;border:1px solid #fd7e14;border-radius:3px;"></div>
              <small class="text-muted">Pencil Booking</small>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <div style="width:16px;height:16px;background:#ffc107;border:1px solid #ffc107;border-radius:3px;"></div>
              <small class="text-muted">Pending</small>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <div style="width:16px;height:16px;background:#28a745;border:1px solid #28a745;border-radius:3px;"></div>
              <small class="text-muted">Approved</small>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
              <div style="width:16px;height:16px;background:#dc3545;border:1px solid #dc3545;border-radius:3px;"></div>
              <small class="text-muted">Booked</small>
            </div>
          </div>

          <!-- FullCalendar mount point -->
          <div id="roomModalCalendar"></div>

          <!-- Loading overlay -->
          <div id="roomModalSpinner" style="position:absolute;left:0;right:0;top:0;bottom:0;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.85);z-index:50;">
            <div class="text-center text-muted">
              <div class="spinner-border text-secondary" role="status" style="width:2rem;height:2rem"></div>
              <div class="mt-2">Loading schedule&hellip;</div>
            </div>
          </div>
        </div>

        <!-- Booking Details Panel (shown when clicking an event) -->
        <div id="roomBookingDetails" class="mt-3" style="display: none;">
          <div id="roomBookingDetailsContent"></div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<style>
/* Spinner overlay */
.spinner-overlay {
  transition: opacity 250ms ease-in-out;
  opacity: 0;
  pointer-events: none;
  display: none;
}
.spinner-overlay.spinner-visible {
  opacity: 1;
  pointer-events: auto;
  display: flex !important;
}

/* Calendar event text readability */
#roomModalCalendar .fc-col-header-cell,
#roomModalCalendar .fc-col-header-cell .fc-col-header-cell-cushion {
  color: #ffffff !important;
}
#roomModalCalendar .fc .fc-event,
#roomModalCalendar .fc .fc-event-main,
#roomModalCalendar .fc .fc-event-title {
  color: #ffffff !important;
}

/* Clean calendar styling */
#roomModalCalendar .fc-scrollgrid {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e9ecef;
}
#roomModalCalendar .fc-col-header-cell {
  background: #0d6efd;
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
  padding: 0.6rem 0.25rem;
}
#roomModalCalendar .fc-daygrid-day-number {
  font-weight: 600;
  color: #333;
}
#roomModalCalendar .fc-day-today .fc-daygrid-day-number {
  background: #0d6efd;
  color: #fff;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}
#roomModalCalendar .fc-event {
  border: none;
  border-radius: 4px;
  padding: 2px 4px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}
#roomModalCalendar .fc-event:hover {
  box-shadow: 0 3px 8px rgba(0,0,0,0.2);
  transform: translateY(-1px);
  transition: all 0.15s ease;
}
#roomModalCalendar .fc-scrollgrid td,
#roomModalCalendar .fc-scrollgrid th {
  border-color: #e9ecef;
}

/* Booking badge styles */
.booking-badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}
.badge-pending { background: #ffc107; color: #000; }
.badge-approved { background: #28a745; }
.badge-confirmed { background: #17a2b8; }
.badge-checkedin { background: #0d6efd; }
.badge-checkedout { background: #6c757d; }
.badge-cancelled { background: #f39c12; }
.badge-warning { background: #fd7e14; }

/* Booking details styling */
#roomBookingDetailsContent .detail-row { margin-bottom: 0.5rem; padding: 0.25rem 0; }
#roomBookingDetailsContent .detail-key { color: #6c757d; min-width: 110px; display: inline-block; font-weight: 600; font-size: 0.9rem; }

@media (max-width: 768px) {
  #roomCalendarLegend { font-size: 0.75rem; gap: 8px !important; }
}
</style>
