<?php
/**
 * Room Calendar Modal Component
 * Displays a professional calendar view for individual rooms/facilities
 */
?>

<!-- Room Calendar Modal -->
<div class="modal fade" id="roomCalendarModal" tabindex="-1" aria-labelledby="roomCalendarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 1600px;">
    <div class="modal-content border-0 shadow-lg">
      
      <!-- Professional Blue Gradient Header -->
      <div class="modal-header border-0 position-relative" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%); padding: 1.5rem 2rem;">
        <div class="d-flex align-items-center w-100">
          <!-- Room Icon & Title -->
          <div class="d-flex align-items-center me-auto">
            <div class="modal-icon-wrapper me-3" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-calendar-alt text-white" style="font-size: 24px;"></i>
            </div>
            <div>
              <h5 class="modal-title text-white mb-0" id="roomCalendarModalLabel" style="font-weight: 600; font-size: 1.25rem;">
                Room Calendar
              </h5>
              <p class="text-white-50 mb-0 small">View availability and bookings</p>
            </div>
          </div>

          <!-- Range Selector -->
          <div class="d-flex align-items-center me-3">
            <label class="text-white-50 me-2 small" style="font-weight: 500;">View Range:</label>
            <select id="roomCalendarRange" class="form-select form-select-sm shadow-sm" style="width: 120px; background: rgba(255,255,255,0.95); border: none; border-radius: 8px; font-weight: 500;">
              <option value="30">30 days</option>
              <option value="90" selected>90 days</option>
              <option value="365">1 year</option>
            </select>
          </div>

          <!-- Close Button -->
          <button type="button" class="btn-close btn-close-white shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;"></button>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="modal-body" style="padding: 2rem; background: #f8f9fa;">
        
        <!-- Legend Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
          <div class="card-body py-3 px-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="d-flex align-items-center justify-content-center gap-4 flex-wrap">
              <div class="d-flex align-items-center">
                <span class="legend-indicator shadow-sm" style="display: inline-block; width: 24px; height: 24px; background: #d1ecf1; border: 2px solid #bee5eb; border-radius: 6px; margin-right: 10px;"></span>
                <span style="font-weight: 500; color: #0c5460; font-size: 0.95rem;">
                  <i class="fas fa-check-circle me-1"></i>Available
                </span>
              </div>
              <div class="d-flex align-items-center">
                <span class="legend-indicator shadow-sm" style="display: inline-block; width: 24px; height: 24px; background: #ffc107; border: 2px solid #ffc107; border-radius: 6px; margin-right: 10px;"></span>
                <span style="font-weight: 500; color: #856404; font-size: 0.95rem;">
                  <i class="fas fa-clock me-1"></i>Pending
                </span>
              </div>
              <div class="d-flex align-items-center">
                <span class="legend-indicator shadow-sm" style="display: inline-block; width: 24px; height: 24px; background: #fd7e14; border: 2px solid #fd7e14; border-radius: 6px; margin-right: 10px;"></span>
                <span style="font-weight: 500; color: #7d3f07; font-size: 0.95rem;">
                  <i class="fas fa-pencil-alt me-1"></i>Pencil Booking
                </span>
              </div>
              <div class="d-flex align-items-center">
                <span class="legend-indicator shadow-sm" style="display: inline-block; width: 24px; height: 24px; background: #f8d7da; border: 2px solid #f5c6cb; border-radius: 6px; margin-right: 10px;"></span>
                <span style="font-weight: 500; color: #721c24; font-size: 0.95rem;">
                  <i class="fas fa-ban me-1"></i>Booked
                </span>
              </div>
              <div class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>Click on a booking for details
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar Container -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
          <div class="card-body p-0">
            <div class="position-relative" style="min-height: 320px;">
              
              <!-- Loading Spinner -->
              <div id="roomModalSpinner" class="spinner-overlay d-flex justify-content-center align-items-center" style="position: absolute; inset: 0; background: rgba(255,255,255,0.9); z-index: 60; border-radius: 12px;">
                <div class="text-center">
                  <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading calendar...</span>
                  </div>
                  <p class="text-muted small mb-0">Loading room calendar...</p>
                </div>
              </div>

              <!-- FullCalendar Container -->
              <div id="roomModalCalendar" style="padding: 1.5rem;"></div>
            </div>
          </div>
        </div>

        <!-- Hidden Booking Details (for functionality) -->
        <div id="roomBookingDetails" style="display: none;">
          <div id="roomBookingDetailsContent"></div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-0" style="background: #f8f9fa; padding: 1.25rem 2rem;">
        <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px; padding: 0.5rem 1.5rem; font-weight: 500;">
          <i class="fas fa-times me-2"></i>Close
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Professional Calendar Styling -->
<style>
/* Spinner overlay with smooth fade */
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

/* Professional Calendar Styling */
#roomModalCalendar {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Toolbar Styling */
#roomModalCalendar .fc-toolbar {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border: 1px solid #e9ecef;
}

