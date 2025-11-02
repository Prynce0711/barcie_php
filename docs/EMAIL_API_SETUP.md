# API-Based Email Setup Guide

## Why Use Email API Instead of SMTP?

### ✅ Benefits:
- **No password exposure** - Only API keys (easier to rotate)
- **Better deliverability** - Professional email services have better reputation
- **No SMTP port blocks** - Works on any hosting
- **Analytics** - Track opens, clicks, bounces
- **Higher limits** - SendGrid: 100/day free vs Gmail: ~500/day total
- **No 2FA issues** - Gmail app passwords can break

---

## Quick Setup Options

### Option 1: SendGrid (Recommended - Easiest)

**Free Tier:** 100 emails/day forever

1. **Sign up:** https://signup.sendgrid.com/
2. **Verify your email**
3. **Get API Key:**
   - Go to Settings → API Keys
   - Click "Create API Key"
   - Name: `BarCIE_Production`
   - Permission: Full Access (or just Mail Send)
   - Copy the key (starts with `SG.`)

4. **Update `.env`:**
```ini
MAIL_METHOD=sendgrid
SENDGRID_API_KEY=SG.your-api-key-here
FROM_EMAIL=barcieinternationalcenter@gmail.com
FROM_NAME=Barcie International Center
```

5. **No other credentials needed!** ✅

---

### Option 2: Mailgun

**Free Tier:** 100 emails/day for 3 months, then pay-as-you-go

1. **Sign up:** https://signup.mailgun.com/
2. **Verify domain** (or use sandbox domain for testing)
3. **Get API Key:**
   - Go to Settings → API Keys
   - Copy the Private API Key

4. **Update `.env`:**
```ini
MAIL_METHOD=mailgun
MAILGUN_API_KEY=your-private-api-key
MAILGUN_DOMAIN=sandboxXXX.mailgun.org
FROM_EMAIL=barcieinternationalcenter@gmail.com
FROM_NAME=Barcie International Center
```

---

### Option 3: Keep SMTP but Use Server Environment Variables

**Don't store credentials in `.env` at all:**

1. **Set in cPanel/Plesk:**
   - Go to PHP Configuration or Environment Variables
   - Add: `SMTP_USERNAME`, `SMTP_PASSWORD`, etc.

2. **Or set in Apache VirtualHost:**
```apache
SetEnv SMTP_USERNAME "pc.clemente11@gmail.com"
SetEnv SMTP_PASSWORD "bwjnpxglrmlsurwg"
```

3. **Remove `.env` file completely** - Code will read from server environment

---

## Migration Steps

### Step 1: Choose Your Method
- **SendGrid** = Easiest, best free tier
- **Mailgun** = More features, better for scale
- **Server Env Vars** = Keep current SMTP, just move credentials

### Step 2: Update Configuration

**Create `.env` with API method:**
```ini
# Choose method: sendgrid, mailgun, or smtp
MAIL_METHOD=sendgrid

# SendGrid
SENDGRID_API_KEY=SG.your-key-here

# OR Mailgun
# MAILGUN_API_KEY=key-your-key-here
# MAILGUN_DOMAIN=mg.yourdomain.com

# Sender info (required for all methods)
FROM_EMAIL=barcieinternationalcenter@gmail.com
FROM_NAME=Barcie International Center

# Only needed if using SMTP fallback
# SMTP_HOST=smtp.gmail.com
# SMTP_USERNAME=pc.clemente11@gmail.com
# SMTP_PASSWORD=bwjnpxglrmlsurwg
# SMTP_PORT=587
# SMTP_SECURE=tls
```

### Step 3: Update `user_auth.php`

Replace the `send_smtp_mail` function call with the new universal sender.

**Find this line in `user_auth.php`:**
```php
$mail_sent = send_smtp_mail($email, $subject, $emailBody);
```

**Replace with:**
```php
require_once __DIR__ . '/mail_sender_api.php';
$mail_sent = send_email_universal($email, $subject, $emailBody);
```

### Step 4: Test

1. Visit `http://localhost/barcie_php/test_email.php`
2. Send test email
3. Check if it arrives

### Step 5: Deploy to Live

1. Upload `.env` with API key (or set environment variables)
2. Upload new code files
3. Test on live server

---

## Comparison Table

| Feature | Gmail SMTP | SendGrid API | Mailgun API | Server Env Vars |
|---------|-----------|--------------|-------------|-----------------|
| **Free Tier** | 500/day | 100/day forever | 100/day (3 months) | Same as SMTP |
| **Setup Difficulty** | Medium | Easy | Easy | Easy |
| **Deliverability** | Good | Excellent | Excellent | Good |
| **Port Blocks** | Can be blocked | Never blocked | Never blocked | Can be blocked |
| **Credentials** | Password in `.env` | API key only | API key only | Server config |
| **Security** | ⚠️ Medium | ✅ High | ✅ High | ✅ High |
| **Analytics** | ❌ No | ✅ Yes | ✅ Yes | ❌ No |
| **Recommended** | For testing | ✅ Production | Production | Production |

---

## My Recommendation

**For your use case (BarCIE booking system):**

1. **Use SendGrid API** for production
   - Free 100 emails/day is plenty for bookings
   - More reliable than Gmail SMTP
   - Better security (just API key, not password)
   - Professional email analytics

2. **Keep SMTP as fallback** in code
   - If API fails, automatically falls back to SMTP
   - Best of both worlds

3. **Use server environment variables** for API keys
   - Even better than `.env` file
   - No risk of accidental commit

---

## Need Help Setting Up?

Tell me which method you want and I'll:
1. Update the code to use it
2. Give you exact steps
3. Help you test it

**Quick vote:** Which do you prefer?
- A) SendGrid (easiest, best free tier)
- B) Mailgun (more features)
- C) Keep SMTP, just move to server env vars
- D) Something else
