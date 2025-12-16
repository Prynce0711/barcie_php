# Deployment Guide - BarCIE Hotel Management System

## For Live Server Deployment

### Step 1: Prepare Files
1. Copy all files to your live server
2. Rename `.env.production` to `.env` (or update `.env` with production values)

### Step 2: Configure Database
Update `.env` with your live server database credentials:
```env
APP_ENV=production
APP_DEBUG=false

DB_HOST=10.20.0.2
DB_USER=root
DB_PASS=root
DB_NAME=barcie_db
DB_PORT=3306
```

### Step 3: Import Database
1. Access phpMyAdmin on your live server
2. Create database `barcie_db` (if not exists)
3. Import the SQL file from your localhost export
4. Verify all tables are created

### Step 4: Set Permissions
```bash
chmod 755 database/
chmod 755 api/
chmod 755 components/
chmod 777 uploads/
chmod 644 .env
```

### Step 5: Test Connection
Access: `http://your-server-ip/check_db.php`
This will verify database connectivity

### Step 6: Verify APIs
Test these endpoints:
- `/api/items.php` - Should return rooms/facilities
- `/api/availability.php` - Should return availability data
- `/api/health.php` - Should return server health status

## Environment Auto-Detection

The system automatically detects the environment:
- **Localhost**: Uses `127.0.0.1`, `root`, empty password
- **Live Server**: Uses values from `.env` or defaults to `10.20.0.2`

## Troubleshooting

### Database Connection Issues
1. Check `.env` file exists and has correct values
2. Verify database server IP and credentials
3. Ensure MySQL/MariaDB service is running
4. Check firewall allows database connections

### API Not Working
1. Verify all API files are uploaded
2. Check file permissions (755 for directories, 644 for files)
3. Test API endpoints directly in browser
4. Check error logs: `logs/php_errors.log`

### Email Not Sending
1. Verify SMTP credentials in `.env`
2. Check if server allows outbound SMTP connections
3. Test with a simple email script

## Important Notes

- Always backup database before deployment
- Keep `.env` file secure (add to .gitignore)
- Use HTTPS on production (SSL certificate)
- Set `APP_DEBUG=false` in production
- Monitor error logs regularly

## Quick Deployment Checklist

- [ ] Upload all files to server
- [ ] Configure `.env` with production values
- [ ] Import database from localhost
- [ ] Set correct file permissions
- [ ] Test database connection
- [ ] Test all API endpoints
- [ ] Test booking process
- [ ] Test email functionality
- [ ] Test file uploads
- [ ] Verify admin dashboard works
- [ ] Check all pages load correctly

## Support

For issues during deployment, check:
1. Server error logs
2. Browser console for JavaScript errors
3. Network tab for failed API requests
4. Database connection test results
