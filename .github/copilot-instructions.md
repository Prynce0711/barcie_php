# Copilot Instructions for BarCIE Hotel Management System

## Project Overview
BarCIE is a PHP-based hotel management system for LCUP's BarCIE facility. It features dual authentication (guest/admin), room and booking management, real-time chat, and a professional support interface. The system is used for both educational and real-world scenarios.

## Architecture & Key Components
- **Entry Points**: `index.php` (landing/auth), `dashboard.php` (admin), `Guest.php` (guest portal)
- **Database Layer**: All DB access via `database/` scripts (e.g., `db_connect.php`, `user_auth.php`).
- **Chat System**: RESTful API endpoints in `database/user_auth.php` (see README for endpoint details). Chat tables must be initialized (`database/init_chat.php`).
- **Admin/Guest Separation**: Role-based session logic and UI. Admin features in dashboard, guest features in portal.
- **Assets**: CSS/JS in `assets/`, images in `assets/images/`, uploads in `uploads/`.
- **Docker Support**: Containerized via `Dockerfile` and `docker-compose.yml`. Local dev via XAMPP also supported.

## Developer Workflows
- **Local Dev**: Use XAMPP (Windows) or Docker Compose. Configure `.env` for DB credentials.
- **Build/Run**:
  - Docker: `docker-compose up --build`
  - XAMPP: Place in `htdocs`, start Apache/MySQL, access via browser.
- **Database Setup**: Create `barcie_db`, import schema, run `database/init_chat.php` for chat tables.
- **Testing**: Use `test_chat_endpoints.php` for chat API validation.
- **CI/CD**: Automated Docker builds via GitHub Actions (`.github/workflows/build-docker.yaml`).

## Project-Specific Conventions
- **Email Validation**: Only `@gmail.com` allowed for registration.
- **Password Policy**: Min 8 chars, must include letters and numbers.
- **File Uploads**: Max 5MB, allowed types: jpg, jpeg, png, gif.
- **Session Keys**: `user_logged_in` for guests, `admin_logged_in` for admins.
- **Room/Facility Types**: Managed via `items` table, type field is `room` or `facility`.
- **Booking Types**: `reservation` (room) or `pencil` (function hall).
- **Chat**: All chat logic via API endpoints, not direct DB access.

## Integration Points
- **PHPMailer**: Used for email notifications (see `vendor/PHPMailer/`).
- **FullCalendar.js**: Admin dashboard calendar.
- **Tailwind CSS**: Utility-first styling.

## Troubleshooting
- **Chat Issues**: Ensure chat tables are initialized and session is set.
- **Docker**: Use container logs for debugging (`docker-compose logs web/db`).
- **Image Uploads**: Check permissions on `uploads/`.

## Examples
- **Chat API Usage**: See README for endpoint samples.
- **Database Connection**: Use env vars, see `database/db_connect.php`.
- **Admin Creation**: Insert directly into `admins` table.

## Key Files/Directories
- `index.php`, `dashboard.php`, `Guest.php`
- `database/` (API, DB logic)
- `assets/` (static files)
- `uploads/` (user images)
- `.env.example`, `Dockerfile`, `docker-compose.yml`
- `.github/workflows/build-docker.yaml`

---
For more details, see `README.md`. If any section is unclear or missing, please provide feedback for improvement.
