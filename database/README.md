# Database Directory

This directory contains all database-related utilities, configurations, and modules for the BarCIE International Center system. It handles database connections, authentication, data fetching, and business logic modules.

## 📁 Directory Structure

```
database/
├── Configuration Files
├── Authentication & Security
├── Data Fetching Utilities
└── modules/           # Business logic modules
```

## ⚙️ Configuration Files

### `config.php`
**Purpose**: Central database configuration file

**Contains**:
- Database credentials
- Server settings
- Environment-specific configurations
- Application constants

**Usage**:
```php
require_once 'database/config.php';
// Access: DB_HOST, DB_NAME, DB_USER, DB_PASS
```

**Configuration Variables**:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'barcie_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

---

### `db_connect.php`
**Purpose**: Database connection handler

**Features**:
- Establishes MySQLi connection
- Connection error handling
- Character set configuration
- Connection pooling

**Usage**:
```php
require_once 'database/db_connect.php';
// Use $conn for database queries
```

**Connection Object**:
```php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
```

---

### `mail_config.php`
**Purpose**: Email configuration using PHPMailer

**Features**:
- SMTP configuration
- Email templates
- Sender settings
- Error handling

**Configuration**:
```php
// SMTP Settings
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
```

**Usage**:
```php
require_once 'database/mail_config.php';
$mail = getMailer();
$mail->addAddress('customer@example.com');
$mail->Subject = 'Booking Confirmation';
$mail->Body = 'Your booking has been confirmed...';
$mail->send();
```

---

## 🔐 Authentication & Security

### `admin_login.php`
**Purpose**: Admin user authentication endpoint

**Method**: `POST`

**Process**:
1. Receive username and password
2. Validate input
3. Check credentials against database
4. Create session if valid
5. Return authentication result

**Request**:
```json
{
  "username": "admin",
  "password": "secure_password"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Login successful",
  "admin_id": 1,
  "username": "admin",
  "role": "super_admin"
}
```

**Security Features**:
- Password hashing (bcrypt)
- SQL injection prevention
- Session management
- Login attempt limiting

---

### `user_auth.php`
**Purpose**: General user authentication utilities

**Functions**:
- `authenticate($username, $password)` - Verify credentials
- `createSession($user_data)` - Initialize user session
- `checkSession()` - Verify active session
- `logout()` - Destroy user session
- `sendVerificationEmail($email)` - Send verification emails

**Usage**:
```php
require_once 'database/user_auth.php';

if (authenticate($username, $password)) {
    createSession($user_data);
    header('Location: dashboard.php');
} else {
    echo "Invalid credentials";
}
```

---

### `csrf_protection.php`
**Purpose**: CSRF (Cross-Site Request Forgery) protection

**Functions**:
- `generateCSRFToken()` - Create unique token
- `validateCSRFToken($token)` - Verify token validity
- `embedCSRFField()` - Add token to forms

**Usage**:
```php
// Generate token
session_start();
require_once 'database/csrf_protection.php';
$token = generateCSRFToken();

// In form
<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

// Validate on submission
if (!validateCSRFToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

---

### `role_check.php`
**Purpose**: Role-based access control

**Functions**:
- `checkRole($required_role)` - Verify user role
- `hasPermission($permission)` - Check specific permission
- `getAdminRoles()` - Fetch available roles

**Roles**:
- `super_admin` - Full system access
- `admin` - Standard admin access
- `manager` - Limited management access
- `staff` - Basic access

**Usage**:
```php
require_once 'database/role_check.php';

if (!checkRole('admin')) {
    http_response_code(403);
    die('Access denied');
}
```

---

## 📊 Data Fetching Utilities

### `fetch_calendar_data.php`
**Purpose**: Fetch booking data for calendar views

**Returns**:
- All bookings within date range
- Room availability status
- Booking conflicts
- Calendar events

**Usage**:
```php
require_once 'database/fetch_calendar_data.php';

$calendar_data = fetchCalendarData('2025-12-01', '2025-12-31');
echo json_encode($calendar_data);
```

**Response Format**:
```json
{
  "events": [
    {
      "id": 1,
      "title": "John Doe - Standard Room",
      "start": "2025-12-20",
      "end": "2025-12-22",
      "color": "#28a745"
    }
  ]
}
```

---

### `fetch_items.php`
**Purpose**: Retrieve catering items and services

**Features**:
- Fetch all items or by category
- Filter by availability
- Include pricing and images
- Sort by various criteria

**Usage**:
```php
require_once 'database/fetch_items.php';

