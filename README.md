# BarCIE International Center - Hospitality Management System

Comprehensive web-based booking and management platform for BarCIE International Center, including public website content, guest reservation flows, and admin operations.

This README has been updated as a practical user manual for:

1. Public/Landing page navigation
2. Guest booking and reservation steps
3. Admin section management
4. Detailed booking action lifecycle (verification, approval, status transitions)

## 1. Quick Access

Use the route query in `index.php`:

| User Type       | URL                                       | Purpose                                |
| --------------- | ----------------------------------------- | -------------------------------------- |
| Public          | `/index.php` or `/index.php?view=landing` | Landing page                           |
| Guest           | `/index.php?view=guest`                   | Guest portal                           |
| Admin Login     | `/index.php?view=admin`                   | Admin authentication                   |
| Admin Dashboard | `/index.php?view=dashboard`               | Admin control panel (session required) |

## 2. Setup and Run

### Requirements

- PHP 7.4+
- MySQL/MariaDB
- Apache/Nginx (XAMPP supported)
- Composer
- Node.js + npm (for Tailwind build)

### Install

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend/build dependencies:

```bash
npm install
```

3. Build admin Tailwind CSS:

```bash
npm run build
```

4. For development/watch mode:

```bash
npm run dev
```

5. Configure DB and environment (`database/config.php`, `.env` if used), then serve in XAMPP (`htdocs/barcie_php`).

## 3. Landing Page Manual (Public)

Main file: `Components/Landing/index.php`

### Sections and What They Do

1. `#home` (Hero)

- Shows CTA buttons: `Get Started` (opens guest overview) and `Learn More`.

2. `#about`

- Displays About content from DB (`landing_about_content`) and dynamic counters (rooms/facilities, ratings).

3. `#vision-mission`

- Presents institutional vision, mission, and history blocks.

4. `#news`

- Loads published news from `api/news.php?action=fetch_published`.
- Supports `Read More` modal and paginated load more.

5. `#event-stylists`

- Shows active event stylist partners from `landing_partners`.

6. `#caterings`

- Shows active catering partners from `landing_partners`.

7. `#brochure`

- Displays brochure carousel from `landing_brochures` with page switching and download.

8. `#contact`

- Shows official Viber, phone, email, and map/location details.

## 4. Guest Portal Manual

Main file: `Components/Guest/index.php`

### Guest Sidebar Sections

1. `Overview`

- Welcome + booking guidance and reminders.

2. `Availability Calendar`

- Card list with room/facility calendar modal.
- Uses availability APIs and filter/search components.

3. `Rooms and Facilities`

- Card gallery view with price/capacity and room details/review modal.

4. `Booking and Reservation`

- Reservation (confirmed booking flow)
- Pencil Booking (draft reservation flow)

5. `Feedback`

- Star-rating feedback form and recent review stream.

### Guest Booking - Reservation Flow (Step by Step)

1. Open Guest portal and go to `Booking & Reservation`.
2. Keep booking type on `Reservation`.
3. Fill required fields:

- Receipt no (auto-generated)
- Room/Facility
- Guest Name, Contact, Email, Age
- Check-in, Check-out, Occupants
- Optional company fields

4. Optional discount:

- Choose discount type
- Upload valid ID/proof
- System auto-applies discount if valid

5. Click `Review Booking`.
6. In confirmation modal:

- Review booking summary
- Choose add-ons (if any)
- Choose payment method (cash or bank transfer)
- For bank transfer, upload payment proof
- Check policy agreement checkboxes

7. Click `Confirm & Proceed`.
8. Result:

- Reservation is saved with `status = pending`
- `payment_status = pending`
- Confirmation email is sent
- Success modal appears with receipt download option.

### Guest Booking - Pencil Booking Flow (Step by Step)

1. In booking section, switch to `Pencil Booking`.
2. Fill required fields similar to reservation.
3. Accept the two-week policy checkbox.
4. Submit draft reservation.
5. System creates receipt `PENCIL-YYYYMMDD-####` in `pencil_bookings`.
6. Email is sent with conversion link and expiration (14 days).
7. Status starts as `pending` until admin action or conversion.

### Guest Booking Status Meanings

| Status                | Meaning                                           |
| --------------------- | ------------------------------------------------- |
| `pending`             | Submitted, waiting for admin/payment action       |
| `approved`            | Verified/approved by admin                        |
| `confirmed`           | Confirmed booking state                           |
| `checked_in`          | Guest already checked in                          |
| `checked_out`         | Guest already checked out                         |
| `cancelled`           | Booking cancelled                                 |
| `rejected`            | Booking/payment rejected                          |
| `need to change room` | Conflict detected, guest must choose another room |

## 5. Admin Dashboard Manual

Main file: `Components/Admin/index.php`

### Admin Sections and Functions

| Section               | What It Does                                                                                   |
| --------------------- | ---------------------------------------------------------------------------------------------- |
| Dashboard             | KPI cards, recent activities, and summary charts.                                              |
| Calendar and Items    | Visual occupancy per room/facility with event statuses.                                        |
| Rooms and Facilities  | Add/edit/delete room or facility records, pricing, capacity, and media.                        |
| Booking Verification  | Lists bookings where `payment_status = pending`; admins review proofs and click Verify/Reject. |
| Booking Management    | Main reservation table for approve/reject/cancel flows and resend room-change notifications.   |
| Pencil Management     | Handles `pencil_bookings` records, status updates, and expiration monitoring.                  |
| Discount Management   | Maintains discount rule definitions and percentages.                                           |
| News and Updates      | Creates and manages landing page news cards.                                                   |
| Partners Management   | Manages catering and event stylist partner entries and visibility.                             |
| Brochure Management   | Manages brochure files shown on landing page.                                                  |
| Reports and Analytics | Date/type filtered reports with PDF and Excel export.                                          |
| Account Management    | Admin account list, filters, statuses, and role controls with extra auth guard.                |

