# API Directory

This directory contains all backend API endpoints for the BarCIE International Center system. These endpoints handle data operations, business logic, and communication between the frontend and database.

## 📁 API Structure

All API endpoints follow RESTful principles and return JSON responses.

```
api/
├── Admin Management
├── Booking Operations
├── Room Management
├── Reports & Analytics
├── Content Management
└── Utilities
```

## 🔐 Admin Management APIs

### `admin_management.php`
**Purpose**: Basic admin user management operations

**Methods**: 
- `POST` - Create new admin user
- `PUT` - Update admin user details
- `DELETE` - Remove admin user
- `GET` - Fetch admin user list

**Request Example**:
```json
{
  "action": "create",
  "username": "admin123",
  "password": "secure_password",
  "role": "admin"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Admin user created successfully",
  "admin_id": 123
}
```

---

### `admin_management_enhanced.php`
**Purpose**: Enhanced admin management with additional features

**Features**:
- Role-based permissions
- Activity logging
- Bulk operations
- Advanced filtering

---

### `admin_heartbeat.php`
**Purpose**: Track admin online status and activity

**Method**: `POST`

**Usage**: Called periodically to update admin's last active timestamp

**Response**:
```json
{
  "success": true,
  "timestamp": "2025-12-19 10:30:45"
}
```

---

## 📅 Booking Operations

### `availability.php`
**Purpose**: Check room availability for specified dates

**Method**: `GET`

**Parameters**:
- `check_in` - Check-in date (YYYY-MM-DD)
- `check_out` - Check-out date (YYYY-MM-DD)
- `room_type` - (Optional) Filter by room type

**Response**:
```json
{
  "success": true,
  "available_rooms": [
    {
      "room_id": 1,
      "room_type": "Standard Room",
      "capacity": 2,
      "price": 1500.00
    }
  ]
}
```

---

### `available_count.php`
**Purpose**: Get count of available rooms per type

**Method**: `GET`

**Response**:
```json
{
  "success": true,
  "counts": {
    "standard": 5,
    "deluxe": 3,
    "suite": 2
  }
}
```

---

### `cancel_booking.php`
**Purpose**: Cancel an existing booking

**Method**: `POST`

**Request**:
```json
{
  "booking_id": 123,
  "reason": "Change of plans"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "refund_amount": 1500.00
}
```

---

### `get_booking_details.php`
**Purpose**: Retrieve detailed information about a specific booking

**Method**: `GET`

**Parameters**: `booking_id`

**Response**:
```json
{
  "success": true,
  "booking": {
    "booking_id": 123,
    "guest_name": "John Doe",
    "room_type": "Deluxe Room",
    "check_in": "2025-12-20",
    "check_out": "2025-12-22",
    "total_amount": 3000.00,
    "status": "confirmed"
  }
}
```

---

## 🏨 Room Management

### `room_availability.php`
**Purpose**: Comprehensive room availability calendar data

**Method**: `GET`

**Parameters**:
- `start_date` - Start of date range
- `end_date` - End of date range

**Response**:
```json
{
  "success": true,
  "calendar": [
    {
      "date": "2025-12-20",
      "rooms": {
        "standard": {"total": 10, "available": 7},
        "deluxe": {"total": 5, "available": 3}
      }
    }
  ]
}
```

---

## 🍽️ Items Management

### `items.php`
**Purpose**: Manage catering items and services

**Methods**:
- `GET` - Fetch all items or specific item
- `POST` - Create new item
- `PUT` - Update existing item
- `DELETE` - Remove item

**Request (Create)**:
```json
{
  "action": "create",
  "name": "Wedding Package",
  "description": "Complete wedding catering",
  "price": 25000.00,
  "category": "catering",
  "image": "base64_image_data"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Item created successfully",
  "item_id": 45
}
```

---

## 📰 Content Management

### `news.php`
**Purpose**: Manage news and announcements

**Methods**:
- `GET` - Fetch news articles
- `POST` - Create new article
- `PUT` - Update article
- `DELETE` - Remove article

**Request (Create)**:
```json
{
  "action": "create",
  "title": "New Year Promotion",
  "content": "Get 20% off on all bookings...",
  "image_url": "assets/images/news/promo.jpg",
  "author": "Admin"
}
```

---

## 📊 Reports & Analytics

### `reports_data.php`
**Purpose**: Generate comprehensive reports and analytics

**Method**: `GET`

**Parameters**:
- `type` - Report type (bookings, revenue, occupancy)
- `start_date` - Report start date
- `end_date` - Report end date
- `format` - Output format (json, csv)

**Response**:
```json
{
  "success": true,
  "report": {
    "total_bookings": 150,
    "total_revenue": 450000.00,
    "occupancy_rate": 85.5,
    "average_stay": 2.5
  }
}
```

---

### `reports_data_mysqli.php`
**Purpose**: Reports using MySQLi implementation (alternative version)

**Features**: Same as `reports_data.php` but using MySQLi instead of PDO

---

