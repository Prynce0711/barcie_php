# Cron Jobs Directory

This directory contains automated task scripts that run periodically to perform system maintenance and automated operations for the BarCIE International Center booking system.

## 📁 Directory Contents

```
cron/
├── auto_checkin_checkout.php    # Automated check-in and check-out
└── auto_checkout.php             # Automated checkout only
```

## 🤖 Automated Scripts

### `auto_checkin_checkout.php`
**Purpose**: Automatically process check-ins and check-outs based on booking dates

**What it does**:
1. Checks all bookings scheduled for today
2. Automatically checks in guests whose check-in date is today
3. Automatically checks out guests whose check-out date is today
4. Updates booking status in the database
5. Sends confirmation emails to guests
6. Logs all automated actions

**Execution Frequency**: Every hour (recommended)

**Process Flow**:
```
1. Fetch all bookings with status 'confirmed'
2. Check if check-in date matches today
   → If yes: Update status to 'checked_in'
   → Send check-in notification
3. Check if check-out date matches today
   → If yes: Update status to 'checked_out'
   → Send check-out notification
4. Log activities for audit trail
```

**Database Updates**:
```sql
-- Check-in
UPDATE bookings 
SET status = 'checked_in', 
    checked_in_at = NOW() 
WHERE check_in_date = CURDATE() 
  AND status = 'confirmed';

-- Check-out
UPDATE bookings 
SET status = 'checked_out', 
    checked_out_at = NOW() 
WHERE check_out_date = CURDATE() 
  AND status = 'checked_in';
```

**Manual Execution**:
```bash
php c:/xampp/htdocs/barcie_php/cron/auto_checkin_checkout.php
```

**Expected Output**:
```
[2025-12-19 10:00:00] Auto Check-in/Check-out Process Started
[2025-12-19 10:00:01] Checked in: Booking #123 - John Doe
[2025-12-19 10:00:02] Checked out: Booking #120 - Jane Smith
[2025-12-19 10:00:03] Total processed: 2 bookings
[2025-12-19 10:00:03] Process completed successfully
```

---

### `auto_checkout.php`
**Purpose**: Specifically handles automated guest checkout process

**What it does**:
1. Identifies bookings with checkout date = today
2. Updates booking status to 'checked_out'
3. Releases room inventory
4. Generates final invoice/receipt
5. Sends checkout confirmation email
6. Archives booking data

**Execution Frequency**: Once daily at midnight (recommended)

**Process Flow**:
```
1. Fetch bookings where check_out_date = TODAY
2. For each booking:
   a. Verify current status is 'checked_in'
   b. Calculate final charges (if any late charges)
   c. Update status to 'checked_out'
   d. Mark room as available
   e. Generate receipt/invoice
   f. Send email to guest
   g. Log activity
3. Generate daily checkout report
```

**Additional Features**:
- Late checkout handling
- Damage fee processing
- Final payment settlement
- Room availability update
- Statistics tracking

**Manual Execution**:
```bash
php c:/xampp/htdocs/barcie_php/cron/auto_checkout.php
```

**Expected Output**:
```
[2025-12-19 00:00:00] Auto Checkout Process Started
[2025-12-19 00:00:01] Processing booking #120 - Jane Smith
[2025-12-19 00:00:02] Final charges calculated: ₱3,000.00
[2025-12-19 00:00:03] Receipt generated: receipt_120.pdf
[2025-12-19 00:00:04] Email sent to: jane@example.com
[2025-12-19 00:00:05] Room #305 marked as available
[2025-12-19 00:00:06] Checkout completed for booking #120
[2025-12-19 00:00:10] Total checkouts processed: 5
[2025-12-19 00:00:10] Process completed successfully
```

---

## ⚙️ Setting Up Cron Jobs

### Windows (Task Scheduler)

#### Method 1: Using Task Scheduler GUI

1. Open Task Scheduler (`taskschd.msc`)
2. Click "Create Basic Task"
3. Name: "BarCIE Auto Check-in/Check-out"
4. Trigger: Daily or Hourly
5. Action: Start a program
6. Program: `C:\xampp\php\php.exe`
7. Arguments: `C:\xampp\htdocs\barcie_php\cron\auto_checkin_checkout.php`
8. Click Finish

#### Method 2: Using Command Line

```powershell
# Create hourly task for check-in/check-out
schtasks /create /tn "BarCIE_AutoCheckinCheckout" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\barcie_php\cron\auto_checkin_checkout.php" /sc hourly /st 00:00

# Create daily task for checkout (midnight)
schtasks /create /tn "BarCIE_AutoCheckout" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\barcie_php\cron\auto_checkout.php" /sc daily /st 00:00
```

#### Verify Tasks
```powershell
# List all tasks
schtasks /query /tn "BarCIE*"

# Run task manually
schtasks /run /tn "BarCIE_AutoCheckinCheckout"
```

---

### Linux/Unix (Crontab)

#### Edit Crontab
```bash
crontab -e
```

#### Add Cron Jobs
```bash
# Run auto_checkin_checkout.php every hour
0 * * * * /usr/bin/php /path/to/barcie_php/cron/auto_checkin_checkout.php >> /var/log/barcie_checkin.log 2>&1

# Run auto_checkout.php daily at midnight
0 0 * * * /usr/bin/php /path/to/barcie_php/cron/auto_checkout.php >> /var/log/barcie_checkout.log 2>&1
```

#### Cron Schedule Examples
```bash
# Every hour
0 * * * * command

# Every 30 minutes
*/30 * * * * command

# Daily at 2 AM
0 2 * * * command

# Every Monday at 8 AM
0 8 * * 1 command

# First day of every month
0 0 1 * * command
```

---

## 📊 Logging and Monitoring