// Fetch all items
$items = fetchAllItems();

// Fetch by category
$catering = fetchItemsByCategory('catering');

// Fetch single item
$item = fetchItemById(5);
```

**Return Structure**:
```php
[
  [
    'id' => 1,
    'name' => 'Wedding Package',
    'description' => 'Complete wedding catering',
    'price' => 25000.00,
    'category' => 'catering',
    'image' => 'uploads/items/wedding.jpg',
    'available' => true
  ]
]
```

---

## 🛠️ Error Handling

### `error_handler.php`
**Purpose**: Centralized error handling and logging

**Functions**:
- `handleError($error)` - Process errors
- `logError($message, $level)` - Log to file
- `displayError($message)` - Show user-friendly errors
- `sendErrorAlert($error)` - Notify admins

**Error Levels**:
- `ERROR` - Critical errors
- `WARNING` - Non-critical issues
- `NOTICE` - Informational messages
- `DEBUG` - Development information

**Usage**:
```php
require_once 'database/error_handler.php';

try {
    // Code that might throw exception
    $result = performDatabaseOperation();
} catch (Exception $e) {
    logError($e->getMessage(), 'ERROR');
    displayError('An error occurred. Please try again.');
}
```

**Log Format**:
```
[2025-12-19 10:30:45] ERROR: Database connection failed - Connection timeout
[2025-12-19 10:31:12] WARNING: Invalid input detected - User ID: 123
```

---

## 📦 Modules Directory

Business logic modules for specific features.

### `modules/AuthModule.php`
**Purpose**: Advanced authentication module

**Class**: `AuthModule`

**Methods**:
- `login($username, $password)` - User login
- `register($user_data)` - User registration
- `resetPassword($email)` - Password reset
- `verifyEmail($token)` - Email verification
- `updatePassword($user_id, $new_password)` - Change password
- `generateToken($user_id)` - Create auth token
- `validateToken($token)` - Verify token

**Usage**:
```php
require_once 'database/modules/AuthModule.php';

$auth = new AuthModule();
$result = $auth->login($username, $password);

if ($result['success']) {
    $_SESSION['user_id'] = $result['user_id'];
    header('Location: dashboard.php');
}
```

---

### `modules/BookingModule.php`
**Purpose**: Booking management business logic

**Class**: `BookingModule`

**Methods**:
- `createBooking($booking_data)` - Create new booking
- `updateBooking($booking_id, $data)` - Update booking
- `cancelBooking($booking_id)` - Cancel booking
- `getBooking($booking_id)` - Fetch booking details
- `checkAvailability($check_in, $check_out, $room_type)` - Check availability
- `calculateTotal($booking_data)` - Calculate booking total
- `applyDiscount($booking_id, $discount_code)` - Apply promo code
- `confirmPayment($booking_id, $payment_data)` - Confirm payment

**Usage**:
```php
require_once 'database/modules/BookingModule.php';

$bookingModule = new BookingModule();

$booking_data = [
    'guest_name' => 'John Doe',
    'room_type' => 'Deluxe',
    'check_in' => '2025-12-20',
    'check_out' => '2025-12-22',
    'guests' => 2
];

$result = $bookingModule->createBooking($booking_data);

if ($result['success']) {
    echo "Booking ID: " . $result['booking_id'];
}
```

---

### `modules/permissions_manager.php`
**Purpose**: Manage user permissions and access control

**Functions**:
- `grantPermission($user_id, $permission)` - Grant permission
- `revokePermission($user_id, $permission)` - Remove permission
- `hasPermission($user_id, $permission)` - Check permission
- `getUserPermissions($user_id)` - Get all user permissions
- `getRolePermissions($role)` - Get role-based permissions

**Permissions**:
- `view_bookings` - View booking list
- `create_bookings` - Create new bookings
- `edit_bookings` - Modify bookings
- `delete_bookings` - Remove bookings
- `manage_users` - User management
- `manage_rooms` - Room management
- `view_reports` - Access reports
- `export_data` - Export functionality

**Usage**:
```php
require_once 'database/modules/permissions_manager.php';

