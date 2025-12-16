# BarCIE Hotel Management System - Updates

## Recent Changes

### 1. Recent Activities Dashboard Widget

A new **Recent Activities** section has been added to the dashboard that displays the latest system activities in real-time.

#### Activities Tracked:
- ✏️ **Draft booking created** (pencil booking)
- ✅ **Pencil approve** (shows who approved)
- 📅 **Booking reserved** (with payment submitted)
- 💰 **Payment approved** (shows who approved)
- 🔑 **Guest checked-in**
- 🚪 **Guest checked-out**
- ❌ **Booking cancelled**

#### Features:
- Real-time activity feed with color-coded icons
- Shows the admin who performed approvals
- Auto-refreshes every 30 seconds
- Displays activities from the last 30 days
- Sample activities included for demonstration

#### Location:
The Recent Activities section appears on the main dashboard page below the metrics cards.

---

### 2. Booking Management Updates

#### Approved Date Column
- **Changed:** The "Created" column in Booking Management has been replaced with "Approved" column
- **Shows:** The date and time when the booking was approved by an admin
- **Displays:** "Pending" for bookings that haven't been approved yet
- **Tracks:** The admin who approved the booking (stored in database)

#### Auto Check-in/Check-out
Manual check-in and check-out buttons have been **removed** and replaced with automatic processing:

**Auto Check-in:**
- ⏰ Triggers at **2:00 PM (14:00)** on the check-in date
- ✅ Automatically changes booking status from `approved`/`confirmed` to `checked_in`
- 🏨 Updates room status to `occupied`

**Auto Check-out:**
- ⏰ Triggers at **12:00 PM (12:00)** on the check-out date
- ✅ Automatically changes booking status from `checked_in` to `checked_out`
- 🏨 Updates room status to `available`

**Action Buttons:**
- ✅ **Approve/Reject** - Available for pending bookings
- 🔔 **Auto Check-in/out Badge** - Shown for approved and checked-in bookings
- ❌ **Cancel** - Available for approved and checked-in bookings
- 👁️ **View** - Always available to see booking details

---

### 3. Database Changes

#### New Columns Added:
```sql
-- Bookings table
approved_by INT(11) NULL       -- Admin ID who approved
approved_at DATETIME NULL      -- Timestamp of approval

-- Pencil_bookings table
approved_by INT(11) NULL       -- Admin ID who approved
approved_at DATETIME NULL      -- Timestamp of approval
```

#### Migration File:
Run the migration to add these columns:
```
http://localhost/barcie_php/database/migrations/008_add_approved_tracking.php
```

Or via command line:
```bash
php database/migrations/008_add_approved_tracking.php
```

---

### 4. Auto Check-in/Check-out Cron Job

#### Setup Instructions

**For Windows Task Scheduler:**

1. **Open Task Scheduler**
   - Press `Win + R`, type `taskschd.msc`, press Enter

2. **Create New Task**
   - Click "Create Task" (not "Create Basic Task")
   - Name: `BarCIE Auto Check-in/Check-out`
   - Description: `Automatically checks in guests at 2pm and checks out at 12pm`
   - Select "Run whether user is logged on or not"
   - Check "Run with highest privileges"

3. **Triggers Tab**
   - Click "New"
   - Begin the task: "On a schedule"
   - Settings: Daily
   - Recur every: 1 days
   - Advanced settings:
     - ✅ Repeat task every: **30 minutes** (or 1 hour)
     - ✅ for a duration of: **Indefinitely**
     - ✅ Enabled

4. **Actions Tab**
   - Click "New"
   - Action: "Start a program"
   - Program/script: `C:\xampp\php\php.exe`
   - Add arguments: `-f "C:\xampp\htdocs\barcie_php\cron\auto_checkin_checkout.php"`
   - Start in: `C:\xampp\htdocs\barcie_php\cron`

5. **Conditions Tab**
   - Uncheck "Start the task only if the computer is on AC power"

6. **Settings Tab**
   - ✅ Allow task to be run on demand
   - ✅ If the task fails, restart every: 1 minute
   - ✅ Attempt to restart up to: 3 times

#### Testing the Cron Job

**Via Command Line:**
```bash
cd C:\xampp\htdocs\barcie_php
php cron\auto_checkin_checkout.php
```

**Via Web Browser (for testing only):**
```
http://localhost/barcie_php/cron/auto_checkin_checkout.php?test=run_cron_now
```

#### Log Files

Check the log file to see cron job execution history:
```
logs/auto_checkin_checkout.log
```

Sample log entry:
```
[2025-12-15 14:00:00] === Auto Check-In/Check-Out Cron Job Started ===
[2025-12-15 14:00:00] Processing auto check-ins (current time: 14:00:05)...
[2025-12-15 14:00:01] ✓ Auto checked-in: Receipt #BARCIE-20251215-0001 - Deluxe Room
[2025-12-15 14:00:01] === Cron Job Completed ===
[2025-12-15 14:00:01] Summary: 1 check-ins, 0 check-outs processed
```

---

### 5. API Endpoints

#### New API:
**`/api/recent_activities.php`**
- Fetches recent system activities for the dashboard
- Requires admin authentication
- Returns activities from the last 30 days
- Supports limit parameter: `?limit=10`

**Response Format:**
```json
{
  "success": true,
  "activities": [
    {
      "activity_type": "payment_approved",
      "id": 123,
      "receipt_no": "BARCIE-20251215-0001",
      "guest_name": "John Doe",
      "activity_date": "2025-12-15 10:30:00",
      "room_name": "Deluxe Room",
      "admin_name": "Admin User"
    }
  ]
}
```

---

### 6. File Changes Summary

#### New Files:
- `api/recent_activities.php` - API endpoint for activity feed
- `cron/auto_checkin_checkout.php` - Automated check-in/check-out script
- `database/migrations/008_add_approved_tracking.php` - Database migration
- `UPDATES.md` - This documentation file

#### Modified Files:
- `components/dashboard/sections/dashboard_section.php` - Added Recent Activities widget
- `components/dashboard/sections/bookings_section.php` - Changed "Created" to "Approved" header
- `components/dashboard/sections/bookings_table_content.php` - Updated to show approved date, removed manual check-in/out buttons
- `database/user_auth.php` - Added approved_by and approved_at tracking

---

### 7. Sample Activities

The system includes sample activities for demonstration. These show:
- A draft booking created for Standard Room
- Payment approval by Admin User
- Guest check-in to Deluxe Suite
- Guest check-out from Conference Room
- A cancelled booking

---

### 8. Important Notes

⚠️ **Cron Job Timing:**
- The cron should run at least every 30 minutes or hourly
- Check-ins are processed only at or after 2:00 PM
- Check-outs are processed only at or after 12:00 PM
- Running more frequently (every 15-30 minutes) ensures timely processing

⚠️ **Database Migration:**
- Must run migration `008_add_approved_tracking.php` before the new features work properly
- This adds the `approved_by` and `approved_at` columns to both tables

⚠️ **Room Status:**
- Auto check-in sets room to `occupied`
- Auto check-out sets room to `available`
- May need manual adjustment if rooms require cleaning

---

### 9. Future Enhancements

Possible improvements:
- Email notifications for auto check-in/check-out
- SMS notifications for guests
- Configurable check-in/check-out times per room type
- Room cleaning status workflow
- Activity filter and search in dashboard
- Export activities to PDF/Excel

---

## Support

For issues or questions, please contact the development team or refer to the main README.md file.

**Last Updated:** December 15, 2025
