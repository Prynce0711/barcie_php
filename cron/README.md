# Automated Checkout System

## Overview
This system automatically handles checkout reminders and guest checkouts based on booking schedules.

## Features
1. **Checkout Reminders**: Sends email reminders to guests 1 hour before their checkout time
2. **Auto-Checkout**: Automatically checks out guests when their checkout time is reached
3. **Email Notifications**: Sends confirmation emails when checkout is completed

## Setup Instructions

### Option 1: Quick Setup (Recommended)
1. Right-click on `setup_auto_checkout.bat`
2. Select "Run as administrator"
3. Follow the on-screen instructions

### Option 2: Manual Setup

#### Step 1: Update Database
Run this SQL command to add the reminder tracking column:
```sql
ALTER TABLE bookings 
ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0 
AFTER discount_status;
```

#### Step 2: Configure Email Settings
Make sure your `.env` file or `database/mail_config.php` has valid SMTP settings:
- SMTP_HOST
- SMTP_USERNAME
- SMTP_PASSWORD
- SMTP_PORT
- FROM_EMAIL

#### Step 3: Create Windows Task Scheduler Job
1. Open Task Scheduler (search "Task Scheduler" in Windows)
2. Click "Create Task"
3. General Tab:
   - Name: `BarCIE_Auto_Checkout`
   - Description: "Automated checkout and reminder system"
   - Run whether user is logged on or not
   - Run with highest privileges

4. Triggers Tab:
   - Click "New"
   - Begin the task: On a schedule
   - Settings: Daily, Recur every 1 day
   - Advanced: Repeat task every 1 hour, for a duration of 1 day
   - Enabled: Yes

5. Actions Tab:
   - Click "New"
   - Action: Start a program
   - Program/script: `C:\xampp\php\php.exe`
   - Arguments: `-f "C:\xampp\htdocs\barcie_php\cron\auto_checkout.php"`

6. Conditions Tab:
   - Uncheck "Start the task only if the computer is on AC power"

7. Settings Tab:
   - Check "Run task as soon as possible after a scheduled start is missed"

## Testing

### Test via Command Line
```bash
C:\xampp\php\php.exe -f "C:\xampp\htdocs\barcie_php\cron\auto_checkout.php"
```

### Test via Browser (for testing only)
```
http://localhost/barcie_php/cron/auto_checkout.php?test=run_cron_now
```

## How It Works

### Reminder Process
1. Script runs every hour
2. Checks for bookings with status `checked_in`
3. Finds bookings where checkout time is within 1 hour
4. Sends reminder email if not already sent
5. Marks booking with `reminder_sent = 1`

### Auto-Checkout Process
1. Script runs every hour
2. Checks for bookings with status `checked_in`
3. Finds bookings where checkout time has passed
4. Updates status to `checked_out`
5. Sends checkout confirmation email

## Logs
All activities are logged to:
```
C:\xampp\htdocs\barcie_php\logs\auto_checkout.log
```

## Troubleshooting

### Emails not sending
- Check SMTP credentials in `.env` or `mail_config.php`
- Check `auto_checkout.log` for error messages
- Test email settings manually

### Task not running
- Verify Task Scheduler has the correct paths
- Check if PHP path is correct: `C:\xampp\php\php.exe`
- Ensure task is set to "Run whether user is logged on or not"
- Check Windows Event Viewer for task scheduler errors

### No reminders being sent
- Check if bookings have email addresses in the `details` field
- Verify checkout times are in the future
- Check `reminder_sent` column is 0 for eligible bookings

## Manual Operations

### View scheduled task status
```bash
schtasks /query /tn "BarCIE_Auto_Checkout"
```

### Run task manually
```bash
schtasks /run /tn "BarCIE_Auto_Checkout"
```

### Disable task
```bash
schtasks /change /tn "BarCIE_Auto_Checkout" /disable
```

### Enable task
```bash
schtasks /change /tn "BarCIE_Auto_Checkout" /enable
```

### Delete task
```bash
schtasks /delete /tn "BarCIE_Auto_Checkout" /f
```

## Email Template Customization
Email templates are defined in `auto_checkout.php`:
- `sendCheckoutReminder()` - Reminder email template
- `sendAutoCheckoutNotification()` - Checkout confirmation template

Edit these functions to customize the email content and styling.

## Support
For issues or questions, check the log files or contact the system administrator.
