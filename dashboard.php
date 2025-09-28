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
    <h1>Rooms & Facilities Management</h1>
    </header>

    <section id="rooms">
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

      <h2>Current Items</h2>
      <label>Filter Type:
        <input type="radio" name="type_filter" value="room" checked> Room
        <input type="radio" name="type_filter" value="facility"> Facility
      </label>

      <div class="cards-grid" id="cards-grid">
        <?php
        $res = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
        while ($item = $res->fetch_assoc()): ?>
          <div class="card" data-type="<?= $item['item_type'] ?>">
            <?php if ($item['image']): ?><img src="<?= $item['image'] ?>"
                style="width:100%;height:150px;object-fit:cover;"><?php endif; ?>
            <h3><?= $item['name'] ?></h3>
            <?= $item['room_number'] ? "<p>Room Number: " . $item['room_number'] . "</p>" : "" ?>
            <p>Capacity: <?= $item['capacity'] ?>   <?= $item['item_type'] === 'room' ? 'persons' : 'people' ?></p>
            <p>Price: $<?= $item['price'] ?><?= $item['item_type'] === 'room' ? '/night' : '/day' ?></p>
            <p><?= $item['description'] ?></p>

            <!-- Edit / Delete Forms -->
            <form method="POST" enctype="multipart/form-data">
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


<script>
function filterItems() {
const selectedType = document.querySelector('input[name="type_filter"]:checked').value;
document.querySelectorAll('.card').forEach(card => {
card.style.display = card.dataset.type === selectedType ? 'block' : 'none';
});
}
document.querySelectorAll('input[name="type_filter"]').forEach(radio => {
radio.addEventListener('change', filterItems);
});
window.onload = filterItems;
</script>

<script>
async function loadItems(){
const res = await fetch('database/fetch_items.php');
const items = await res.json();
const container = document.getElementById('cards-grid');
container.innerHTML = '';
items.forEach(item=>{
const card=document.createElement('div');
card.classList.add('card');
card.dataset.type=item.item_type;
card.innerHTML=`
${item.image? `<img src="${item.image}" style="width:100%;height:150px;object-fit:cover;">`:''}
<h3>${item.name}</h3>
${item.room_number? `<p>Room Number: ${item.room_number}</p>` : ''}
<p>Capacity: ${item.capacity} ${item.item_type==='room'?'persons':'people'}</p>
<p>Price: $${item.price}${item.item_type==='room'?'/night':'/day'}</p>
<p>${item.description}</p>
`;
container.appendChild(card);
});
filterItems();
}

function filterItems(){
const selectedType=document.querySelector('input[name="type"]:checked').value;
document.querySelectorAll('.card').forEach(card=>{
card.style.display = card.dataset.type === selectedType ? 'block' : 'none';
});
}

document.querySelectorAll('input[name="type"]').forEach(radio=>{
radio.addEventListener('change', filterItems);
});
window.onload=loadItems;
</script>

</body>

</html>