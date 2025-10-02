# BarCIE Hotel Management System

A comprehensive PHP-based hotel management system for **Barasoain Center for Innovative Education (BarCIE)** - LCUP's Laboratory Facility for BS Tourism Management.

![BarCIE Logo](assets/images/imageBg/barcie_logo.jpg)

## 🏨 Overview

BarCIE Hotel Management System is a full-featured web application designed to streamline hotel operations including room management, booking reservations, guest services, and administrative tasks. Built specifically for educational purposes and real-world hotel management scenarios.

## ✨ Features

### 🔐 Authentication System
- **Dual Authentication**: Separate login systems for guests and administrators
- **Secure Registration**: Email validation with domain restrictions (@email.lcup.edu.ph)
- **Password Security**: Real-time validation and secure hashing
- **Session Management**: Persistent user sessions with role-based access

### 👨‍💼 Admin Dashboard
- **Interactive Calendar**: FullCalendar integration with booking visualization
- **Room & Facility Management**: CRUD operations for rooms and facilities
- **Booking Management**: Approve, reject, check-in, check-out functionality
- **User Management**: Complete user administration interface
- **Real-time Statistics**: Active bookings, pending approvals, occupancy rates
- **Dark Mode**: Toggle between light and dark themes
- **Communication Hub**: Integrated chat and WebRTC video calling

### 👤 Guest Portal
- **Room Browsing**: Filter and view available rooms and facilities
- **Dual Booking System**:
  - **Standard Reservations**: Complete guest information and stay details
  - **Pencil Bookings**: Function hall reservations for events
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
- **Status Tracking**: Real-time booking status updates
- **Receipt Generation**: Automatic receipt number generation
- **Date Validation**: Prevent double bookings and conflicts

## 🛠️ Technology Stack

### Backend
- **PHP 8.x**: Server-side logic and database interactions
- **MySQL**: Relational database for data storage
- **Session Management**: PHP sessions for authentication

### Frontend
- **HTML5 & CSS3**: Modern markup and styling
- **Tailwind CSS**: Utility-first CSS framework
- **Vanilla JavaScript**: Client-side interactivity
- **FullCalendar.js**: Calendar and event management
- **Font Awesome**: Icon library
- **Responsive Design**: Mobile-first approach

### Development Environment
- **XAMPP**: Local development server
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
│
├── 📂 database/
│   ├── 📄 db_connect.php     # Database connection
│   ├── 📄 user_auth.php      # Authentication & user management
│   ├── 📄 admin_login.php    # Admin authentication
│   └── 📄 fetch_items.php    # Room/facility data API
│
├── 📂 assets/
│   ├── 📂 css/
│   │   ├── 📄 dashboard.css  # Admin dashboard styles
│   │   └── 📄 guest.css      # Guest portal styles
│   ├── 📂 js/
│   │   ├── 📄 dashboard.js   # Admin dashboard scripts
│   │   └── 📄 guest.js       # Guest portal scripts
│   └── 📂 images/
│       ├── 📂 rooms/         # Room images
│       └── 📂 imageBg/       # Background images & logos
│
└── 📂 uploads/               # User uploaded content
    └── 📸 *.jpg              # Uploaded room/facility images
