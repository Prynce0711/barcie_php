# 🔧 Live Server Image Fix Guide

## Problem
Images show 404 errors on live server:
```
/uploads/1761475126_68fdfa36abbd0.jpg - 404 Not Found
/uploads/1761473693_68fdf49d09203.jpg - 404 Not Found
```

## Root Causes
1. ❌ Image files not uploaded to live server
2. ❌ Wrong file/folder permissions
3. ❌ `.htaccess` not working or disabled
4. ❌ Path mismatch between code and actual file location

---

## ✅ STEP-BY-STEP FIX

### Step 1: Upload Image Files to Live Server
**Action:** Use FTP/SFTP or your hosting control panel to upload the `uploads/` folder

**Files to upload:**
```
uploads/
├── .htaccess                          (REQUIRED)
├── 1761310555_atis3.jpg
├── 1761473693_68fdf49d09203.jpg
├── 1761475126_68fdfa36abbd0.jpg
└── 1761477242_68fe027a02cd3.jpg
```

**Verify:** Make sure ALL image files from localhost are on the live server

---

### Step 2: Set Correct Permissions

**Via FTP/File Manager:**
- `uploads/` folder: `755` (rwxr-xr-x)
- `.htaccess` file: `644` (rw-r--r--)
- All `.jpg` files: `644` (rw-r--r--)

**Via SSH:**
```bash
chmod 755 uploads/
chmod 644 uploads/.htaccess
chmod 644 uploads/*.jpg
```

---

### Step 3: Test Image Access

**Option A: Run Diagnostic Script**
1. Upload `uploads/test-access.php` to live server
2. Visit: `https://your-domain.com/uploads/test-access.php`
3. Check if images load
4. **DELETE `test-access.php` after testing!**

**Option B: Direct Image Test**
Visit this URL in browser:
```
https://barcie-test.safehub-lcup.uk/uploads/1761475126_68fdfa36abbd0.jpg
```

**Expected:** Image should display
**If 404:** Image file is missing from server
**If 403:** Permission issue

---

### Step 4: Verify .htaccess is Working

**Test if `.htaccess` is enabled:**
1. Visit: `https://your-domain.com/uploads/`
2. **Expected:** 403 Forbidden (directory listing disabled)
3. **If you see file listing:** `.htaccess` is NOT working

**If .htaccess is disabled:**
Contact your hosting provider or add to main Apache config:
```apache
<Directory /path/to/uploads>
    AllowOverride All
</Directory>
```

---

### Step 5: Check Database Paths

**Run this SQL query to check image paths:**
```sql
SELECT id, name, image FROM items WHERE image IS NOT NULL;
```

**Expected paths:** (relative, no leading slash)
```
uploads/1761475126_68fdfa36abbd0.jpg
uploads/1761473693_68fdf49d09203.jpg
```

**NOT:**
```
/uploads/...              ❌ (has leading slash)
C:/xampp/htdocs/...       ❌ (local file path)
http://localhost/...      ❌ (absolute URL)
```

---

## 🧪 Quick Test Checklist

- [ ] Upload `uploads/` folder to live server
- [ ] Set folder permissions to `755`
- [ ] Set file permissions to `644`
- [ ] Upload `.htaccess` to uploads folder
- [ ] Test direct image URL in browser
- [ ] Check database paths are relative
- [ ] Clear browser cache
- [ ] Test admin dashboard image display
- [ ] Test guest portal image display

---

## 🐛 Still Not Working?

### Check Apache Modules
Your server needs these enabled:
- `mod_rewrite` (for .htaccess)
- `mod_headers` (for CORS if cross-domain)
- `mod_mime` (for proper image MIME types)

### Check Server Logs
Look for errors in:
- Apache error log
- PHP error log
- Browser console (F12)

### Common Issues:

**Issue:** Images work on localhost but not live server
**Fix:** Upload the actual image files!

**Issue:** 403 Forbidden
**Fix:** Check file/folder permissions (755/644)

**Issue:** 404 Not Found
**Fix:** Verify file exists on server at correct path

**Issue:** Images show as broken
**Fix:** Check MIME types and file corruption

---

## 📝 After Fixing

1. ✅ Delete `uploads/test-access.php` (security risk!)
2. ✅ Test uploading NEW images via admin dashboard
3. ✅ Verify new images display immediately
4. ✅ Delete this guide if desired

---

## 🔒 Security Notes

- ✅ `.htaccess` prevents PHP execution in uploads/
- ✅ Directory listing is disabled
- ✅ Only image files (.jpg, .png, etc.) are accessible
- ⚠️ Never set permissions to `777` (security risk!)

