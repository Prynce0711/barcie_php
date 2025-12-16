# Quick Integration Guide - Reports & Analytics

## 5-Minute Setup

### Step 1: Add to Dashboard Navigation

Find your dashboard navigation (usually in `dashboard.php`) and add:

```php
<li class="nav-item">
    <a class="nav-link" href="#reports" data-section="reports" onclick="showSection('reports')">
        <i class="fas fa-chart-bar me-2"></i>
        Reports & Analytics
    </a>
</li>
```

### Step 2: Add Section Container

In your dashboard content area, add:

```php
<!-- Reports Section -->
<div id="reports" class="dashboard-section" style="display: none;">
    <?php include 'components/dashboard/sections/reports_section.php'; ?>
</div>
```

### Step 3: Include Assets in Dashboard Head

Add these lines in the `<head>` section of your dashboard:

```html
<!-- Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<!-- Reports CSS -->
<link rel="stylesheet" href="assets/css/reports.css">
```

### Step 4: Include JavaScript Before Closing Body

Add before `</body>`:

```html
<!-- Reports JavaScript -->
<script src="assets/js/dashboard/reports.js"></script>
```

### Step 5: Update Section Switching Function (if needed)

If your dashboard uses a section switching function, ensure it supports the reports section:

```javascript
function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.dashboard-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.style.display = 'block';
        
        // Initialize reports if opening reports section
        if (sectionId === 'reports' && typeof initReports === 'function') {
            initReports();
        }
    }
    
    // Update navigation active state
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    event.target.closest('.nav-link')?.classList.add('active');
}
```

## Testing

1. **Access Dashboard**: Log in as admin
2. **Click "Reports & Analytics"** in navigation
3. **Verify Data Loads**: Should see summary cards and reports
4. **Test Filters**: Change date range and click "Generate Report"
5. **Test Export**: Click "Export PDF" or "Export Excel"

## Common Issues & Fixes

### Issue 1: Section Not Showing
**Fix**: Verify the `showSection()` function is called correctly and the section ID matches

### Issue 2: Charts Not Rendering
**Fix**: Check browser console, ensure Chart.js CDN is loaded

### Issue 3: No Data Showing
**Fix**: Verify database has bookings data and date range includes bookings

### Issue 4: Export Not Working
**Fix**: Check that API files are accessible (api/export_report_pdf.php, api/export_report_excel.php)

## File Checklist

Verify these files exist:

- ✅ `components/dashboard/sections/reports_section.php`
- ✅ `api/reports_data.php`
- ✅ `api/export_report_pdf.php`
- ✅ `api/export_report_excel.php`
- ✅ `assets/css/reports.css`
- ✅ `assets/js/dashboard/reports.js`

## Database Requirements

Ensure your database has these tables:
- `bookings` (with columns: id, room_id, user_name, user_email, check_in_date, check_out_date, total_price, status)
- `rooms` (with columns: id, room_number, type, status)

## Optional: Add Booking Source Column

If you want to track booking sources (Walk-in, Online), run:

```sql
ALTER TABLE bookings 
ADD COLUMN booking_source VARCHAR(50) DEFAULT 'Online' 
AFTER status;
```

## Performance Tip

For faster queries on large datasets, add these indexes:

```sql
ALTER TABLE bookings ADD INDEX idx_check_in_date (check_in_date);
ALTER TABLE bookings ADD INDEX idx_status (status);
ALTER TABLE bookings ADD INDEX idx_room_id (room_id);
```

## Complete Example Dashboard Integration

Here's a complete example of how your dashboard.php might look:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BarCIE Hotel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/reports.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-section="dashboard" onclick="showSection('dashboard')">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#bookings" data-section="bookings" onclick="showSection('bookings')">
                                <i class="fas fa-calendar-check me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#rooms" data-section="rooms" onclick="showSection('rooms')">
                                <i class="fas fa-door-open me-2"></i>Rooms
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#reports" data-section="reports" onclick="showSection('reports')">
                                <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Dashboard Section -->
                <div id="dashboard" class="dashboard-section">
                    <?php include 'components/dashboard/sections/dashboard_section.php'; ?>
                </div>
                
                <!-- Bookings Section -->
                <div id="bookings" class="dashboard-section" style="display: none;">
                    <?php include 'components/dashboard/sections/bookings_section.php'; ?>
                </div>
                
                <!-- Rooms Section -->
                <div id="rooms" class="dashboard-section" style="display: none;">
                    <?php include 'components/dashboard/sections/rooms_section.php'; ?>
                </div>
                
                <!-- Reports Section -->
                <div id="reports" class="dashboard-section" style="display: none;">
                    <?php include 'components/dashboard/sections/reports_section.php'; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/toast-notification.js"></script>
    <script src="assets/js/dashboard/reports.js"></script>
    
    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.style.display = 'block';
                
                // Initialize reports if opening reports section
                if (sectionId === 'reports' && typeof generateReport === 'function') {
                    generateReport();
                }
            }
            
            // Update navigation active state
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('.nav-link')?.classList.add('active');
        }
    </script>
</body>
</html>
```

## You're Done! 🎉

The reports module is now integrated. Navigate to your dashboard and click "Reports & Analytics" to see it in action.

## Next Steps

1. Customize the styling in `assets/css/reports.css`
2. Add more report types as needed
3. Set up scheduled report emails
4. Add user permissions for viewing reports

## Need Help?

Check the full documentation in `REPORTS_README.md` for detailed information about:
- All features
- API endpoints
- Customization options
- Troubleshooting guide
- Performance optimization