```

## 💾 Database Schema

The system uses the following main database tables:

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Admins Table
```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Items Table (Rooms & Facilities)
```sql
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    item_type ENUM('room', 'facility') NOT NULL,
    room_number VARCHAR(20),
    description TEXT,
    capacity INT DEFAULT 1,
    price DECIMAL(10,2) DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Bookings Table
```sql
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('reservation', 'pencil') NOT NULL,
    details TEXT,
    status ENUM('pending', 'confirmed', 'rejected', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    checkin DATETIME,
    checkout DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Feedback Table
```sql
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP** (Apache, MySQL, PHP)
- **Web Browser** (Chrome, Firefox, Safari)
- **Git** (optional, for cloning)

### Step-by-Step Installation

1. **Download & Install XAMPP**
   ```bash
   # Download from https://www.apachefriends.org/
   # Install with Apache and MySQL modules
   ```

2. **Clone or Download Project**
   ```bash
   # Option 1: Clone repository
   git clone https://github.com/Prynce0711/barcie_php.git
   
   # Option 2: Download ZIP and extract
   # Place in C:\xampp\htdocs\barcie_php
   ```

3. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

4. **Create Database**
   ```sql
   # Access phpMyAdmin at http://localhost/phpmyadmin
   # Create database named 'barcie_db'
   CREATE DATABASE barcie_db;
   
   # Import the SQL schema (create tables as shown above)
   ```

5. **Configure Database Connection**
   ```php
   // Verify database/db_connect.php settings
   $host = "localhost";
   $user = "root";
   $pass = "";
   $dbname = "barcie_db";
   ```

6. **Create Admin Account**
   ```sql
   # Insert admin user in the database
   INSERT INTO admins (username, password) VALUES ('admin', 'admin123');
   ```

7. **Access the Application**
   ```
   http://localhost/barcie_php/
   ```

## 🎯 Usage Guide

### For Administrators

1. **Login**: Click Admin Panel → Enter credentials (admin/admin123)
2. **Dashboard**: View statistics, calendar, and recent activities
3. **Room Management**: Add, edit, or delete rooms and facilities
4. **Booking Management**: Process reservations and manage check-ins/check-outs
5. **User Management**: View and manage registered users
6. **Communication**: Use chat and video calling features

### For Guests

1. **Registration**: Create account with valid email (@email.lcup.edu.ph)
2. **Login**: Access guest portal with credentials
3. **Browse Rooms**: Filter and view available accommodations
4. **Make Reservations**: Book rooms or function halls
5. **Manage Profile**: Update personal information
6. **Submit Feedback**: Provide service feedback

## 🔧 Configuration

### Email Validation
```javascript
// Email must end with @email.lcup.edu.ph
const emailPattern = /@email\.lcup\.edu\.ph$/;
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
   Solution: Check XAMPP MySQL service and database credentials
   ```

2. **Images Not Loading**
   ```
   Solution: Verify uploads folder permissions and file paths
   ```

3. **Session Issues**
   ```
   Solution: Check PHP session configuration and cookies
   ```

4. **Email Validation Errors**
   ```
   Solution: Ensure email follows @email.lcup.edu.ph format
   ```

## 📞 Contact Information

**BarCIE International Center**
- 📱 **Viber**: [0939 905 7425](viber://chat?number=+639399057425)
- ☎️ **Telephone**: 044 791 7424 / 044 919 8410
- 📧 **Email**: 
  - barcieinternationalcenter@gmail.com
  - barcie@lcup.edu.ph
- 📍 **Address**: Valenzuela St. Capitol View Park Subd. Brgy. Bulihan, City of Malolos, Bulacan 3000

## 📊 Development Status

- ✅ **User Authentication System**
- ✅ **Admin Dashboard**
- ✅ **Room Management**
- ✅ **Booking System**
- ✅ **Guest Portal**
- ✅ **Database Integration**
- ✅ **Responsive Design**
- 🔄 **Payment Integration** (In Progress)
- 🔄 **Advanced Reporting** (Planned)
- 🔄 **API Development** (Planned)

## 🎓 Academic Information

**Capstone Project Details**
- **Course**: BSIT 4B (Bachelor of Science in Information Technology)
- **Institution**: La Consolacion University Philippines
- **Project Type**: Capstone Project
- **Start Date**: September 1, 2025

### Team Members
- **Project Leader**: Prynce Carlo Clemente (Full Stack Developer)
- **Research Specialist**: Roxanne Gonzales

### Technical Specifications
- **Frontend**: HTML5, CSS3, Tailwind CSS, JavaScript
- **Backend**: PHP, MySQL, JSON
- **Database**: XAMPP (MySQL)
- **Development Tools**: Browser Sync, Git

## 📝 License

This project is developed as part of a capstone project for educational purposes at LCUP (La Consolacion University Philippines).

## 👥 Contributors

- **Developer**: Prynce0711
- **Institution**: La Consolacion University Philippines
- **Program**: BS Information Technology
- **Purpose**: Capstone Project

## 🔄 Version History

- **v1.0.0** - Initial release with core functionality
- **Features**: Authentication, booking system, admin dashboard
- **Database**: MySQL with complete schema
- **UI/UX**: Responsive design with dark mode

---

**Built with ❤️ for BarCIE International Center**

*Barasoain Center for Innovative Education - Your gateway to hospitality excellence*