#roomModalCalendar .fc-toolbar-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e3c72;
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

/* Button Styling */
#roomModalCalendar .fc-button {
  background: #ffffff;
  border: 1px solid #dee2e6;
  color: #495057;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

#roomModalCalendar .fc-button:hover {
  background: #1e3c72;
  color: #ffffff;
  border-color: #1e3c72;
  box-shadow: 0 2px 8px rgba(30,60,114,0.3);
  transform: translateY(-1px);
}

#roomModalCalendar .fc-button:active,
#roomModalCalendar .fc-button-active {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
  color: #ffffff !important;
  border-color: #1e3c72 !important;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
}

#roomModalCalendar .fc-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Calendar Grid */
#roomModalCalendar .fc-scrollgrid {
  border: 1px solid #e9ecef;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

/* Table Headers */
#roomModalCalendar .fc-col-header-cell {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  color: #ffffff;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  padding: 1rem 0.5rem;
  border: none;
}

#roomModalCalendar .fc-col-header-cell-cushion {
  color: #ffffff;
  text-decoration: none;
}

/* Day Cells */
#roomModalCalendar .fc-daygrid-day {
  background: #ffffff;
  transition: background 0.2s ease;
}

#roomModalCalendar .fc-daygrid-day:hover {
  background: #f8f9fa;
}

#roomModalCalendar .fc-daygrid-day-frame {
  padding: 0.75rem 0.5rem;
  min-height: 80px;
}

#roomModalCalendar .fc-daygrid-day-top {
  padding: 0.25rem 0.5rem;
}

/* Day Numbers */
#roomModalCalendar .fc-daygrid-day-number {
  font-weight: 600;
  color: #1e3c72;
  font-size: 1rem;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  transition: all 0.2s ease;
}

#roomModalCalendar .fc-day-today .fc-daygrid-day-number {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  color: #ffffff;
  box-shadow: 0 2px 6px rgba(30,60,114,0.3);
}

#roomModalCalendar .fc-day-other .fc-daygrid-day-number {
  color: #adb5bd;
}

/* Event Styling */
#roomModalCalendar .fc-event {
  border: none;
  border-radius: 6px;
  padding: 0.35rem 0.5rem;
  margin: 2px 0;
  font-size: 0.85rem;
  font-weight: 500;
  box-shadow: 0 2px 4px rgba(0,0,0,0.12);
  transition: all 0.2s ease;
  cursor: pointer;
}

#roomModalCalendar .fc-event:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  transform: translateY(-1px);
}

#roomModalCalendar .fc-event-title {
  font-weight: 500;
}

/* Background Events (Available/Reserved) */
#roomModalCalendar .fc-bg-event {
  opacity: 0.3;
  border: none;
}

/* Grid Lines */
#roomModalCalendar .fc-scrollgrid td,
#roomModalCalendar .fc-scrollgrid th {
  border-color: #e9ecef;
}

/* Week Numbers */
#roomModalCalendar .fc-daygrid-week-number {
  background: #f8f9fa;
  color: #6c757d;
  font-weight: 500;
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  #roomModalCalendar .fc-toolbar {
    flex-direction: column;
    gap: 0.75rem;
  }

  #roomModalCalendar .fc-toolbar-title {
    font-size: 1.25rem;
  }

  #roomModalCalendar .fc-daygrid-day-frame {
    min-height: 60px;
  }

  .modal-header {
    padding: 1rem !important;
  }

  .modal-icon-wrapper {
    width: 40px !important;
    height: 40px !important;
  }

  .modal-icon-wrapper i {
    font-size: 20px !important;
  }
}

/* Legend hover effects */
.legend-indicator {
  transition: all 0.2s ease;
}

.legend-indicator:hover {
  transform: scale(1.1);
}

/* Booking badge styles */
.booking-badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.badge-pending { background: #ffc107; color: #000; }
.badge-approved { background: #28a745; }
.badge-confirmed { background: #17a2b8; }
.badge-checked_in { background: #0d6efd; }
.badge-checkedin { background: #0d6efd; }
.badge-checked_out { background: #6c757d; }
.badge-checkedout { background: #6c757d; }
.badge-cancelled { background: #f39c12; }
.badge-rejected { background: #dc3545; }

/* Booking details styling */
#roomBookingDetailsContent {
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

#roomBookingDetailsContent .detail-row {
  margin-bottom: 0.5rem;
  padding: 0.25rem 0;
}

#roomBookingDetailsContent .detail-key {
  color: #6c757d;
  min-width: 110px;
  display: inline-block;
  font-weight: 600;
  font-size: 0.9rem;
}

.booking-actions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}

.booking-actions .btn {
  margin-right: 0.5rem;
  border-radius: 6px;
  font-weight: 500;
  padding: 0.4rem 1rem;
}
</style>
