<?php
// Simple debug without session restrictions
require_once 'database/db_connect.php';

// Mock data for testing
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
$status_distribution = [
    'pending' => 1,
    'approved' => 2,
    'confirmed' => 1,
    'checked_in' => 0,
    'checked_out' => 3,
    'cancelled' => 0,
    'rejected' => 0
];
$total_bookings = 7;
$monthly_bookings = [];
$recent_activities = [];
$room_events = [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Debug Sections</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
        
        /* Additional dashboard styles */
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
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="text-center mb-4">
            <h1><i class="fas fa-bug me-2"></i>Simple Debug Sections Testing</h1>
        </div>
        
        <!-- Navigation -->
        <div class="text-center mb-4">
            <a href="#" class="nav-link active" onclick="showSection('dashboard-section', this)">
                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
            </a>
            <a href="#" class="nav-link" onclick="showSection('test-section', this)">
                <i class="fas fa-vial me-1"></i>Test Section
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
            <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Section (WORKING!)</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Dashboard Content</h5>
                            <p>This section is displaying correctly!</p>
                            <ul>
                                <li>Total Rooms: <?php echo $total_rooms; ?></li>
                                <li>Total Facilities: <?php echo $total_facilities; ?></li>
                                <li>Active Bookings: <?php echo $active_bookings; ?></li>
                                <li>Average Rating: <?php echo number_format($feedback_stats['avg_rating'], 1); ?>/5.0</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Quick Actions</h5>
                            <button class="btn btn-primary me-2" onclick="showSection('test-section')">
                                <i class="fas fa-arrow-right me-1"></i>Go to Test Section
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="test-section" class="content-section">
            <h2><i class="fas fa-vial me-2"></i>Test Section (WORKING!)</h2>
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle me-2"></i>Success!</h4>
                <p>If you can see this message, the section switching is working correctly.</p>
                <button class="btn btn-success" onclick="showSection('dashboard-section')">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </button>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5>Navigation Test</h5>
                    <p>Click the buttons above to test section switching. Both JavaScript navigation and onclick events should work.</p>
                    
                    <h6>Debug Console</h6>
                    <div id="debug-log" class="alert alert-info">
                        <small>Check browser console (F12) for any JavaScript errors.</small>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showSection(sectionId, clickedElement = null) {
            console.log('showSection called with:', sectionId);
            
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
                console.log('Hiding section:', section.id);
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
                
                // Update debug log
                const debugLog = document.getElementById('debug-log');
                if (debugLog) {
                    debugLog.innerHTML = `<small>Last action: Switched to section "${sectionId}" at ${new Date().toLocaleTimeString()}</small>`;
                }
            } else {
                console.error('Section not found:', sectionId);
            }
            
            // Add active class to clicked nav link
            if (clickedElement) {
                clickedElement.classList.add('active');
            }
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, initializing...');
            showSection('dashboard-section', document.querySelector('.nav-link.active'));
        });
    </script>
</body>
</html>