### Log File Structure

Create log directory:
```bash
mkdir -p logs/cron
```

### Enable Logging in Scripts

```php
// At the beginning of cron script
$log_file = __DIR__ . '/../logs/cron/auto_checkin_' . date('Y-m-d') . '.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Usage
logMessage('Process started');
logMessage('Checked in booking #123');
logMessage('Process completed');
```

### Log Rotation

Implement log rotation to prevent large file sizes:

```php
// Keep only last 30 days of logs
$log_dir = __DIR__ . '/../logs/cron/';
$files = glob($log_dir . '*.log');
foreach ($files as $file) {
    if (filemtime($file) < strtotime('-30 days')) {
        unlink($file);
    }
}
```

---

## 🔔 Email Notifications

### Check-in Email Template

```php
$subject = "Welcome to BarCIE - Check-in Confirmation";
$message = "
Dear {guest_name},

Welcome to BarCIE International Center!

Your check-in has been confirmed:
- Booking ID: {booking_id}
- Room: {room_type}
- Check-in Date: {check_in_date}
- Check-out Date: {check_out_date}

We hope you enjoy your stay!

Best regards,
BarCIE Team
";
```

### Check-out Email Template

```php
$subject = "Thank You for Staying at BarCIE";
$message = "
Dear {guest_name},

Thank you for choosing BarCIE International Center!

Check-out Summary:
- Booking ID: {booking_id}
- Room: {room_type}
- Stay Duration: {duration} nights
- Total Amount: ₱{total_amount}

Your receipt is attached.

We hope to see you again soon!

Best regards,
BarCIE Team
";
```

---

## 🛠️ Testing Cron Scripts

### Test Before Scheduling

```bash
# Run script manually
php cron/auto_checkin_checkout.php

# Check for errors
php -l cron/auto_checkin_checkout.php

# Test with output
php cron/auto_checkin_checkout.php 2>&1
```

### Dry Run Mode

Add a test mode to scripts:

```php
// Add at the top of script
$DRY_RUN = true; // Set to false in production

// Before database update
if ($DRY_RUN) {
    echo "DRY RUN: Would update booking #$booking_id\n";
} else {
    // Actual database update
    $stmt->execute();
}
```

### Test with Sample Data

```php
// Create test booking with today's dates
INSERT INTO bookings (
    guest_name, 
    room_type, 
    check_in_date, 
    check_out_date, 
    status
) VALUES (
    'Test Guest', 
    'Standard', 
    CURDATE(), 
    DATE_ADD(CURDATE(), INTERVAL 2 DAY), 
    'confirmed'
);

// Run cron script
// Verify booking status changed to 'checked_in'
```

---

## 📈 Performance Considerations

### Optimize Queries

```php
// Bad: Multiple queries
foreach ($bookings as $booking) {
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute(['checked_in', $booking['id']]);
}

// Good: Batch update
UPDATE bookings 
SET status = 'checked_in' 
WHERE check_in_date = CURDATE() 
  AND status = 'confirmed';
```

### Limit Processing

```php
// Process in batches
$batch_size = 50;
$offset = 0;

do {
    $bookings = fetchBookings($batch_size, $offset);
    processBookings($bookings);
    $offset += $batch_size;
} while (count($bookings) == $batch_size);
```

---

## 🔒 Security Considerations

### File Permissions

```bash
# Restrict access to cron scripts
chmod 700 cron/*.php
```

### Authentication

```php
// Verify script is run from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Verify script path
$allowed_path = '/path/to/barcie_php/cron/';
if (strpos(__FILE__, $allowed_path) !== 0) {
    die('Invalid execution path');
}
```

### Database Credentials

- Use read-only credentials when possible
- Limit permissions to necessary tables
- Use separate cron user account

---

## 🐛 Troubleshooting

### Common Issues

**Issue**: Script doesn't run
```bash
# Check PHP path
which php
# or
where php

# Test PHP execution
php -v

# Check file permissions
ls -la cron/
```

**Issue**: Database connection fails
```php
// Add connection test
if (!$conn) {
    error_log("Cron DB Connection Failed: " . mysqli_connect_error());
    exit(1);
}
```

**Issue**: Emails not sending
```php
// Test mail configuration
require_once 'database/mail_config.php';
$mail = getMailer();
$mail->addAddress('test@example.com');
$mail->Subject = 'Test';
$mail->Body = 'Test email';

if (!$mail->send()) {
    error_log("Mail Error: " . $mail->ErrorInfo);
}
```

---

## 📋 Maintenance Checklist

### Daily
- [ ] Check cron execution logs
- [ ] Verify successful check-ins/check-outs
- [ ] Monitor error logs

### Weekly
- [ ] Review cron job performance
- [ ] Check email delivery rates
- [ ] Verify data accuracy

### Monthly
- [ ] Rotate log files
- [ ] Update cron scripts if needed
- [ ] Performance optimization review

---

## 📝 Adding New Cron Jobs

### Template for New Cron Script

```php
<?php
/**
 * New Cron Job
 * Description: What this cron job does
 * Schedule: When it should run
 */

// Verify CLI execution
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

// Include dependencies
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../database/error_handler.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Logging function
$log_file = __DIR__ . '/../logs/cron/your_script_' . date('Y-m-d') . '.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

// Start process
logMessage('Process started');

try {
    // Your cron job logic here
    
    logMessage('Process completed successfully');
} catch (Exception $e) {
    logMessage('Error: ' . $e->getMessage());
    error_log('Cron Error: ' . $e->getMessage());
    exit(1);
}

exit(0);
```

---

## 🔗 Related Documentation

- Main Project: `../README.md`
- Database: `../database/README.md`
- API: `../api/README.md`

---

For questions or support, please contact the system administrator.
