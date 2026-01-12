# BarCIE International Center - Hospitality Management System

A comprehensive web-based booking and management system for BarCIE International Center, featuring room reservations, catering services, event management, and administrative controls.

## 🌟 Features

### Public Features
- **Landing Page**: Modern, responsive homepage with information about the facility
- **Room Booking System**: Interactive calendar-based room reservation system
- **Catering Services**: Browse and book catering packages for events
- **Event Stylists**: Professional event styling services
- **News & Updates**: Latest announcements and news section
- **AI Chatbot**: Intelligent assistant for customer inquiries
- **Brochure Downloads**: Digital brochures and promotional materials

### Admin Features
- **Dashboard**: Comprehensive overview of bookings, revenue, and statistics
- **Booking Management**: View, edit, approve, and manage all reservations
- **Room Management**: Configure room types, pricing, and availability
- **Calendar View**: Visual representation of bookings and occupancy
- **Reports & Analytics**: Generate detailed reports (PDF/Excel exports)
- **User Management**: Admin account management with role-based access
- **News Management**: Create and publish news updates
- **Item Management**: Manage catering items and packages
- **Real-time Monitoring**: Live booking status and admin activity tracking

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Bootstrap 5.3.2
- **Icons**: Font Awesome 6.5.1
- **Animations**: AOS (Animate On Scroll)
- **PDF Generation**: DomPDF
- **Excel Export**: PhpSpreadsheet
- **Email**: PHPMailer
- **Environment Config**: vlucas/phpdotenv

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (for dependency management)
- Modern web browser (Chrome, Firefox, Safari, Edge)



## 📁 Project Structure

```
barcie_php/
├── api/                        # Backend API endpoints
│   ├── admin_management.php    # Admin user management
│   ├── availability.php        # Room availability checks
│   ├── booking.php             # Booking operations
│   ├── chatbot_answer.php      # AI chatbot responses
│   ├── items.php               # Catering items API
│   ├── news.php                # News management
│   ├── reports_data.php        # Analytics and reports
│   └── ...
├── assets/
│   ├── css/                    # Stylesheets
│   ├── images/                 # Image assets
│   └── js/                     # JavaScript files
├── components/
│   ├── dashboard/              # Admin dashboard components
│   ├── guest/                  # Guest booking components
│   └── landing/                # Landing page components
├── cron/                       # Automated tasks
│   ├── auto_checkin_checkout.php
│   └── auto_checkout.php
├── database/                   # Database utilities
│   ├── config.php              # Database configuration
│   ├── db_connect.php          # Database connection
│   ├── admin_login.php         # Admin authentication
│   ├── user_auth.php           # User authentication
│   └── modules/                # Database modules
├── scripts/                    # Utility scripts
├── uploads/                    # User uploaded files
│   └── items/                  # Catering item images
├── vendor/                     # Composer dependencies
├── admin.php                   # Admin login page
├── dashboard.php               # Admin dashboard
├── Guest.php                   # Guest booking portal
├── index.php                   # Main landing page
├── logout.php                  # Logout handler
├── composer.json               # PHP dependencies
├── .env                        # Environment configuration
└── README.md                   # This file
```

### For Customers

1. **Browse Facilities**: Visit the homepage to explore available rooms and services
2. **Make a Booking**: 
   - Navigate to the booking section
   - Select dates and room type
   - Fill in guest information
   - Submit booking request
3. **Check Availability**: Use the interactive calendar to check room availability
4. **Contact Support**: Use the chatbot or contact form for inquiries

### For Administrators

1. **Login**: Access the admin panel
2. **Dashboard**: View key metrics and recent activities
3. **Manage Bookings**:
   - Review pending bookings
   - Approve or reject requests
   - Edit booking details
   - Generate receipts and confirmations
4. **Manage Content**:
   - Update news and announcements
   - Manage room listings
   - Configure catering items
5. **Generate Reports**:
   - View analytics and statistics
   - Export reports to PDF or Excel
   - Track revenue and occupancy


### Upload Errors
- Verify folder permissions (755 for directories, 644 for files)
- Check PHP upload limits in php.ini
- Ensure uploads/ directory exists

### Email Not Sending
- Verify SMTP credentials in `.env`
- Enable "Less secure app access" for Gmail (or use App Passwords)
- Check firewall/antivirus settings

### Session Issues
- Clear browser cache and cookies
- Check PHP session configuration
- Verify session directory permissions

## 🔒 Security Features

- CSRF Protection
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- Input validation and sanitization
- Session management
- Role-based access control
- Secure file upload handling

## 📦 Dependencies

### PHP Packages (via Composer)
- `dompdf/dompdf` - PDF generation
- `phpoffice/phpspreadsheet` - Excel export
- `phpmailer/phpmailer` - Email functionality
- `vlucas/phpdotenv` - Environment configuration

### Frontend Libraries (via CDN)
- Bootstrap 5.3.2
- Font Awesome 6.5.1
- AOS Animation Library

## 🚧 Maintenance

### Regular Tasks
- Backup database regularly
- Monitor disk space for uploads
- Review and archive old bookings
- Update dependencies periodically
- Monitor error logs

### Log Files
- PHP errors: Check Apache/PHP error logs
- Application logs: Check custom log files in designated directories

## 📞 Support

For technical support or inquiries:
- **Website**: [Your website URL]
- **Email**: [Support email]
- **Phone**: [Contact number]

## 📄 License

[Specify your license here]

## 👥 Credits

Developed for BarCIE International Center

---

**Version**: 1.0.0  
**Last Updated**: December 19, 2025
