# ⏰ AUTO-CHECKOUT SYSTEM - QUICK START GUIDE

## 🚀 Installation (Run Once)

### Option 1: Automatic Setup (Recommended)
1. Right-click `setup_auto_checkout.bat`
2. Select **"Run as administrator"**
3. Done! ✅

### Option 2: Manual Command
```bash
schtasks /create /tn "BarCIE_Auto_Checkout" /tr "C:\xampp\php\php.exe -f C:\xampp\htdocs\barcie_php\cron\auto_checkout.php" /sc hourly /st 00:00 /f
```

---

## 📋 What It Does

### 1. Sends Reminder Emails ⏰
- **When**: 1 hour before checkout time
- **Who**: Guests with `checked_in` status
- **Content**: Friendly reminder with booking details

### 2. Auto-Checkout ✅
- **When**: Checkout time is reached
- **What**: Changes status from `checked_in` → `checked_out`
- **Email**: Sends thank you + checkout confirmation

---

## 🧪 Testing

### Test Now (Command Line)
```bash
C:\xampp\php\php.exe -f "C:\xampp\htdocs\barcie_php\cron\auto_checkout.php"
```

### Test via Web Browser
Visit: `http://localhost/barcie_php/test_auto_checkout.php`

---

## 📊 Monitor System

**Dashboard**: http://localhost/barcie_php/test_auto_checkout.php

Shows:
- ✅ Task scheduler status
- 📈 Statistics (upcoming checkouts, pending reminders, overdue)
- 📝 Recent activity logs
- 🔍 Detailed upcoming checkouts list

---

## 🔧 Manage Task

### View Status
```bash
schtasks /query /tn "BarCIE_Auto_Checkout"
```

### Run Manually Now
```bash
schtasks /run /tn "BarCIE_Auto_Checkout"
```

### Disable
```bash
schtasks /change /tn "BarCIE_Auto_Checkout" /disable
```

### Enable
```bash
schtasks /change /tn "BarCIE_Auto_Checkout" /enable
```

### Delete/Remove
```bash
schtasks /delete /tn "BarCIE_Auto_Checkout" /f
```

---

## 📝 Logs Location
`C:\xampp\htdocs\barcie_php\logs\auto_checkout.log`

---

## ⚙️ Configuration

### Email Settings
Edit `.env` file or `database/mail_config.php`:
```
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_PORT=587
SMTP_SECURE=tls
FROM_EMAIL=your-email@gmail.com
```

### Timing
Script runs: **Every 1 hour** (can be changed in Task Scheduler)

---

## 🐛 Troubleshooting

### ❌ Task Not Running
- Verify paths in Task Scheduler
- Check if PHP path is correct: `C:\xampp\php\php.exe`
- Run as administrator

### ❌ Emails Not Sending
- Check SMTP credentials
- Review `logs/auto_checkout.log`
- Test email configuration manually

### ❌ No Reminders
- Ensure bookings have email in `details` field
- Check `reminder_sent` column is 0
- Verify checkout times are in future

---

## 📧 Email Requirements

For guests to receive emails, their booking must have:
1. Valid email address in the `details` field
2. Status = `checked_in`
3. Checkout time in the future

---

## ✨ Features

✅ Automatic checkout at scheduled time  
✅ Email reminder 1 hour before checkout  
✅ Professional HTML email templates  
✅ Activity logging  
✅ Web-based monitoring dashboard  
✅ Manual test capability  
✅ Prevents duplicate reminders  

---

## 📞 Support

- View logs: `C:\xampp\htdocs\barcie_php\logs\auto_checkout.log`
- Monitor dashboard: `http://localhost/barcie_php/test_auto_checkout.php`
- Documentation: `cron/README.md`

---

**🎉 Your automated checkout system is ready to use!**
