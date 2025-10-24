<?php
// Debug script to check if sections are working
// Include database connection
require_once 'src/database/db_connect.php';

// For debugging purposes, we'll mock the required variables instead of requiring admin session

// Check if tables exist and get real data or use mock data
$total_rooms = 0;
$total_facilities = 0;
$active_bookings = 0;
$feedback_stats = [
    'total_feedback' => 0,
    'avg_rating' => 0,
    'five_star' => 0,
    'four_star' => 0,
    'three_star' => 0,
    'two_star' => 0,
    'one_star' => 0
];
$status_distribution = [
    'pending' => 0,
    'approved' => 0,
    'confirmed' => 0,
    'checked_in' => 0,
    'checked_out' => 0,
    'cancelled' => 0,
    'rejected' => 0
];
$monthly_bookings = [];
$recent_activities = [];
$room_events = [];

// Try to get real data, but don't fail if tables don't exist
try {
    // Total Rooms
    $total_rooms_result = $conn->query("SELECT COUNT(*) AS count FROM items WHERE item_type='room'");
    if ($total_rooms_result) {
        $total_rooms = $total_rooms_result->fetch_assoc()['count'];
    }
    
    // Total Facilities
    $total_facilities_result = $conn->query("SELECT COUNT(*) AS count FROM items WHERE item_type='facility'");
    if ($total_facilities_result) {
        $total_facilities = $total_facilities_result->fetch_assoc()['count'];
    }
    
    // Active Bookings
    $active_bookings_result = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status='approved'");
    if ($active_bookings_result) {
        $active_bookings = $active_bookings_result->fetch_assoc()['count'];
    }
    
    // Feedback Statistics
    $feedback_stats_result = $conn->query("SELECT 
        COUNT(*) as total_feedback,
        COALESCE(AVG(rating), 0) as avg_rating,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
        COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
        COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
        COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
        FROM feedback");
    if ($feedback_stats_result) {
        $feedback_stats = $feedback_stats_result->fetch_assoc();
    }
    
    // Booking status distribution
    $statuses = ['pending', 'approved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'rejected'];
    foreach ($statuses as $status) {
        $result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status='$status'");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            $status_distribution[$status] = (int) $count;
        }
    }
    
} catch (Exception $e) {
    // If database queries fail, we'll use mock data
    $total_rooms = 5;
    $total_facilities = 3;
    $active_bookings = 2;
    $feedback_stats = [
        'total_feedback' => 10,
        'avg_rating' => 4.5,
        'five_star' => 7,
        'four_star' => 2,
        'three_star' => 1,
        'two_star' => 0,
        'one_star' => 0
    ];
}

$total_bookings = array_sum($status_distribution);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Sections</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    
    <style>
        .content-section { 
            display: none; 
            border: 2px solid #007bff; 
            padding: 20px; 
            margin: 10px; 
            border-radius: 8px;
            background: #f8f9fa;
        }
        .content-section.active { 
            display: block; 
        }
        .nav-link { 
            cursor: pointer; 
            padding: 10px 15px; 
            margin: 5px; 
            background: #007bff; 
            color: white; 
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .nav-link:hover {
            background: #0056b3;
            color: white;
        }
        .nav-link.active {
            background: #28a745;
        }
        .debug-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* Additional styles for dashboard components */
        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quick-action-card {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .quick-action-card:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.2);
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .bg-purple { background-color: #6f42c1 !important; }
        .text-purple { color: #6f42c1 !important; }
    </style>
</head>
<body class="bg-light">
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="debug-header">
            <h1 class="text-center mb-2">
                <i class="fas fa-bug me-2"></i>Debug Sections Testing
            </h1>
            <p class="text-center mb-0 opacity-75">
                Testing dashboard sections functionality and data processing
            </p>
        </div>
        
        <!-- Navigation -->
        <div class="text-center mb-4">
            <a href="#" class="nav-link active" onclick="showSection('dashboard-section', this)">
                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
            </a>
            <a href="#" class="nav-link" onclick="showSection('calendar-section', this)">
                <i class="fas fa-calendar me-1"></i>Calendar
            </a>
            <a href="#" class="nav-link" onclick="showSection('rooms', this)">
                <i class="fas fa-building me-1"></i>Rooms
            </a>
            <a href="#" class="nav-link" onclick="showSection('bookings', this)">
                <i class="fas fa-calendar-check me-1"></i>Bookings
            </a>
        </div>

        <!-- Debug Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Debug Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Total Rooms:</strong> <?php echo $total_rooms; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Total Facilities:</strong> <?php echo $total_facilities; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Active Bookings:</strong> <?php echo $active_bookings; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Avg Rating:</strong> <?php echo number_format($feedback_stats['avg_rating'], 1); ?>/5.0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sections -->
        <section id="dashboard-section" class="content-section active">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Section</h2>
            <div class="container-fluid">
                <?php include 'src/components/dashboard/sections/dashboard_section.php'; ?>
            </div>
        </section>

        <section id="calendar-section" class="content-section">
            <h2><i class="fas fa-calendar me-2"></i>Calendar Section</h2>
            <div class="container-fluid">
                <?php include 'src/components/dashboard/sections/calendar_section.php'; ?>
            </div>
        </section>

        <section id="rooms" class="content-section">
            <h2><i class="fas fa-building me-2"></i>Rooms Section</h2>
            <div class="container-fluid">
                <?php include 'src/components/dashboard/sections/rooms_section.php'; ?>
            </div>
        </section>

        <section id="bookings" class="content-section">
            <h2><i class="fas fa-calendar-check me-2"></i>Bookings Section</h2>
            <div class="container-fluid">
                <?php include 'src/components/dashboard/sections/bookings_section.php'; ?>
            </div>
        </section>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    
    <script>
        // Global variables for calendar
        let calendarInstance = null;
        
        function showSection(sectionId, clickedElement = null) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show target section
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
                console.log('Showing section:', sectionId);
            } else {
                console.error('Section not found:', sectionId);
            }
            
            // Add active class to clicked nav link
            if (clickedElement) {
                clickedElement.classList.add('active');
            }
            
            // Initialize calendar if calendar section is shown
            if (sectionId === 'calendar-section') {
                setTimeout(() => {
                    if (typeof initializeRoomCalendar === 'function') {
                        initializeRoomCalendar();
                    }
                }, 100);
            }
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, dashboard section should be visible');
            
            // Ensure dashboard section is active on load
            showSection('dashboard-section', document.querySelector('.nav-link.active'));
        });
    </script>
</body>
</html>