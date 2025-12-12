# BarCIE Hotel Management System

A comprehensive PHP-based hotel management system for **Barasoain Center for Innovative Education (BarCIE)** - LCUP's Laboratory Facility for BS Tourism Management.

![BarCIE Logo](assets/images/imageBg/barcie_logo.jpg)

## 🏨 Overview

BarCIE Hotel Management System is a full-featured web application designed to stre5. **Submit Feedback**: Provide service feedback

## � Professional Email System

The BarCIE system features a comprehensive email notification system that sends beautiful, professional HTML emails for all booking lifecycle events.

### Email Features
- **8 Different Email Templates**: Each status change triggers a unique, professionally designed email
- **Beautiful Design**: Modern HTML emails with blue gradient header and BarCIE logo
- **Embedded Logo**: BarCIE logo included in all emails (base64 encoded)
- **Responsive Layout**: Works perfectly on desktop and mobile email clients
- **Status-Specific Styling**: Color-coded badges and cards for each email type
- **SMTP Integration**: Powered by PHPMailer with Gmail support
- **Automatic Sending**: Emails sent automatically on all booking/discount status changes

### Email Templates

#### 1. Booking Confirmation
**When**: Guest submits a new booking  
**Subject**: "Booking Confirmation - BarCIE International Center"  
**Contains**: Receipt number, room details, check-in/out dates, pending status, discount info (if applicable)

#### 2. Booking Approved
**When**: Admin approves the reservation  
**Subject**: "Booking Approved - BarCIE International Center"  
**Contains**: Green success badge, reservation details, check-in reminders

#### 3. Booking Rejected
**When**: Admin rejects the reservation  
**Subject**: "Booking Status Update - BarCIE International Center"  
**Contains**: Red status badge, booking details, contact invitation

#### 4. Check-in Confirmed
**When**: Guest is checked in  
**Subject**: "Check-in Confirmed - BarCIE International Center"  
**Contains**: Cyan badge, welcome message, check-out reminder

#### 5. Check-out Complete
**When**: Guest is checked out  
**Subject**: "Check-out Complete - BarCIE International Center"  
**Contains**: Purple badge, thank you message, feedback invitation

#### 6. Booking Cancelled
**When**: Admin cancels the reservation  
**Subject**: "Booking Cancelled - BarCIE International Center"  
**Contains**: Orange warning badge, cancellation details

#### 7. Discount Approved
**When**: Admin approves discount application (independent of booking)  
**Subject**: "Discount Application Approved - BarCIE"  
**Contains**: Green badge, discount type, note about booking approval

#### 8. Discount Rejected
**When**: Admin rejects discount application  
**Subject**: "Discount Application Update - BarCIE"  
**Contains**: Red badge, standard rate notification

### Email Configuration

**SMTP Settings** (in `database/mail_config.php`):
```php
return [
   'host' => 'smtp.example.com',
   'username' => 'smtp_user@example.com',
   'password' => 'SMTP_PASSWORD_PLACEHOLDER',
   'secure' => 'tls',
   'port' => 587,
   'from_email' => 'no-reply@example.com',
   'from_name' => 'BarCIE'
];
```

**Gmail Setup**:
1. Enable 2-Step Verification on your Gmail account
2. Generate App Password at: https://myaccount.google.com/apppasswords
3. Update `mail_config.php` with your credentials

### Testing Email System

**Test SMTP Configuration**:
```
http://localhost/barcie_php/test_email.php?email=YOUR_EMAIL@example.com
```

**Test Booking Emails**:
```
http://localhost/barcie_php/test_booking_email.php?email=YOUR_EMAIL@example.com
```

