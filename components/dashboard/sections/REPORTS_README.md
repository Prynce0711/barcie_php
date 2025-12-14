# Reports and Analytics Module

## Overview
A comprehensive reports and analytics system for the BarCIE Hotel Management System. This module provides detailed insights into bookings, revenue, occupancy, guests, and rooms with advanced filtering, visualization, and export capabilities.

## Features

### 1. Booking Reports
- **Daily & Monthly Bookings**: Track booking patterns over time
- **Status Breakdown**: View confirmed, cancelled, pending, and no-show bookings
- **Booking Sources**: Analyze walk-in vs. online bookings
- **Trend Analysis**: Visual charts showing booking trends

### 2. Occupancy Reports
- **Room Occupancy Rate**: Calculate and display occupancy percentages
- **Daily/Monthly Usage**: Track rooms used per period
- **Room Status**: View available, occupied, and maintenance status
- **Peak Occupancy**: Identify highest occupancy dates

### 3. Revenue Reports
- **Total Revenue**: Track income across all bookings
- **Revenue by Date**: Daily and monthly revenue breakdowns
- **Revenue by Room Type**: Compare performance across room categories
- **Average Calculations**: Daily and monthly revenue averages

### 4. Guest Reports
- **Total Guests**: Count unique guests
- **Average Stay Length**: Calculate typical booking duration
- **Return Guests**: Identify repeat customers
- **Top Guests**: Rank by bookings and spending

### 5. Room Reports
- **Most/Least Booked Rooms**: Identify best and worst performers
- **Room Performance**: Compare bookings across all rooms
- **Room Type Distribution**: Analyze inventory status
- **Revenue by Room**: Track earnings per room

### 6. Export & Filters
- **Date Range Filtering**: Custom date selection
- **Room Type Filtering**: Filter by specific room categories
- **PDF Export**: Generate professional PDF reports
- **Excel Export**: Download CSV files for further analysis

## Installation

### Step 1: Database Setup

No additional database tables are required. The module uses existing tables:
- `bookings`
- `rooms`

Ensure your database has the following columns in the `bookings` table:
- `id`, `room_id`, `user_name`, `user_email`, `check_in_date`, `check_out_date`
- `total_price`, `status`, `booking_source` (optional)

### Step 2: File Structure

The module consists of the following files:

```
components/dashboard/sections/
  └── reports_section.php          # Main UI component

api/
  ├── reports_data.php              # Data API endpoint
  ├── export_report_pdf.php         # PDF export handler
  └── export_report_excel.php       # Excel/CSV export handler

assets/
  ├── css/
  │   └── reports.css               # Styling
  └── js/
      └── dashboard/
          └── reports.js            # JavaScript functionality
```

### Step 3: Integration with Dashboard

Add the reports section to your dashboard. In your main dashboard file (e.g., `dashboard.php`), add:

```php
<!-- Add to navigation menu -->
<li class="nav-item">
    <a class="nav-link" href="#reports" data-section="reports">
        <i class="fas fa-chart-bar"></i> Reports & Analytics
    </a>
</li>

<!-- Add to content sections -->
<div id="reports" class="dashboard-section" style="display:none;">
    <?php include 'components/dashboard/sections/reports_section.php'; ?>
</div>
```

### Step 4: Include Required Assets

Add these lines to your dashboard's `<head>` section:

```html
<!-- Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Reports CSS -->
<link rel="stylesheet" href="assets/css/reports.css">
```

Add this before the closing `</body>` tag:

```html
<!-- Reports JavaScript -->
<script src="assets/js/dashboard/reports.js"></script>
```

### Step 5: Verify Dependencies

Ensure the following dependencies are installed:

1. **DomPDF** (for PDF export) - Already included in your vendor folder
2. **Chart.js** (for charts) - Loaded via CDN
3. **Bootstrap 5** (for UI) - Should already be included

## Usage

### Accessing Reports

1. Navigate to the dashboard
2. Click on "Reports & Analytics" in the navigation menu
3. The overview report will load automatically

### Filtering Reports

1. **Date Range**: Select start and end dates
2. **Room Type**: Choose a specific room type or "All Room Types"
3. **Report Type**: Select from:
   - Overview (all reports combined)
   - Booking Reports
   - Occupancy Reports
   - Revenue Reports
   - Guest Reports
   - Room Reports
4. Click "Generate Report" to update

### Exporting Reports

- **PDF Export**: Click "Export PDF" to generate a professional PDF report
- **Excel Export**: Click "Export Excel" to download a CSV file

## API Endpoints

### GET `/api/reports_data.php`

Fetches report data based on filters.

