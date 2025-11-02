# Email Setup Checklist for Live Server

## ✅ Pre-Deployment (Already Done)
- [x] `.htaccess` created to protect `.env`
- [x] `.htaccess` pushed to GitHub
- [x] `.env` in `.gitignore` (keeps credentials secret)

---

## 📋 Deploy to Live Server

### Step 1: Pull Latest Code
```bash
# SSH into your live server, then:
cd /path/to/your/project
git pull origin test
```

Or use your hosting control panel to pull from GitHub.

---

### Step 2: Create `.env` File on Live Server

**Option A: Using cPanel/Plesk File Manager**
1. Log into cPanel/Plesk
2. Go to File Manager
3. Navigate to your project root (where `index.php` is)
4. Click "New File" and name it `.env`
5. Edit the file and paste this content:

```ini
# SMTP / Mail settings
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=pc.clemente11@gmail.com
SMTP_PASSWORD=bwjnpxglrmlsurwg
SMTP_PORT=587
SMTP_SECURE=tls
FROM_EMAIL=barcieinternationalcenter@gmail.com
FROM_NAME="Barcie International Center"

# Password to protect test pages
TEST_PAGE_PASSWORD=prynce0711

# OpenAI API Key
OPENAI_API_KEY=AIzaSyAAaTeSWW_5BSPldjOMzzQsDeJ5oh1HHII
```

6. Save and close

**Option B: Using FTP/SFTP (FileZilla, WinSCP)**
1. Connect to your server
2. Navigate to project root
3. Upload your local `.env` file from `c:\xampp\htdocs\barcie_php\.env`

**Option C: Using SSH/Terminal**
```bash
cd /path/to/your/project
nano .env
# Paste the content above, save (Ctrl+O, Enter, Ctrl+X)
chmod 600 .env  # Set proper permissions
```

---

### Step 3: Verify `.env` is Protected

Open browser and try to access:
```
https://your-domain.com/.env
```

**Expected result:** You should see **403 Forbidden** or **404 Not Found**

❌ **If you can see the file contents**, the `.htaccess` isn't working. Contact your host.

---

### Step 4: Test Email Configuration

Visit:
```
https://your-domain.com/test_email.php
```

1. Enter password: `prynce0711`
2. Check if all variables show "SET" (not "NOT SET")
3. Enter your email address
4. Click "Send Test Email"
5. Check your inbox (and spam folder)

**✅ Success:** You receive the test email
**❌ Failed:** Check the error message and see troubleshooting below

---

### Step 5: Test Booking with Email

1. Go to guest booking page
2. Make a test reservation
3. Check if confirmation email is sent
4. Check server error logs if email fails:
   - cPanel: Go to Metrics → Errors
   - SSH: `tail -f /var/log/apache2/error.log` or `/var/log/php-fpm/error.log`

---

## 🔧 Troubleshooting

### Email not sending?

1. **Check PHP error logs** on live server
2. **Verify Gmail App Password** is correct (not regular password)
3. **Check if port 587 is open** on your server
4. **Try port 465 with SSL** instead:
   ```ini
   SMTP_PORT=465
   SMTP_SECURE=ssl
   ```
5. **Check Gmail account** - ensure "Less secure app access" or App Passwords are enabled
6. **Contact hosting provider** - some hosts block outgoing SMTP

### .env variables showing "NOT SET"?

1. Verify `.env` file exists in project root
2. Check file permissions: `ls -la .env`
3. Verify `vendor/autoload.php` exists (run `composer install` if missing)
4. Check if Dotenv is installed: `composer show vlucas/phpdotenv`

### 403 Forbidden on whole site?

The `.htaccess` rules might be too strict. Edit and comment out:
```apache
# <IfModule mod_rewrite.c>
#     RewriteEngine On
#     RewriteRule ^vendor/.*$ - [F,L]
#     RewriteRule ^database/.*\.(php|sql)$ - [F,L]
# </IfModule>
```

---

## 📞 Support

If emails still don't work after following all steps:
1. Check server PHP version (needs PHP 7.4+)
2. Verify `php-mbstring` extension is installed
3. Contact your hosting provider about SMTP restrictions
4. Share error logs for further diagnosis

---

## 🔐 Security Notes

- **Never commit `.env` to GitHub** (already in `.gitignore` ✅)
- **Change TEST_PAGE_PASSWORD** after testing
- **Use different credentials** for staging vs production
- **Backup `.env`** securely (password manager, encrypted backup)