### `export_report_pdf.php`
**Purpose**: Export reports as PDF documents

**Method**: `POST`

**Request**:
```json
{
  "report_type": "monthly_summary",
  "start_date": "2025-12-01",
  "end_date": "2025-12-31"
}
```

**Response**: PDF file download

---

### `export_report_excel.php`
**Purpose**: Export reports as Excel spreadsheets

**Method**: `POST`

**Response**: XLSX file download

---

## 🧾 Document Generation

### `generate_booking_pdf.php`
**Purpose**: Generate booking confirmation PDF

**Method**: `POST`

**Parameters**: `booking_id`

**Response**: PDF document with booking details

---

### `generate_elegant_bookings_pdf.php`
**Purpose**: Generate elegant formatted booking report

**Method**: `POST`

**Response**: Professionally formatted PDF report

---

### `receipt.php`
**Purpose**: Generate payment receipt

**Method**: `GET`

**Parameters**: `booking_id`

**Response**: Receipt PDF or HTML

---

## 🤖 AI & Automation

### `chatbot_answer.php`
**Purpose**: Process chatbot queries and return AI-generated responses

**Method**: `POST`

**Request**:
```json
{
  "question": "What are your room rates?",
  "context": "booking"
}
```

**Response**:
```json
{
  "success": true,
  "answer": "Our room rates start from ₱1,500 per night...",
  "suggestions": ["Check availability", "View room types"]
}
```

---

## 📝 Activity Tracking

### `recent_activities.php`
**Purpose**: Log and retrieve recent system activities

**Methods**:
- `GET` - Fetch recent activities
- `POST` - Log new activity

**Response (GET)**:
```json
{
  "success": true,
  "activities": [
    {
      "id": 1,
      "user": "admin",
      "action": "Created booking",
      "timestamp": "2025-12-19 10:30:00"
    }
  ]
}
```

---

## 📤 File Operations

### `upload_chunk.php`
**Purpose**: Handle large file uploads using chunked upload

**Method**: `POST`

**Parameters**:
- `chunk` - File chunk data
- `chunk_number` - Current chunk index
- `total_chunks` - Total number of chunks
- `filename` - Original filename

**Response**:
```json
{
  "success": true,
  "uploaded": true,
  "file_path": "uploads/items/image_123.jpg"
}
```

---

## 🔧 Utilities

### `bootstrap.php`
**Purpose**: Initialize application environment and dependencies

**Usage**: Include at the start of API endpoints

```php
require_once 'api/bootstrap.php';
```

---

### `health.php`
**Purpose**: System health check endpoint

**Method**: `GET`

**Response**:
```json
{
  "status": "healthy",
  "database": "connected",
  "version": "1.0.0",
  "timestamp": "2025-12-19T10:30:00Z"
}
```

---

## 🛡️ Security

### Authentication
All API endpoints (except public ones) require authentication:

```php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
```

### CSRF Protection
Include CSRF token in requests:

```javascript
fetch('api/endpoint.php', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(data)
});
```

### Input Validation
All inputs are sanitized and validated:

```php
$input = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING);
```

---

## 📋 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERR_001"
}
```

---

## 🧪 Testing APIs

### Using cURL

```bash
# GET request
curl -X GET "http://localhost/barcie_php/api/availability.php?check_in=2025-12-20&check_out=2025-12-22"

# POST request
curl -X POST "http://localhost/barcie_php/api/cancel_booking.php" \
  -H "Content-Type: application/json" \
  -d '{"booking_id": 123}'
```

### Using Postman

1. Import the API collection
2. Set environment variables
3. Test endpoints individually
4. View formatted responses

### Using JavaScript

```javascript
// GET request
fetch('api/availability.php?check_in=2025-12-20&check_out=2025-12-22')
  .then(response => response.json())
  .then(data => console.log(data));

// POST request
fetch('api/cancel_booking.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({booking_id: 123})
})
  .then(response => response.json())
  .then(data => console.log(data));
```

---

## 📝 Creating New APIs

### Template Structure

```php
<?php
// api/your_endpoint.php

// Bootstrap and dependencies
require_once 'bootstrap.php';
require_once '../database/db_connect.php';

// Set JSON response header
header('Content-Type: application/json');

// Authentication check (if required)
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Handle GET request
            break;
        case 'POST':
            // Handle POST request
            $data = json_decode(file_get_contents('php://input'), true);
            break;
        case 'PUT':
            // Handle PUT request
            break;
        case 'DELETE':
            // Handle DELETE request
            break;
        default:
            throw new Exception('Method not allowed');
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    // Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
```

---

## 🚀 Performance Optimization

- Use prepared statements to prevent SQL injection
- Cache frequently accessed data
- Implement pagination for large datasets
- Use database indexes effectively
- Compress large responses
- Enable HTTP caching headers

---

## 📊 Monitoring & Logging

- Log all API requests and responses
- Monitor response times
- Track error rates
- Set up alerts for critical failures
- Review logs regularly

---

For questions or contributions, please refer to the main project README.md