if (hasPermission($_SESSION['user_id'], 'manage_rooms')) {
    // Show room management interface
} else {
    echo "Access denied";
}
```

---

### `modules/audit_trail.php`
**Purpose**: Track and log user activities

**Functions**:
- `logActivity($user_id, $action, $details)` - Log activity
- `getActivityLog($filters)` - Retrieve logs
- `getUserActivity($user_id)` - Get user-specific logs
- `generateAuditReport($start_date, $end_date)` - Generate report

**Activity Types**:
- `login` - User login
- `logout` - User logout
- `create` - Record creation
- `update` - Record modification
- `delete` - Record deletion
- `export` - Data export
- `view` - Data viewing

**Usage**:
```php
require_once 'database/modules/audit_trail.php';

// Log activity
logActivity($_SESSION['admin_id'], 'create', [
    'type' => 'booking',
    'booking_id' => 123,
    'guest_name' => 'John Doe'
]);

// Get activity log
$activities = getActivityLog([
    'user_id' => $_SESSION['admin_id'],
    'action' => 'create',
    'limit' => 50
]);
```

**Log Structure**:
```php
[
  [
    'id' => 1,
    'user_id' => 5,
    'username' => 'admin',
    'action' => 'create',
    'entity_type' => 'booking',
    'entity_id' => 123,
    'details' => 'Created booking for John Doe',
    'ip_address' => '192.168.1.100',
    'timestamp' => '2025-12-19 10:30:45'
  ]
]
```

---

## 🔧 Database Schema Management

### Creating Connections

**MySQLi Connection**:
```php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');
```

**PDO Connection**:
```php
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
```

### Query Best Practices

**Prepared Statements (MySQLi)**:
```php
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
```

**Prepared Statements (PDO)**:
```php
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :id");
$stmt->execute(['id' => $booking_id]);
$booking = $stmt->fetch();
```

---

## 🔒 Security Best Practices

### Input Sanitization
```php
// Sanitize string input
$clean_input = filter_var($input, FILTER_SANITIZE_STRING);

// Sanitize email
$clean_email = filter_var($email, FILTER_SANITIZE_EMAIL);

// Validate integer
$id = filter_var($input, FILTER_VALIDATE_INT);
```

### Password Hashing
```php
// Hash password
$hashed = password_hash($password, PASSWORD_BCRYPT);

// Verify password
if (password_verify($input_password, $hashed_password)) {
    // Password is correct
}
```

### SQL Injection Prevention
- Always use prepared statements
- Never concatenate user input into queries
- Validate and sanitize all inputs
- Use parameterized queries

---

## 📊 Database Backup

### Manual Backup
```bash
mysqldump -u root -p barcie_db > backup_$(date +%Y%m%d).sql
```

### Automated Backup Script
```php
// Create backup
exec('mysqldump -u ' . DB_USER . ' -p' . DB_PASS . ' ' . DB_NAME . ' > backup.sql');
```

### Restore Database
```bash
mysql -u root -p barcie_db < backup.sql
```

---

## 🧪 Testing Database Operations

### Test Connection
```php
require_once 'database/db_connect.php';

if ($conn->ping()) {
    echo "Database connection is alive!";
} else {
    echo "Connection failed: " . $conn->error;
}
```

### Test Queries
```php
// Test simple query
$result = $conn->query("SELECT 1");
if ($result) {
    echo "Query executed successfully";
}
```

---

## 📝 Common Database Operations

### INSERT
```php
$stmt = $conn->prepare("INSERT INTO bookings (guest_name, room_type, check_in) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $room, $date);
$stmt->execute();
$new_id = $conn->insert_id;
```

### UPDATE
```php
$stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();
```

### DELETE
```php
$stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
```

### SELECT
```php
$stmt = $conn->prepare("SELECT * FROM bookings WHERE status = ?");
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // Process row
}
```

---

## 🚀 Performance Optimization

- Use indexes on frequently queried columns
- Optimize complex queries with EXPLAIN
- Cache frequently accessed data
- Use connection pooling
- Implement query result caching
- Regular database maintenance (OPTIMIZE TABLE)

---

## 📋 Maintenance Tasks

- Regular backups (daily/weekly)
- Monitor slow queries
- Update statistics
- Check and repair tables
- Clean up old logs
- Archive historical data

---

For questions or contributions, please refer to the main project README.md
