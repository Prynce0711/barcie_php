<?php
// Dashboard page (Admin Panel)
session_start();

// TODO: Add authentication check
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">

  <!-- FullCalendar CSS & JS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>

<body>

  <!-- Dark Mode Toggle -->

  <button class="dark-toggle" onclick="toggleDarkMode()">🌙</button>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Hotel Admin</h2>
    <a href="#" class="nav-link active" data-section="dashboard">Dashboard</a>
    <a href="#" class="nav-link" data-section="rooms">Rooms & Facilities</a>
    <a href="#" class="nav-link" data-section="bookings">Bookings</a>
    <a href="#" class="nav-link" data-section="payments">Payments</a>
    <a href="#" class="nav-link" data-section="users">Users</a>
    <a href="#" class="nav-link" data-section="reports">Reports</a>
    <a href="#" class="nav-link" data-section="communication">Communication</a>
    <a href="#" class="nav-link" data-section="others">Other Features</a>
    <a href="index.php" style="color: #e74c3c;">Logout</a>
  </div>


  <!-- Main Content -->
  <div class="main-content">

    <!-- Header -->
    <header>
      <h1>Hotel Management Dashboard</h1>
      <p>Welcome back, Admin!</p>
    </header>

    <!-- Dashboard Section -->
    <section id="dashboard" class="content-section active">
      <h2>Dashboard Overview</h2>
      <div class="overview-grid">

        <!-- Left Side -->
        <div class="overview-left">
          <div class="card">
            <h3>Quick Stats</h3>
            <p>Total Rooms: 20</p>
            <p>Active Bookings: 5</p>
            <p>Pending Approvals: 3</p>
          </div>

          <div class="card booking-summary">
            <h3>Recent Activity</h3>
            <ul>
              <li>John Doe booked Room 101</li>
              <li>Maria checked out Room 202</li>
              <li>2 Pending feedbacks</li>
            </ul>
          </div>
        </div>

        <!-- Right Side (Mini Calendar) -->
        <div class="overview-right">
          <div class="calendar-container">
            <h3>Availability Calendar</h3>
            <div id="miniCalendar"></div>
          </div>
        </div>

      </div>
    </section>


    <!-- Rooms & Facilities -->
    <section id="rooms" class="content-section">
      <h2>Rooms & Facilities</h2>
      <form>
        <label>Room Name:</label>
        <input type="text" placeholder="Enter room name">
        <label>Price:</label>
        <input type="number" placeholder="Enter price">
        <button type="submit" class="add">Add Room</button>
      </form>
      <table>
        <tr>
          <th>Room</th>
          <th>Price</th>
          <th>Status</th>
        </tr>
        <tr>
          <td>Deluxe Suite</td>
          <td>$200</td>
          <td>Available</td>
        </tr>
      </table>
    </section>

    <!-- Bookings -->


    <section id="bookings" class="content-section">
      <h2>Bookings</h2>
      <table>
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Details</th>
          <th>Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>

        <?php
        include __DIR__ . '/database/db_connect.php';

        $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['type']}</td>
                <td>{$row['details']}</td>
                <td>{$row['created_at']}</td>
                <td>{$row['status']}</td>
                <td>
                    <form method='POST' action='database/save_booking.php' style='display:inline;'>
                        <input type='hidden' name='booking_id' value='{$row['id']}'>
                        <button type='submit' name='action' value='approve' class='approve'>Approve</button>
                    </form>
                    <form method='POST' action='database/save_booking.php' style='display:inline;'>
                        <input type='hidden' name='booking_id' value='{$row['id']}'>
                        <button type='submit' name='action' value='reject' class='reject'>Reject</button>
                    </form>
                </td>
              </tr>";
        }
        ?>
      </table>
    </section>




    <!-- Payments -->
    <section id="payments" class="content-section">
      <h2>Payments</h2>
      <table>
        <tr>
          <th>Guest</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <tr>
          <td>Mary Smith</td>
          <td>$300</td>
          <td>Pending</td>
          <td><button class="approve">Mark Paid</button></td>
        </tr>
      </table>
    </section>

    <!-- Users -->
    <section id="users" class="content-section">
      <h2>User Management</h2>
      <form>
        <label>Username:</label>
        <input type="text" placeholder="Enter username">
        <label>Email:</label>
        <input type="email" placeholder="Enter email">
        <button type="submit" class="add">Add User</button>
      </form>
      <table>
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
        </tr>
        <tr>
          <td>admin</td>
          <td>admin@example.com</td>
          <td>Administrator</td>
        </tr>
      </table>
    </section>

    <!-- Reports -->
    <section id="reports" class="content-section">
      <h2>Reports & Analytics</h2>
      <canvas id="reportChart"></canvas>
    </section>

    <!-- Communication -->
    <section id="communication" class="content-section">
      <h2>Communication</h2>
      <form id="feedback">
        <label>Message:</label>
        <textarea rows="4" placeholder="Enter your message"></textarea>
        <button type="submit" class="add">Send</button>
      </form>
    </section>

    <!-- Other Features -->
    <section id="others" class="content-section">
      <h2>Other Features</h2>
      <p>Manage staff, system settings, and more here.</p>
    </section>
  </div>

  <!-- Footer -->
  <div class="footer">
    <p>&copy; <?php echo date("Y"); ?> Hotel Management System</p>
  </div>

  <script>
    /* Sidebar toggle */
    function toggleSidebar() {
      document.querySelector(".sidebar").classList.toggle("active");
      document.querySelector(".main-content").classList.toggle("active");
    }

    /* Navigation */
    document.querySelectorAll(".nav-link").forEach(link => {
      link.addEventListener("click", e => {
        e.preventDefault();
        document.querySelectorAll(".nav-link").forEach(l => l.classList.remove("active"));
        link.classList.add("active");
        let section = link.dataset.section;
        document.querySelectorAll(".content-section").forEach(sec => sec.classList.remove("active"));
        document.getElementById(section).classList.add("active");
      });
    });

    /* Chart.js Reports */
    const ctx = document.getElementById('reportChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
        datasets: [{
          label: 'Bookings',
          data: [12, 19, 7, 15, 20],
          backgroundColor: '#1abc9c'
        }]
      }
    });




    // Load saved theme
    window.onload = () => {
      if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
      }
    };
  </script>


  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('miniCalendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 400,
        headerToolbar: {
          left: 'prev,next',
          center: 'title',
          right: ''
        },
        events: [
          { title: 'Room 101 - Booked', start: '2025-09-26' },
          { title: 'Room 202 - Checkout', start: '2025-09-28' },
          { title: 'Room 303 - Reserved', start: '2025-10-01' }
        ]
      });
      calendar.render();
    });
  </script>


</body>

</html>