<?php
// Dashboard page (Admin Panel)
session_start();

// TODO: Add authentication check
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php");
//     exit;
// }


include __DIR__ . '/database/db_connect.php';


// Handle Add/Update/Delete
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // DELETE
    if ($action === "delete" && isset($_POST['id'])) {
      $id = intval($_POST['id']);
      // Delete image if exists
      $stmt = $conn->prepare("SELECT image FROM items WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->bind_result($img);
      $stmt->fetch();
      $stmt->close();
      if ($img && file_exists($img))
        unlink($img);

      $stmt = $conn->prepare("DELETE FROM items WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#rooms");
      exit;
    }

    // UPDATE
    if ($action === "update" && isset($_POST['id'])) {
      $id = intval($_POST['id']);
      $name = $_POST['name'];
      $type = $_POST['item_type'];
      $room_number = $_POST['room_number'] ?: null;
      $description = $_POST['description'] ?: null;
      $capacity = $_POST['capacity'] ?: 0;
      $price = $_POST['price'] ?: 0;

      $image_path = $_POST['old_image'] ?? null;
      if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir))
          mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
          $image_path = $target_file;
          // Delete old image
          if (!empty($_POST['old_image']) && file_exists($_POST['old_image']))
            unlink($_POST['old_image']);
        }
      }

      $stmt = $conn->prepare("UPDATE items SET name=?, item_type=?, room_number=?, description=?, capacity=?, price=?, image=? WHERE id=?");
      $stmt->bind_param("ssssidsi", $name, $type, $room_number, $description, $capacity, $price, $image_path, $id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#rooms");
      exit;
    }
  }

  // ADD NEW
  if (isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $type = $_POST['item_type'];
    $room_number = $_POST['room_number'] ?: null;
    $description = $_POST['description'] ?: null;
    $capacity = $_POST['capacity'] ?: 0;
    $price = $_POST['price'] ?: 0;

    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
      $target_dir = "uploads/";
      if (!file_exists($target_dir))
        mkdir($target_dir, 0777, true);
      $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
      if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
      }
    }

    $stmt = $conn->prepare("INSERT INTO items (name, item_type, room_number, description, capacity, price, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssids", $name, $type, $room_number, $description, $capacity, $price, $image_path);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php#rooms");
    exit;
  }
}
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
    <a href="#" class="nav-link" data-section="dashboard-section">Dashboard</a>
    <a href="#" class="nav-link" data-section="rooms">Rooms & Facilities</a>
    <a href="#" class="nav-link" data-section="bookings">Bookings</a>
    <a href="#" class="nav-link" data-section="payments">Payments</a>
    <a href="#" class="nav-link" data-section="users">Users</a>
    <a href="#" class="nav-link" data-section="communication">Communication</a>

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
    <section id="dashboard-section" class="content-section active">
      <h2 class="dashboard-section-title">Dashboard Overview</h2>

      <div class="dashboard-grid-container">

        <!-- Left Column: Mini Calendar -->
        <div class="dashboard-left">
          <div class="dashboard-card calendar-card">
            <h3 class="card-title"><i class="fa-solid fa-calendar-days"></i> Availability Calendar</h3>
            <div id="dashboardCalendar"></div>
          </div>
        </div>

        <?php


        // Total Rooms (you can keep this static if rooms are fixed)
        $total_rooms = 20;

        // Active Bookings
        $active_bookings_result = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status='approved'");
        $active_bookings = $active_bookings_result->fetch_assoc()['count'];

        // Pending Approvals
        $pending_approvals_result = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status='pending'");
        $pending_approvals = $pending_approvals_result->fetch_assoc()['count'];

        // Recent Activities (latest 5)
        $recent_activity_result = $conn->query("SELECT type, details, created_at FROM bookings ORDER BY created_at DESC LIMIT 5");
        $recent_activities = [];
        while ($row = $recent_activity_result->fetch_assoc()) {
          $recent_activities[] = $row;
        }
        ?>


        <!-- Right Column: Stats & Activity -->
        <div class="dashboard-right">
          <!-- Quick Stats Card -->
          <div class="dashboard-card stats-card">
            <h3 class="card-title"><i class="fa-solid fa-chart-simple"></i> Quick Stats</h3>
            <ul class="stats-list">
              <li>Total Rooms: <strong><?php echo $total_rooms; ?></strong></li>
              <li>Active Bookings: <strong><?php echo $active_bookings; ?></strong></li>
              <li>Pending Approvals: <strong><?php echo $pending_approvals; ?></strong></li>
            </ul>
          </div>

          <!-- Recent Activity Card -->
          <div class="dashboard-card activity-card">
  <h3 class="card-title"><i class="fa-solid fa-clock"></i> Recent Activity</h3>
  
  <table class="activity-table">
    <thead>
      <tr>
        <th>Type</th>
        <th>Details</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recent_activities as $activity): ?>
        <tr>
          <td><?php echo htmlspecialchars($activity['type']); ?></td>
          <td><?php echo htmlspecialchars($activity['details']); ?></td>
          <td><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
        </div>

      </div>
    </section>

    <?php
    // ✅ Load booking events for calendar
    $events = [];
    $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
      $events[] = [
        'id' => $row['id'],
        'title' => "Room " . $row['details'] . " (" . $row['type'] . ")",
        'start' => $row['checkin'],
        'end' => $row['checkout'],
        'status' => $row['status']
      ];
    }
    ?>
    <script>
      const bookingEvents = <?php echo json_encode($events); ?>;
    </script>



    <section id="rooms" class="content-section">
      <!-- ✅ Add Item Form -->
      <h2>Add Room / Facility</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_item" value="1">
        <label>Name:</label><input type="text" name="name" required>
        <label>Type:</label>
        <select name="item_type" required>
          <option value="room">Room</option>
          <option value="facility">Facility</option>
        </select>
        <label>Room Number (optional):</label><input type="text" name="room_number">
        <label>Description:</label><textarea name="description"></textarea>
        <label>Capacity:</label><input type="number" name="capacity" required>
        <label>Price:</label><input type="number" step="0.01" name="price" required>
        <label>Image (optional):</label><input type="file" name="image" accept="image/*">
        <button type="submit">Add Item</button>
      </form>

      <!-- ✅ Existing Items -->
      <h2>Existing Items</h2>
      <label>Filter Type:
        <input type="radio" name="type_filter" value="room" checked> Room
        <input type="radio" name="type_filter" value="facility"> Facility
      </label>

      <div class="cards-grid" id="cards-grid">
        <?php
        $res = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
        while ($item = $res->fetch_assoc()): ?>
          <div class="card" data-type="<?= $item['item_type'] ?>">
            <?php if ($item['image']): ?>
              <img src="<?= $item['image'] ?>" style="width:100%;height:150px;object-fit:cover;">
            <?php endif; ?>

            <h3><?= $item['name'] ?></h3>
            <?= $item['room_number'] ? "<p>Room Number: " . $item['room_number'] . "</p>" : "" ?>
            <p>Capacity: <?= $item['capacity'] ?>   <?= $item['item_type'] === 'room' ? 'persons' : 'people' ?></p>
            <p>Price: $<?= $item['price'] ?><?= $item['item_type'] === 'room' ? '/night' : '/day' ?></p>
            <p><?= $item['description'] ?></p>

            <!-- ✅ Edit Toggle Button -->
            <button type="button" class="toggle-edit">Edit</button>

            <!-- ✅ Edit Form (hidden by default) -->
            <form method="POST" enctype="multipart/form-data" class="edit-form" style="display:none;">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?= $item['id'] ?>">
              <input type="hidden" name="old_image" value="<?= $item['image'] ?>">

              <label>Name: <input type="text" name="name" value="<?= $item['name'] ?>" required></label>
              <label>Type:
                <select name="item_type">
                  <option value="room" <?= $item['item_type'] == 'room' ? 'selected' : '' ?>>Room</option>
                  <option value="facility" <?= $item['item_type'] == 'facility' ? 'selected' : '' ?>>Facility</option>
                </select>
              </label>
              <label>Room Number: <input type="text" name="room_number" value="<?= $item['room_number'] ?>"></label>
              <label>Description: <textarea name="description"><?= $item['description'] ?></textarea></label>
              <label>Capacity: <input type="number" name="capacity" value="<?= $item['capacity'] ?>" required></label>
              <label>Price: <input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" required></label>
              <label>Change Image: <input type="file" name="image"></label>
              <button type="submit">Update</button>
            </form>

            <!-- ✅ Delete Form -->
            <form method="POST">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $item['id'] ?>">
              <button type="submit" style="background:red;color:white;">Delete</button>
            </form>
          </div>
        <?php endwhile; ?>
      </div>
    </section>



    <!-- Bookings -->


    <section id="bookings" class="content-section">

      <!-- Select Booking Type -->
      <label><input type="radio" name="bookingType" value="reservation" checked onchange="toggleBookingForm()">
        Reservation</label>
      <label><input type="radio" name="bookingType" value="pencil" onchange="toggleBookingForm()"> Pencil Booking
        (Function Hall)</label>

      <!-- Reservation Form -->
      <form id="reservationForm" method="POST" action="database/save_booking.php">
        <h3>Reservation Form</h3>
        <label>Official Receipt No.: <input type="text" name="receipt_no" readonly></label>
        <label>Guest Name: <input type="text" name="guest_name" required></label>
        <label>Contact Number: <input type="text" name="contact_number" required></label>
        <label>Email Address: <input type="email" name="email" required></label>
        <label>Check-in Date & Time: <input type="datetime-local" name="checkin" required></label>
        <label>Check-out Date & Time: <input type="datetime-local" name="checkout" required></label>
        <label>Number of Occupants: <input type="number" name="occupants" min="1" required></label>
        <label>Company Affiliation (optional): <input type="text" name="company"></label>
        <label>Company Contact Number (optional): <input type="text" name="company_contact"></label>
        <input type="hidden" name="type" value="reservation">
        <button type="submit">Confirm Reservation</button>
      </form>

      <!-- Pencil Booking Form -->
      <form id="pencilForm" method="POST" action="database/save_booking.php" style="display:none;">
        <h3>Pencil Booking Form (Function Hall)</h3>
        <label>Date of Pencil Booking: <input type="date" name="pencil_date" value="<?php echo date('Y-m-d'); ?>"
            readonly></label>
        <label>Event Type: <input type="text" name="event_type" required></label>
        <label>Function Hall: <input type="text" name="hall" required></label>
        <label>Number of Pax: <input type="number" name="pax" min="1" required></label>
        <label>Time of Event (From): <input type="time" name="time_from" required></label>
        <label>Time of Event (To): <input type="time" name="time_to" required></label>
        <label>Food Provider/Caterer: <input type="text" name="caterer" required></label>
        <label>Contact Person: <input type="text" name="contact_person" required></label>
        <label>Contact Number: <input type="text" name="contact_number" required></label>
        <label>Company Affiliation (optional): <input type="text" name="company"></label>
        <label>Company Number (optional): <input type="text" name="company_number"></label>
        <input type="hidden" name="type" value="pencil">
        <button type="submit" onclick="return pencilReminder()">Submit Pencil Booking</button>
      </form>



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

      <!-- ✅ Table of Registered Users -->
      <table>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>

        <?php

        $sql = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= $row['created_at'] ?></td>
              <td>
                <!-- ✅ Inline Edit Form -->
                <form method="post" action="database/user_auth.php" style="display:inline-block;">
                  <input type="hidden" name="action" value="edit_user">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>
                  <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                  <button type="submit" class="action-btn edit">Save</button>
                </form>

                <!-- ✅ Delete Form -->
                <form method="post" action="database/user_auth.php" style="display:inline-block;"
                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" class="action-btn delete">Delete</button>
                </form>
              </td>
            </tr>
            <?php
          endwhile;
        else:
          ?>
          <tr>
            <td colspan="5">No users found.</td>
          </tr>
        <?php endif; ?>
      </table>
    </section>



    <!-- Communication -->
    <section id="communication" class="content-section">
      <h2>Communication</h2>

      <!-- Chat messages -->
      <div id="chat-box" class="chat-box">
        <div id="messages"></div>
      </div>

      <!-- Chat input -->
      <form id="chat-form">
        <input type="text" id="chat-input" placeholder="Type a message..." required />
        <button type="submit" class="add">Send</button>
      </form>

      <!-- Call controls -->
      <div class="call-controls">
        <button id="voice-call">📞 Voice Call</button>
        <button id="video-call">🎥 Video Call</button>
        <button id="end-call" style="display:none;">❌ End Call</button>
      </div>

      <!-- Video area -->
      <div class="video-container" style="display:none;">
        <video id="localVideo" autoplay muted playsinline></video>
        <video id="remoteVideo" autoplay playsinline></video>
      </div>
    </section>



    <!-- Footer -->
    <div class="footer">
      <p>&copy; <?php echo date("Y"); ?> Hotel Management System</p>
    </div>


    <script>

      document.addEventListener("DOMContentLoaded", () => {
        const navLinks = document.querySelectorAll(".nav-link");
        const sections = document.querySelectorAll(".content-section");

        // Load last active section from localStorage
        let lastSectionId = localStorage.getItem("activeSection") || "dashboard-section";

        function showSection(sectionId) {
          // Remove 'active' from all links
          navLinks.forEach(l => l.classList.remove("active"));
          // Remove 'active' from all sections
          sections.forEach(sec => sec.classList.remove("active"));

          // Add 'active' to selected link
          const activeLink = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
          if (activeLink) activeLink.classList.add("active");

          // Show selected section
          const section = document.getElementById(sectionId);
          if (section) section.classList.add("active");

          // Save current section to localStorage
          localStorage.setItem("activeSection", sectionId);
        }

        // Initialize the page with last active section
        showSection(lastSectionId);

        // Add click listeners
        navLinks.forEach(link => {
          link.addEventListener("click", e => {
            e.preventDefault();
            const sectionId = link.dataset.section;
            showSection(sectionId);
          });
        });
      });

      /* Navigation */



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

    </script>

    <!-- ✅ FullCalendar Initialization -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('dashboardCalendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          height: 300,
          headerToolbar: { left: 'prev,next', center: 'title', right: '' },
          events: bookingEvents.map(event => ({
            id: event.id,
            title: `${event.title} | ${event.status}`,
            start: event.start,
            end: event.end,
            color:
              event.status === 'approved' ? 'green' :
                event.status === 'pending' ? 'orange' : 'red'
          })),
          eventClick: function (info) {
            alert(`Booking ID: ${info.event.id}\nDetails: ${info.event.title}\nStart: ${info.event.start.toLocaleString()}\nEnd: ${info.event.end ? info.event.end.toLocaleString() : "N/A"}`);
          }
        });

        calendar.render();
      });

    </script>

    <!-- ✅ Dark Mode Toggle Script -->
    <script>
      function toggleDarkMode() {
        document.body.classList.toggle("dark-mode");

        // Save preference
        if (document.body.classList.contains("dark-mode")) {
          localStorage.setItem("theme", "dark");
        } else {
          localStorage.setItem("theme", "light");
        }
      }

      // Load saved theme on page load
      window.addEventListener("DOMContentLoaded", () => {
        if (localStorage.getItem("theme") === "dark") {
          document.body.classList.add("dark-mode");
        }
      });
    </script>

    <!-- ✅ Script to load items dynamically -->

    <script>
      async function loadItems() {
        const res = await fetch('database/fetch_items.php');
        const items = await res.json();
        const container = document.getElementById('cards-grid');
        container.innerHTML = '';
        items.forEach(item => {
          const card = document.createElement('div');
          card.classList.add('card');
          card.dataset.type = item.item_type;
          card.innerHTML = `
${item.image ? `<img src="${item.image}" style="width:100%;height:150px;object-fit:cover;">` : ''}
<h3>${item.name}</h3>
${item.room_number ? `<p>Room Number: ${item.room_number}</p>` : ''}
<p>Capacity: ${item.capacity} ${item.item_type === 'room' ? 'persons' : 'people'}</p>
<p>Price: $${item.price}${item.item_type === 'room' ? '/night' : '/day'}</p>
<p>${item.description}</p>
`;
          container.appendChild(card);
        });
        filterItems();
      }

    </script>

    <!-- ✅ Script for toggling edit form -->
    <script>