**Parameters:**
- `start_date` (required): Start date (YYYY-MM-DD)
- `end_date` (required): End date (YYYY-MM-DD)
- `room_type` (optional): Filter by room type
- `report_type` (required): Type of report (overview, booking, occupancy, revenue, guest, room)

**Response:**
```json
{
    "success": true,
    "filters": {
        "start_date": "2025-01-01",
        "end_date": "2025-12-31",
        "room_type": ""
    },
    "data": {
        "summary": {...},
        "booking_reports": {...},
        "occupancy_reports": {...},
        ...
    }
}
```

### GET `/api/export_report_pdf.php`

Generates and downloads a PDF report.

**Parameters:** Same as `reports_data.php`

### GET `/api/export_report_excel.php`

Generates and downloads a CSV/Excel report.

**Parameters:** Same as `reports_data.php`

## Customization

### Adding New Report Types

1. Add a new function in `api/reports_data.php`:
```php
function getCustomReport($pdo, $startDate, $endDate, $roomType, $params) {
    // Your SQL queries here
    return $data;
}
```

2. Add the case in the switch statement:
```php
case 'custom':
    $response['data'] = getCustomReport($pdo, $startDate, $endDate, $roomType, $params);
    break;
```

3. Add UI section in `reports_section.php`

4. Add update function in `reports.js`

### Customizing Charts

Charts are rendered using Chart.js. To customize:

1. Find the chart rendering function in `reports.js` (e.g., `renderBookingTrendsChart()`)
2. Modify the `options` object:
```javascript
options: {
    responsive: true,
    plugins: {
        legend: {
            position: 'bottom'
        }
    },
    scales: {
        y: {
            beginAtZero: true
        }
    }
}
```

### Styling

All styles are in `assets/css/reports.css`. Key classes:
- `.report-section` - Main report sections
- `.summary-card` - Summary cards at the top
- `.card` - Card containers
- `.table` - Table styling

## Troubleshooting

### Charts Not Displaying

**Issue**: Charts appear blank or don't render

**Solutions:**
1. Verify Chart.js is loaded: Check browser console for errors
2. Ensure canvas elements have IDs matching JavaScript code
3. Check that data is being returned from API

### PDF Export Issues

**Issue**: PDF export fails or shows errors

**Solutions:**
1. Verify DomPDF is installed: Check `vendor/dompdf/` directory
2. Ensure write permissions for temporary files
3. Check PHP memory limit (increase if needed)

### Data Not Loading

**Issue**: Reports show "No data available"

**Solutions:**
1. Check database connection
2. Verify bookings exist in the date range
3. Check browser console for API errors
4. Verify API endpoint paths are correct

### Excel Export Issues

**Issue**: Excel file has encoding issues

**Solutions:**
1. The CSV includes UTF-8 BOM for Excel compatibility
2. Open in Excel using "Data > From Text/CSV" if needed
3. Ensure proper character encoding in database

## Database Optimization

For better performance on large datasets:

### Add Indexes

```sql
-- Add indexes for faster queries
ALTER TABLE bookings ADD INDEX idx_check_in_date (check_in_date);
ALTER TABLE bookings ADD INDEX idx_status (status);
ALTER TABLE bookings ADD INDEX idx_room_id (room_id);
ALTER TABLE bookings ADD INDEX idx_user_email (user_email);
ALTER TABLE rooms ADD INDEX idx_type (type);
```

### Add Booking Source Column (Optional)

If you want to track booking sources:

```sql
ALTER TABLE bookings 
ADD COLUMN booking_source VARCHAR(50) DEFAULT 'Online' 
AFTER status;
```

## Security Considerations

1. **Authentication**: Ensure only authenticated admin users can access reports
2. **SQL Injection**: All queries use prepared statements
3. **XSS Protection**: Data is escaped before display
4. **CSRF Protection**: Implement CSRF tokens for form submissions

## Browser Compatibility

- Chrome/Edge: Fully supported
- Firefox: Fully supported
- Safari: Fully supported
- IE11: Not supported (use modern browsers)

## Performance

- Report generation: ~1-3 seconds for typical datasets
- PDF export: ~3-5 seconds
- Excel export: ~1-2 seconds
- Optimize by limiting date ranges for large datasets

## Future Enhancements

Potential improvements:
- Real-time data updates
- Scheduled report emails
- Custom report builder
- Advanced analytics with ML predictions
- Dashboard widgets
- Comparison between date ranges

## Support

For issues or questions:
1. Check troubleshooting section
2. Review browser console for errors
3. Verify database structure
4. Check PHP error logs

## Version History

- **v1.0.0** (2025-12-14)
  - Initial release
  - All 6 report types implemented
  - PDF and Excel export
  - Chart visualizations
  - Advanced filtering

## License

Part of BarCIE Hotel Management System