### Email Design Specifications
- **Header**: Blue gradient (#1e3c72 → #2a5298) with embedded BarCIE logo
- **Logo**: 80x80px circular image with white border
- **Layout**: 600px width, responsive table-based design
- **Typography**: Modern sans-serif fonts (Segoe UI, Roboto, Helvetica Neue)
- **Color Coding**: Status-specific colors (Green, Red, Yellow, Cyan, Purple, Orange)
- **Compatibility**: Works in Gmail, Outlook, Apple Mail, Yahoo, and all major email clients

## 🔧 Configuration

## ✨ Features

### 🔐 Authentication System
- **Admin Authentication**: Secure login system for administrators
- **Password Security**: Real-time validation and secure hashing
- **Session Management**: Persistent admin sessions with role-based access

### 👨‍💼 Admin Dashboard
- **Interactive Calendar**: FullCalendar integration with booking visualization
- **Room & Facility Management**: CRUD operations for rooms and facilities
- **Advanced Booking Management**: 
  - Approve, reject, check-in, check-out functionality
  - **Separate Discount Approval System**: Independent discount approval/rejection without affecting booking status
  - Email notifications for all status changes
  - Dual-action buttons for booking and discount decisions
- **Email System**: 
  - Professional HTML email templates with BarCIE branding
  - Automated notifications for all booking status changes (8 email types)
  - SMTP integration via PHPMailer with Gmail support
  - Beautiful blue gradient design with embedded logo
- **User Management**: Complete user administration interface
- **Real-time Statistics**: Active bookings, pending approvals, occupancy rates
- **Dark Mode**: Toggle between light and dark themes

### 👤 Guest Portal
- **Room Browsing**: Filter and view available rooms and facilities
- **Dual Booking System**:
  - **Standard Reservations**: Complete guest information and stay details
  - **Pencil Bookings**: Function hall reservations for events
  - **Discount Applications**: Apply for student, senior citizen, or PWD discounts with proof upload
- **Email Notifications**: 
  - Instant booking confirmation emails with receipt details
  - Status update notifications (approved, rejected, check-in, check-out, cancelled)
  - Separate discount approval/rejection notifications
  - Professional HTML email design with BarCIE branding
- **Profile Management**: Update personal information and view booking history
- **Payment Integration**: Multiple payment method support
- **Feedback System**: Submit and track feedback
- **Responsive Design**: Mobile-friendly interface

### 🏢 Room & Facility Management
- **Dynamic Content**: Real-time loading of rooms and facilities
- **Image Upload**: Visual representation of accommodations
- **Capacity Management**: Track room occupancy limits
- **Pricing System**: Flexible pricing for different room types
- **Type Classification**: Separate management for rooms vs facilities

### 📅 Booking System
- **Reservation Management**: Complete guest reservation workflow
- **Event Booking**: Specialized pencil booking for function halls
- **Discount System**: 
  - Support for student, senior citizen, and PWD discounts
  - Proof of eligibility upload (ID, certificates)
  - Separate approval workflow from booking approval
  - Independent discount and booking status tracking
- **Status Tracking**: Real-time booking status updates
- **Receipt Generation**: Automatic receipt number generation (BARCIE-YYYYMMDD-XXXX)
- **Date Validation**: Prevent double bookings and conflicts
- **Email Notifications**: Automated professional emails for all status changes

### 📧 Professional Email System
  - 8 different email templates for booking lifecycle
  - Beautiful HTML design with blue gradient header
  - BarCIE logo embedded in emails (base64)
  - Status-specific color coding (green, red, yellow, cyan, purple, orange)
  - Responsive email layout for all devices
  - SMTP via PHPMailer with Gmail integration
  - Automatic email sending on all status changes

## 🛠️ Technology Stack

### Backend
- **PHP 8.2+**: Server-side logic and database interactions (Compatible with PHP 8.4.13)
- **MySQL/MariaDB**: Relational database for data storage
- **Composer**: Dependency management for PHP packages
- **PHPMailer 6.11.1**: SMTP email functionality with OAuth2 support
- **PSR-3 Logging**: Professional logging standards implementation
- **Session Management**: PHP sessions for authentication
- **Apache**: Web server with mod_rewrite enabled

### Frontend
- **HTML5 & CSS3**: Modern markup and styling
- **Tailwind CSS**: Utility-first CSS framework
- **Vanilla JavaScript**: Client-side interactivity
- **FullCalendar.js**: Calendar and event management
- **Font Awesome**: Icon library
- **Responsive Design**: Mobile-first approach

### Development & Deployment
- **Docker**: Containerized deployment with PHP 8.2-Apache
- **Docker Compose**: Multi-container orchestration
- **XAMPP**: Alternative local development server
- **GitHub Actions**: Automated CI/CD pipeline
- **Docker Hub**: Container image registry
- **Browser Sync**: Live reloading during development
- **Git**: Version control

## 📁 Project Structure

```
barcie_php/
├── 📄 index.php              # Landing page with authentication
├── 📄 dashboard.php          # Admin dashboard interface
├── 📄 Guest.php              # Guest portal interface
├── 📄 package.json           # Project dependencies
├── 📄 README.md              # Project documentation
├── 📄 README_DOCKER.md       # Docker deployment guide
├── 📄 Dockerfile             # Docker container configuration
├── 📄 docker-compose.yml     # Multi-container orchestration
├── 📄 test_email.php         # Email configuration testing
├── 📄 test_booking_email.php # Booking email testing
├── 📄 EMAIL_TROUBLESHOOTING.md # Email system troubleshooting guide
├── 📄 EMAIL_BLUE_THEME_UPDATE.md # Blue email theme documentation
├── 📄 DASHBOARD_DISCOUNT_UPDATE.md # Discount system documentation
├── 📄 .env.example           # Environment variables template
├── 📄 .gitignore             # Git ignore patterns
├── 📄 .dockerignore          # Docker ignore patterns
│
├── 📂 .github/
│   └── 📂 workflows/
│       └── 📄 build-docker.yaml # CI/CD pipeline for Docker Hub
│
├── 📂 database/
│   ├── 📄 db_connect.php     # Database connection with env support
│   ├── 📄 user_auth.php      # Booking management + Email System
│   ├── 📄 admin_login.php    # Admin authentication
│   ├── 📄 fetch_items.php    # Room/facility data API
│   └── 📄 mail_config.php    # SMTP email configuration
│
├── 📂 assets/
│   ├── 📂 css/
│   │   ├── 📄 dashboard.css  # Admin dashboard styles
│   │   ├── 📄 dashboard-enhanced.css # Enhanced admin UI
│   │   ├── 📄 guest.css      # Guest portal styles
│   │   └── 📄 guest-enhanced.css # Enhanced guest UI
│   ├── 📂 js/
│   │   ├── 📄 dashboard-bootstrap.js # Admin dashboard scripts
│   │   └── 📄 guest-bootstrap.js     # Guest portal scripts
│   └── 📂 images/
│       ├── 📂 rooms/         # Room images
│       └── 📂 imageBg/       # Background images & logos
│
└── 📂 uploads/               # User uploaded content
    └── 📸 *.jpg              # Uploaded room/facility images
```



## 🚀 Installation & Setup

### 🐳 Docker Deployment (Recommended)

**Prerequisites**
- Docker and Docker Compose installed
- Git (optional, for cloning)

**Quick Start**
1. **Clone the Repository**
   ```bash
   git clone https://github.com/Prynce0711/barcie_php.git
   cd barcie_php
   ```

2. **Configure Environment**
   ```bash
   # Copy environment template
   cp .env.example .env
   
   # Edit .env file with your database credentials
   # Default values work for Docker setup
   ```

3. **Build and Start Containers**
   ```bash
   docker-compose up --build
   ```

4. **Access the Application**
   ```
   http://localhost:8080
   ```

5. **Import Database (if needed)**
   ```bash
   # Import your SQL dump
   docker exec -i $(docker-compose ps -q db) mysql -u root -p barcie_db < your_dump.sql
   ```

6. **Access the Application**
   ```
   http://localhost:8080
   ```

**Prerequisites**
- **XAMPP** (Apache, MySQL, PHP 8.x)
- **Web Browser** (Chrome, Firefox, Safari)
- **Git** (optional, for cloning)

**Step-by-Step Installation**

1. **Download & Install XAMPP**
   ```bash
   # Download from https://www.apachefriends.org/
   # Install with Apache and MySQL modules
   # Ensure PHP 8.0+ is included
   ```

2. **Clone or Download Project**
   ```bash
   # Option 1: Clone repository
   git clone https://github.com/Prynce0711/barcie_php.git
   
   # Option 2: Download ZIP and extract
   # Place in C:\xampp\htdocs\barcie_php
   ```

3. **Install Dependencies**
   ```bash
   # Navigate to project directory
   cd C:\xampp\htdocs\barcie_php
   
   # Install Composer dependencies
   composer install
   
   # If Composer is not installed, download from https://getcomposer.org/
   ```

4. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

5. **Create Database**
   ```sql
   # Access phpMyAdmin at http://localhost/phpmyadmin
   # Create database named 'barcie_db'
   CREATE DATABASE barcie_db;
   
   # Import the SQL schema (create tables as shown above)
   ```


7. **Create Admin Account**
   ```sql
   -- Insert admin user in the database (use a secure password and hash)
   INSERT INTO admins (username, password) VALUES ('ADMIN_USERNAME', 'ADMIN_PASSWORD_HASH');
   ```

8. **Verify Dependencies**
   ```bash
   # Test Composer autoloader
   php -r "require_once 'vendor/autoload.php'; echo 'Dependencies loaded successfully\n';"
   
   # Test PHPMailer
   php -r "require_once 'vendor/autoload.php'; use PHPMailer\PHPMailer\PHPMailer; echo 'PHPMailer available\n';"
   ```

9. **Access the Application**
   ```
   http://localhost/barcie_php/
   ```

## 🎯 Usage Guide

### For Administrators

1. **Login**: Click Admin Panel → Enter credentials (admin/admin123)
2. **Dashboard**: View statistics, calendar, and recent activities
3. **Room Management**: Add, edit, or delete rooms and facilities
4. **Booking Management**: Process reservations and manage check-ins/check-outs

### For Guests

1. **Browse Rooms**: View available accommodations
2. **Make Reservations**: Book rooms or function halls directly (no account required)
3. **Submit Feedback**: Provide service feedback

## 🔧 Configuration


# Tailscale Configuration (optional)
TS_AUTHKEY=your_authkey   # For VPN networking
```

### Composer Dependencies
The project uses Composer for dependency management:
```json
{
    "require": {
        "psr/log": "^3.0",
        "phpmailer/phpmailer": "^6.11"
    }
}
```

**Installation:**
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

### Email Validation
```javascript
// Email must end with @gmail.com
const emailPattern = /@gmail\.com$/;
```

### Password Requirements
```javascript
// Minimum 8 characters, letters and numbers
const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
```

### File Upload Settings
```php
// Maximum file size for image uploads
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
```

### Docker Configuration
```yaml
# docker-compose.yml services
services:
  web:                    # PHP 8.2 Apache container
    build: .
    ports: ["8080:80"]
    
  db:                     # MariaDB database container
    image: mariadb:latest
    environment:
      MYSQL_ROOT_PASSWORD: ""
      MYSQL_DATABASE: barcie_db
```

## 🎨 Customization

### Themes
- Dark mode toggle available
- CSS custom properties for easy color changes
- Responsive breakpoints for mobile devices

### Branding
- Logo: Replace `assets/images/imageBg/barcie_logo.jpg`
- Background: Update `assets/images/imageBg/BarCIE-0.jpg`
- Colors: Modify CSS variables in stylesheets

## 🔒 Security Features

- **Password Hashing**: PHP `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Secure session management
- **Input Validation**: Client and server-side validation
- **File Upload Security**: Type and size restrictions
- **XSS Prevention**: HTML escaping for user inputs

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   ```
   XAMPP: Check MySQL service and database credentials in .env
   Docker: Ensure containers are running with docker-compose ps
   ```

2. **Images Not Loading**
   ```
   Solution: Verify uploads folder permissions and file paths
   Docker: Check volume mounts in docker-compose.yml
   ```

3. **Session Issues**
   ```
   Solution: Check PHP session configuration and cookies
   Clear browser cache and restart containers if using Docker
   ```

4. **Email Validation Errors**
   ```
   Solution: Ensure email follows @gmail.com format
   ```

5. **Composer/Vendor Folder Issues**
   ```
   Problem: PHPMailer not loading or vendor autoloader errors
   Solution: Run 'composer install' to ensure dependencies are properly installed
   Verify: Check that vendor/autoload.php exists and is accessible
   Fix: Ensure database/user_auth.php uses Composer autoloader instead of manual includes
   ```

6. **Admin Booking Actions Not Working**
   ```
   Problem: Admin cannot approve/reject bookings or update booking status
   Solution: Check browser console for JavaScript errors
   Verify: Ensure updateBookingStatus function is loaded in dashboard-bootstrap.js
   Fix: Refresh page after booking status changes to see updated information
   ```

5. **Docker Build Issues**
   ```
   # Clear Docker cache and rebuild
   docker-compose down
   docker system prune -a
   docker-compose up --build
   ```

6. **Port Conflicts**
   ```
   # If port 8080 is busy, change in docker-compose.yml
   ports: ["8081:80"]  # Use port 8081 instead
   ```

7. **Email Not Sending**
   ```
   Problem: Booking confirmation or status emails not received
   Solution: 
   - Test email configuration at test_email.php
   - Verify Gmail App Password is correct in mail_config.php
   - Check spam/junk folder
   - Enable 2-Step Verification on Gmail account
   - Generate new App Password if needed
   - Check PHP error logs for email errors (C:\xampp\php\logs\php_error.log)
   - Verify port 587 is not blocked by firewall
   ```

8. **Discount and Booking Status Confusion**
   ```
   Problem: Discount rejection also rejects the booking
   Solution: Use separate buttons - "Approve/Reject" for booking, separate "Approve Discount/Reject Discount" buttons
   Verify discount_status column exists in bookings table
   Check that admin_update_discount action is being called (not admin_update_booking)
   ```

### Docker-Specific Troubleshooting

**Container Logs**
```bash
# Check web container logs
docker-compose logs web

# Check database container logs
docker-compose logs db
```

**Database Access**
```bash
# Connect to database container
docker-compose exec db mysql -u root -p barcie_db
```

**File Permissions**
```bash
# Fix file permissions in container
docker-compose exec web chown -R www-data:www-data /var/www/html
```

## 📞 Contact Information

For support or inquiries, please open an issue on the repository or contact the project owner via the repository contact methods. Personal phone numbers, private email addresses, and exact street addresses have been removed from this public README to protect privacy.

## 📊 Development Status

- ✅ **User Authentication System**
- ✅ **Admin Dashboard**
- ✅ **Room Management**
- ✅ **Booking System**
- ✅ **Guest Portal**
- ✅ **Database Integration**
- ✅ **Responsive Design**
- ✅ **Docker Containerization**
- ✅ **CI/CD Pipeline (GitHub Actions)**
- ✅ **Environment Configuration**
- ✅ **Composer Dependency Management**
- ✅ **PHPMailer Integration (Fixed)**
- ✅ **Admin Booking Management (Fixed)**
- ✅ **JavaScript Error Handling (Enhanced)**
- ✅ **Vendor Folder Configuration (Resolved)**
- ✅ **Professional Email System**
- ✅ **8 HTML Email Templates**
- ✅ **SMTP Email Integration (Gmail)**
- ✅ **Separate Discount Approval System**
- ✅ **Discount Status Tracking**
- ✅ **Email Notifications for All Status Changes**
- ✅ **Blue Theme Email Design**
- ✅ **Embedded Logo in Emails**
- ✅ **Email Testing Tools**


## 🚀 Deployment & DevOps

### Docker Hub Registry
- **Image**: `your-dockerhub-username/barcie:latest`
- **Auto-build**: Triggered on main branch commits
- **Registry**: Docker Hub public repository

### CI/CD Pipeline
- **GitHub Actions**: Automated build and push to Docker Hub
- **Triggers**: Push to main branch, pull requests, manual dispatch
- **Build Environment**: Ubuntu latest with Docker
- **Security**: Docker Hub credentials stored as GitHub secrets

### Production Deployment Options

1. **Docker Hub Pull**
   ```bash
   docker pull your-dockerhub-username/barcie:latest
   docker run -d -p 8080:80 your-dockerhub-username/barcie:latest
   ```

2. **Docker Compose Production**
   ```bash
   # Use production docker-compose.yml
   docker-compose -f docker-compose.prod.yml up -d
   ```

3. **Manual Build**
   ```bash
   docker build -t barcie-php .
   docker run -d -p 8080:80 barcie-php
   ```

## 🎓 Academic Information

**Capstone Project Details**
- **Course**: BSIT 4B
- **Institution**: [Redacted / Educational Institution]
- **Project Type**: Capstone Project
- **Start Date**: September 1, 2025

### Team Members
- See repository contributors for team details. Personal names were removed from this public README to protect privacy.

### Technical Specifications
- **Frontend**: HTML5, CSS3, Tailwind CSS, JavaScript
- **Backend**: PHP, MySQL, JSON
- **Database**: XAMPP (MySQL)
- **Development Tools**: Browser Sync, Git

## 📝 License

This project is developed as part of a capstone project for educational purposes at LCUP (La Consolacion University Philippines).

## 👥 Contributors

See the repository contributors list and commit history for contributor names. Do not publish private personal contact details here.

## 🔄 Version History

- **v2.3.0** - Professional Email System & Discount Management (October 2025)
  - **Professional Email Templates**: Implemented 8 beautifully designed HTML email templates
  - **Email System Features**:
    - Blue gradient header with embedded BarCIE logo (base64)
    - Status-specific color coding and badges
    - Responsive design for all email clients
    - SMTP integration via PHPMailer and Gmail
    - Automatic email sending on all booking/discount status changes
  - **Separate Discount Approval System**:
    - Independent discount approval/rejection workflow
    - Discount status tracked separately from booking status
    - Dual-action buttons in admin dashboard
    - Separate email notifications for discount decisions
  - **Email Templates**:
    - Booking Confirmation (Yellow pending badge)
    - Booking Approved (Green success)
    - Booking Rejected (Red with empathy)
    - Check-in Confirmed (Cyan welcome)
    - Check-out Complete (Purple thank you)
    - Booking Cancelled (Orange warning)
    - Discount Approved (Green celebration)
    - Discount Rejected (Red with standard rate info)
  - **Testing Tools**: Added test_email.php and test_booking_email.php for email verification
  - **Documentation**: Created EMAIL_TROUBLESHOOTING.md, EMAIL_BLUE_THEME_UPDATE.md, DASHBOARD_DISCOUNT_UPDATE.md
  - **Database Enhancement**: Added discount_status column to bookings table
  - **UI Improvements**: Enhanced dashboard with separate discount management section

- **v2.2.0** - System Stability & Bug Fixes (October 2025)
  - **Fixed Vendor Folder Issues**: Resolved PHPMailer loading errors by implementing proper Composer autoloader usage
  - **Enhanced Admin Booking Management**: Fixed JavaScript errors preventing admin from updating booking statuses
  - **Improved Error Handling**: Added comprehensive try-catch blocks and validation throughout JavaScript functions
  - **Updated Booking Details Modal**: Corrected table column mapping for proper data display (9 columns vs 6)
  - **Enhanced Filter Functions**: Improved booking filter functionality with robust error handling
  - **Composer Integration**: Properly configured dependency management for PHPMailer and PSR-3 logging
  - **Code Quality Improvements**: Fixed linting issues and added proper code formatting
  - **Documentation Updates**: Enhanced troubleshooting section with vendor-specific solutions

- **v2.0.0** - Docker deployment and CI/CD integration
  - Added Docker containerization with PHP 8.2-Apache
  - Implemented Docker Compose for multi-container setup
  - GitHub Actions CI/CD pipeline for automated builds
  - Environment variable configuration support
  - Docker Hub registry integration
  - Enhanced database connection with environment variables

- **v1.0.0** - Initial release with core functionality
  - Authentication system with dual login
  - Room and booking management
  - Admin dashboard with calendar integration
  - Guest portal with responsive design
  - MySQL database with complete schema
  - UI/UX with dark mode support

---

**Built with ❤️ for BarCIE International Center**

*Barasoain Center for Innovative Education - Your gateway to hospitality excellence*

## 🔔 Recent Updates (2025-11-16)

- **Sensitive data redacted**: Removed or replaced direct personal contact details (phone numbers, personal emails, street addresses) from this public `README.md` to protect privacy.
- **Configuration placeholders**: Replaced embedded SMTP and test-email examples with neutral placeholders (e.g. `smtp_user@example.com`, `SMTP_PASSWORD_PLACEHOLDER`, `YOUR_EMAIL@example.com`) and recommended using environment variables for credentials.
- **Admin credentials sanitized**: Replaced the sample admin insertion (`admin/admin123`) with a placeholder and guidance to store hashed passwords (example: `INSERT INTO admins (username, password) VALUES ('ADMIN_USERNAME', 'ADMIN_PASSWORD_HASH');`).
- **Docker Hub username anonymized**: Replaced the personal Docker Hub image references with `your-dockerhub-username/barcie:latest` in examples.
- **Contributors & team names**: Removed personal contributor names from the public README and pointed readers to the repository contributors list instead.
- **Security recommendation**: Encourage scanning the repository for further secrets (env files, SQL dumps) and using `.env` + `.gitignore`, secret management, or tools like `git-secrets`.

If you'd like, I can run a repo-wide scan for remaining secrets (API keys, emails, phone numbers) and either automatically redact them or produce a report.