document.addEventListener("DOMContentLoaded", () => {
  /* ---------------- Booking Type Toggle ---------------- */
  function toggleBookingForm() {
    const selectedType = document.querySelector('input[name="bookingType"]:checked').value;
    const reservationForm = document.getElementById("reservationForm");
    const pencilForm = document.getElementById("pencilForm");

    if (selectedType === "reservation") {
      reservationForm.style.display = "block";
      pencilForm.style.display = "none";
    } else {
      reservationForm.style.display = "none";
      pencilForm.style.display = "block";
    }
  }

  // Init booking form display
  toggleBookingForm();

  // Attach listeners
  document.querySelectorAll('input[name="bookingType"]').forEach(radio => {
    radio.addEventListener("change", toggleBookingForm);
  });


  /* ---------------- Item Filter Toggle ---------------- */
  function filterItems() {
    const selectedType = document.querySelector('input[name="type_filter"]:checked').value;
    document.querySelectorAll(".card").forEach(card => {
      card.style.display = (card.dataset.type === selectedType) ? "block" : "none";
    });
  }

  // Init filtering
  filterItems();

  // Attach listeners
  document.querySelectorAll('input[name="type_filter"]').forEach(radio => {
    radio.addEventListener("change", filterItems);
  });
});
</script>


    <script src="/socket.io/socket.io.js"></script>
    <script>
      const socket = io(); // connect to server

      // ---- Chat ----
      const chatForm = document.getElementById("chat-form");
      const chatInput = document.getElementById("chat-input");
      const messagesDiv = document.getElementById("messages");

      chatForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const msg = chatInput.value;
        socket.emit("chatMessage", msg); // send to server
        chatInput.value = "";
      });

      socket.on("chatMessage", (msg) => {
        const p = document.createElement("p");
        p.textContent = msg;
        messagesDiv.appendChild(p);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
      });

      // ---- WebRTC (Voice/Video Call) ----
      let localStream, peerConnection;
      const config = { iceServers: [{ urls: "stun:stun.l.google.com:19302" }] };

      const voiceBtn = document.getElementById("voice-call");
      const videoBtn = document.getElementById("video-call");
      const endBtn = document.getElementById("end-call");
      const localVideo = document.getElementById("localVideo");
      const remoteVideo = document.getElementById("remoteVideo");
      const videoContainer = document.querySelector(".video-container");

      async function startCall(video = false) {
        localStream = await navigator.mediaDevices.getUserMedia({ video, audio: true });
        localVideo.srcObject = localStream;
        videoContainer.style.display = "flex";
        endBtn.style.display = "inline-block";

        peerConnection = new RTCPeerConnection(config);
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

        peerConnection.ontrack = (event) => {
          remoteVideo.srcObject = event.streams[0];
        };

        peerConnection.onicecandidate = (event) => {
          if (event.candidate) socket.emit("candidate", event.candidate);
        };

        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        socket.emit("offer", offer);
      }

      voiceBtn.onclick = () => startCall(false);
      videoBtn.onclick = () => startCall(true);
      endBtn.onclick = () => {
        peerConnection.close();
        localStream.getTracks().forEach(track => track.stop());
        videoContainer.style.display = "none";
        endBtn.style.display = "none";
      };

      socket.on("offer", async (offer) => {
        peerConnection = new RTCPeerConnection(config);
        peerConnection.ontrack = (event) => remoteVideo.srcObject = event.streams[0];
        peerConnection.onicecandidate = (event) => {
          if (event.candidate) socket.emit("candidate", event.candidate);
        };

        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        localVideo.srcObject = localStream;

        await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        socket.emit("answer", answer);
      });

      socket.on("answer", (answer) => {
        peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
      });

      socket.on("candidate", (candidate) => {
        peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
      });
    </script>


</body>

</html>