# Email Troubleshooting Guide

## Quick Diagnosis

### Test Your Setup NOW

1. **Open in browser**: `http://localhost/barcie_php/test_email.php?email=YOUR_EMAIL@gmail.com`
   - Replace `YOUR_EMAIL@gmail.com` with your actual email
   - This will show you exactly what's wrong

2. **Check PHP Error Log**: 
   - Open: `C:\xampp\php\logs\php_error.log`
   - Look for lines starting with "=== EMAIL ATTEMPT ==="
   - This shows all email attempts and errors

## Common Issues & Solutions

### ❌ Issue 1: "Mail config file not found"

**Problem**: `mail_config.php` is in wrong location

**Solution**:
```
File should be at: c:\xampp\htdocs\barcie_php\database\mail_config.php
NOT at: c:\xampp\htdocs\barcie_php\mail_config.php
```

**Fix**: The file is already in the correct location based on our setup.

---

### ❌ Issue 2: "SMTP connect() failed"

**Problem**: Gmail is blocking the connection

**Solution**:
1. Enable 2-Step Verification on your Gmail account
2. Generate an App Password:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and your device
   - Copy the 16-character password
3. Update `database/mail_config.php`:
   ```php
   'password' => 'xxxx xxxx xxxx xxxx', // Your App Password (16 characters)
   ```

---

### ❌ Issue 3: "Could not instantiate mail function"

**Problem**: PHP mail() function not configured

**Solution**: You're using SMTP (PHPMailer), so this shouldn't occur. If it does:
1. Check if PHPMailer is installed: `composer install`
2. Verify `vendor` folder exists

---

### ❌ Issue 4: Email sends but guest doesn't receive

**Problem**: Email goes to spam or wrong address

**Checklist**:
- [ ] Check spam/junk folder
- [ ] Verify email address is correct in booking form
- [ ] Check if email has typos (extra spaces, wrong domain)
- [ ] Gmail might delay emails by 1-5 minutes

---

### ❌ Issue 5: No error in logs, but no email

**Problem**: Email code not being executed

**Debug Steps**:

1. Make a test booking
2. Check error log for this exact sequence:
   ```
   Booking Debug - Type: reservation, Room ID: X, Receipt: BARCIE-...
   Attempting to send confirmation email to: someone@gmail.com
   === EMAIL ATTEMPT ===
   To: someone@gmail.com
   ```

3. If you DON'T see this, the booking isn't reaching the email code
4. If you DO see this, check what comes after for the error

---

## Step-by-Step Test

### Test 1: Direct Email Test

Visit: `http://localhost/barcie_php/test_email.php?email=YOUR_EMAIL@gmail.com`

**Expected Output**:
```
✓ Autoloader found
✓ Config found
✓ PHPMailer class loaded
✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓
```

**If this WORKS**: Email system is fine, issue is in booking flow
**If this FAILS**: Email configuration issue

---

### Test 2: Make a Real Booking

1. Go to: `http://localhost/barcie_php/Guest.php`
2. Fill out booking form with YOUR email address
3. Submit booking
4. Immediately check: `C:\xampp\php\logs\php_error.log`

**Look for these lines**:
```
Booking Debug - Type: reservation, Room ID: 1, Receipt: BARCIE-...
Attempting to send confirmation email to: your@email.com
=== EMAIL ATTEMPT ===
To: your@email.com
Subject: Booking Confirmation - BarCIE International Center
Config loaded successfully
SMTP Host: smtp.gmail.com
SMTP User: pc.clemente11@gmail.com
[SMTP debug output...]
Email sent successfully to: your@email.com
=== EMAIL SUCCESS ===
```

---

## Gmail App Password Setup

### Step 1: Enable 2-Step Verification
1. Go to: https://myaccount.google.com/security
2. Click "2-Step Verification"
3. Follow setup wizard

### Step 2: Generate App Password
1. Go to: https://myaccount.google.com/apppasswords
2. Select app: "Mail"
3. Select device: "Windows Computer"
4. Click "Generate"
5. Copy the 16-character password (format: xxxx xxxx xxxx xxxx)

### Step 3: Update Config
Edit `database/mail_config.php`:
```php
<?php
return [
    'host' => 'smtp.gmail.com',
    'username' => 'pc.clemente11@gmail.com',
    'password' => 'xxxx xxxx xxxx xxxx', // <-- PASTE APP PASSWORD HERE
    'secure' => 'tls',
    'port' => 587,
    'from_email' => 'pc.clemente11@gmail.com',
    'from_name' => 'Barcie International Center'
];
```

---

## Firewall Check

If SMTP connection fails, temporarily disable Windows Firewall:

1. Press Win + R
2. Type: `firewall.cpl`
3. Click "Turn Windows Defender Firewall on or off"
4. Turn off for Private networks (testing only)
5. Try sending email again
6. **Re-enable firewall after testing**

---

## Current Configuration

Your current setup:
- **SMTP Host**: smtp.gmail.com
- **Port**: 587
- **Security**: TLS
- **From Email**: pc.clemente11@gmail.com
- **Password**: cfub wwdd guow dntw (This should be an App Password)

⚠️ **IMPORTANT**: The password in your config looks like it might be an App Password (16 chars), but verify it's correct!

---

## Quick Fixes

### Fix 1: Reset Everything
```bash
cd c:\xampp\htdocs\barcie_php
composer install
```

### Fix 2: Test PHPMailer Installation
```bash
cd c:\xampp\htdocs\barcie_php
composer show phpmailer/phpmailer
```

Should show version info.

### Fix 3: Clear PHP Error Log
```bash
# Delete old log to see fresh errors
del C:\xampp\php\logs\php_error.log
# Make a booking
# Check new log
```

---

## What To Send Me If Still Not Working

1. **Output from test_email.php**
2. **Last 50 lines from**: `C:\xampp\php\logs\php_error.log`
3. **Screenshot of booking form with your email entered**
4. **Confirmation that you're using a Gmail App Password**

---

## Expected Email Flow

When a booking is created:

1. ✉️ **Guest** receives: "Booking Confirmation" email immediately
2. ✉️ **Admin** receives: "New Discount Application" (if discount applied)
3. ✉️ **Guest** receives: "Discount Approved/Rejected" (when admin decides)
4. ✉️ **Guest** receives: "Booking Approved/Rejected" (when admin decides)
5. ✉️ **Guest** receives: "Check-in Confirmed" (when checked in)
6. ✉️ **Guest** receives: "Check-out Complete" (when checked out)

All emails should arrive within 1-2 minutes max.

---

## Still Not Working?

Run this in PowerShell to check SMTP connectivity:
```powershell
Test-NetConnection -ComputerName smtp.gmail.com -Port 587
```

Should show: `TcpTestSucceeded : True`

If False, your network/firewall is blocking SMTP.
