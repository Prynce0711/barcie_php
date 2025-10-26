# Page State Management System - User Guide

## 🎯 Overview

Your website now has a **smart redirect system** that:
- ✅ Remembers where you were when you refresh the page
- ✅ Admin always starts at **Dashboard** section on login
- ✅ Guest always starts at **Overview** section on login  
- ✅ Supports browser back/forward buttons
- ✅ Works with URL hashes (bookmarkable sections)

## 🔧 How It Works

### For Admins:
1. **Login** → Redirects to `dashboard.php#dashboard`
2. **Navigate** → Click any sidebar link (Dashboard, Calendar, Rooms, Bookings)
3. **Refresh** → Page returns to the section you were viewing
4. **Logout** → Clears saved state, returns to landing page

### For Guests:
1. **Open Guest Portal** → Starts at `Guest.php#overview`
2. **Navigate** → Click any sidebar link (Overview, Rooms, Booking, Feedback)
3. **Refresh** → Page returns to the section you were viewing

## 📍 Section IDs

### Admin Dashboard Sections:
- `dashboard` - Main dashboard (DEFAULT on login)
- `calendar` - Calendar view
- `rooms` - Room management
- `bookings` - Booking management

### Guest Portal Sections:
- `overview` - Overview page (DEFAULT on first visit)
- `availability` - Check availability
- `rooms` - Browse rooms
- `booking` - Make a booking
- `feedback` - Submit feedback

## 🔗 URL Structure

You can bookmark or share specific sections:

**Admin:**
- http://localhost/barcie_php/dashboard.php#dashboard
- http://localhost/barcie_php/dashboard.php#calendar
- http://localhost/barcie_php/dashboard.php#rooms
- http://localhost/barcie_php/dashboard.php#bookings

**Guest:**
- http://localhost/barcie_php/Guest.php#overview
- http://localhost/barcie_php/Guest.php#rooms
- http://localhost/barcie_php/Guest.php#booking
- http://localhost/barcie_php/Guest.php#feedback

## 💻 Developer API

The state manager exposes a global API at `window.BarcieStateManager`:

```javascript
// Navigate to a section programmatically
BarcieStateManager.navigate('dashboard');

// Get current section
const currentSection = BarcieStateManager.getCurrentSection();

// Handle login (sets default section)
BarcieStateManager.handleLoginSuccess('admin'); // or 'guest'

// Handle logout (clears state)
BarcieStateManager.handleLogout();

// Get saved state
const state = BarcieStateManager.getState();

// Clear saved state
BarcieStateManager.clearState();
```

## 🐛 Troubleshooting

### Section not showing after refresh:
1. Check browser console for errors
2. Verify section ID matches one of the defined sections
3. Clear browser cache and try again

### Default section not working:
1. Check that page-state-manager.js is loaded before other scripts
2. Verify sessionStorage is enabled in browser
3. Check console for initialization messages

### URL hash not updating:
1. Ensure navigation links use `href="#section-name"`
2. Check that click handlers aren't preventing default behavior

## 📝 Files Modified

- `src/assets/js/page-state-manager.js` - Core state management logic
- `src/assets/css/page-state.css` - Section visibility styles
- `dashboard.php` - Added state manager script
- `Guest.php` - Added state manager script (via footer)
- `src/assets/js/landing/auth.js` - Admin login integration
- `src/components/guest/footer.php` - Added state manager script

## ✅ Testing Checklist

- [ ] Admin login redirects to dashboard section
- [ ] Guest opens at overview section
- [ ] Refreshing page maintains current section
- [ ] Browser back button works
- [ ] Sidebar navigation updates active state
- [ ] URL hash changes when navigating
- [ ] Logout clears saved state