Booking table quick action mapping:

1. `pending` -> Approve or Reject
2. `approved` / `checked_in` -> Cancel
3. `need to change room` -> Resend room-change email

## 6. How Admin Takes Action on Bookings (Detailed)

This is the core action lifecycle.

### A. Reservation Creation

When guest submits reservation:

1. Row is inserted in `bookings`.
2. Initial values:

- `status = pending`
- `payment_status = pending`

3. Booking appears first in Payment Verification queue.

### B. Payment Verification Actions

In `Payment Verification`:

1. Verify

- Sets `payment_status = verified`
- Sets booking `status = approved` (unless overlap conflict handling triggers)
- Sets `payment_verified_at` and verifier info
- Sends approval-related email

2. Reject

- Sets `payment_status = rejected`
- Sets booking `status = pending`
- Sends rejection email

Special case during Verify:

- If another overlapping booking is already approved for the same room/date range, current booking is set to `status = need to change room` and `payment_status = rejected`.

### C. Conflict and Auto-Rejection Logic

When verifying/approving a booking, system checks overlapping same-room schedules.

If conflict exists:

1. Current booking may become `need to change room`
2. Conflicting pending/overlap bookings can be auto-marked `need to change room`
3. Guests receive change-room email with room suggestions and link to room-change page.

### D. Booking Management Actions

From bookings table:

1. `Approve` -> `admin_update_booking` action
2. `Reject` -> `admin_update_booking`
3. `Cancel` -> `admin_update_booking`
4. `Resend Email` (need-to-change-room) -> `resend_change_room_email`

### E. Guest Change Room Action

Guest can use change-room link:

1. Select new room number
2. Server validates no date overlap
3. Updates booking room
4. Resets booking to pending verification state.

### F. Automatic Check-in / Check-out

Automated scripts (in `Components/Admin/cron`):

1. Auto check-in after 2:00 PM on check-in date (approved/confirmed -> checked_in)
2. Auto check-out after 12:00 PM on checkout date (checked_in -> checked_out)
3. Room status updates to occupied/available accordingly.

### G. Cancellation Flow

Guest cancellation endpoint: `api/CancelBooking.php`

1. Validates receipt + email
2. Applies cancellation status update
3. Handles cancellation warning windows
4. Sends cancellation confirmation email.

## 7. Role and Permission Notes

Observed behavior in current implementation:

1. Staff restrictions in UI

- Payment verification actions hidden (view only)
- Rooms management add/edit/delete hidden
- News edit/delete/add hidden
- Pencil status action buttons hidden

2. Pencil status server-side guard

- `update_pencil_booking_status` allows only `admin`, `manager`, `super_admin`.

3. Super admin

- Permission manager treats `super_admin` as full access.

## 8. Reports and Data Interpretation

Reports source APIs:

1. `api/ReportsData.php`
2. `api/ExportReportPdf.php`
3. `api/ExportReportExcel.php`

Important interpretation:

1. Revenue calculations prioritize verified/approved booking sets.
2. Occupancy uses active statuses (`approved`, `confirmed`, `checked_in`).
3. Report filters include date range, room type, and report category.

## 9. Key APIs Used by UI

### Guest-facing

- `api/items.php` - Rooms/facilities list
- `api/availability.php` - Calendar events
- `api/RoomAvailability.php` - Occupied date checks per room
- `api/receipt.php` - Reservation receipt number generation
- `api/news.php?action=fetch_published` - Landing news

### Admin-facing

- `database/index.php?endpoint=user_auth` (POST actions)
- `api/GetBookingDetails.php` - Booking modal details
- `api/RecentActivities.php` - Dashboard feed
- `api/ReportsData.php` - Reports dashboard data

## 10. Troubleshooting

### Upload Problems

1. Check `uploads/` permissions.
2. Increase `upload_max_filesize` and `post_max_size` in PHP.
3. Confirm file type and size validation in form.

### Emails Not Sending

1. Verify SMTP settings in environment/config.
2. Check app-password/security requirements of provider.
3. Review PHP/mail logs.

### Session/Login Issues

1. Clear browser cache/cookies.
2. Verify session storage path permissions.
3. Confirm remember-me/session restore logic and DB connection availability.

## 11. Tech Stack

### Backend

- PHP 7.4+
- MySQL/MariaDB

### Frontend

- HTML, CSS, JavaScript
- Bootstrap 5.3.2
- Chart.js, FullCalendar, Font Awesome

### Build and Libraries

- Tailwind CLI (`npm run css:dev`, `npm run css:build`)
- PHPMailer
- DomPDF
- dotenv (vlucas/phpdotenv)

## 12. Contact

From landing contact section:

1. Viber: `0939 905 7425`
2. Telephone: `044 791 7424` / `044 919 8410`
3. Email: `barcieinternationalcenter@gmail.com`, `barcie@lcup.edu.ph`
4. Address: Valenzuela St. Capitol View Park Subd. Brgy. Bulihan, City of Malolos, Bulacan 3000

## 13. Credits

Developed for BarCIE International Center and La Consolacion University Philippines.

---

Version: 2.1.0
Last Updated: April 16, 2026
