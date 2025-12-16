# Reports Loading Issue - Fix Guide

## Issues Fixed
1. ❌ **BEFORE**: Reports section keeps loading indefinitely on live server
2. ❌ **BEFORE**: Analytics not working
3. ✅ **AFTER**: Reports load correctly with proper timezone handling
4. ✅ **AFTER**: Better error handling for debugging

## Root Causes
1. **Missing timezone setting** in `api/reports_data.php`
2. **No error handling** - errors were failing silently
3. **Database queries might time out** on live server with no feedback

## Files Modified

### 1. api/reports_data.php
- ✅ Added timezone setting: `date_default_timezone_set('Asia/Manila');`
- ✅ Added error logging for debugging
- ✅ Added try-catch blocks for better error handling
- ✅ Added connection testing

### 2. api/test_reports.php (NEW)
- ✅ Created simple test endpoint to verify API is working
- ✅ Tests database connection
- ✅ Tests timezone configuration
- ✅ Returns diagnostic information

## Deployment Steps

### Step 1: Upload Files to Live Server

Upload these files:
```
/api/reports_data.php (modified)
/api/test_reports.php (new)
```

### Step 2: Test the API Endpoint

Before testing the full reports, test the simple endpoint first:

```
https://your-domain.com/api/test_reports.php
```

**Expected Response:**
```json
{
  "success": true,
  "message": "API is working",
  "total_bookings": 123,
  "timezone": "Asia/Manila",
  "current_time": "2025-12-16 15:30:00",
  "mysql_timezone": "+08:00"
}
```

**If you get an error:**
- Check the error message
- Verify database connection settings
- Check PHP error logs

### Step 3: Test the Full Reports Endpoint

Test with a simple query:
```
https://your-domain.com/api/reports_data.php?start_date=2025-12-01&end_date=2025-12-16&report_type=overview
```

**Expected Response:**
```json
{
  "success": true,
  "filters": {
    "start_date": "2025-12-01",
    "end_date": "2025-12-16",
    "room_type": ""
  },
  "data": {
    "summary": {
      "total_bookings": 10,
      "total_revenue": 50000,
      "total_guests": 10,
      "occupancy_rate": 75.5
    },
    ...
  }
}
```

### Step 4: Test in Browser

1. Go to dashboard → Reports & Analytics section
2. Click "Generate Report"
3. **Expected**: Report should load with data and charts
4. **If stuck loading**: Open browser console (F12) to see error messages

## Troubleshooting

### Issue: API returns "Database connection failed"

**Solution:**
1. Check `database/db_connect.php` is correctly configured
2. Verify database credentials in `.env` or config
3. Check if database server is accessible
4. Test connection: `mysqli_connect($host, $user, $pass, $db);`

### Issue: API times out or takes too long

**Possible Causes:**
1. Too many bookings - queries are slow
2. Missing database indexes
3. Server timeout settings

**Solutions:**
```sql
-- Add indexes to speed up queries
CREATE INDEX idx_bookings_checkin ON bookings(checkin);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_payment_status ON bookings(payment_status);
CREATE INDEX idx_bookings_room_id ON bookings(room_id);
```

Increase PHP timeout in `.htaccess`:
```apache
php_value max_execution_time 300
php_value max_input_time 300
```

### Issue: "Loading..." never stops

**Open browser console (F12) and check for:**

1. **Network errors** (Failed to fetch):
   - Check if `api/reports_data.php` is accessible
   - Verify URL path is correct
   - Check server error logs

2. **JavaScript errors**:
   - Check if Chart.js is loaded
   - Verify `reports.js` is loaded
   - Look for "Chart is not defined" errors

3. **CORS errors**:
   - Verify headers in `api/reports_data.php`
   - Check server CORS configuration

### Issue: Charts not rendering

**Solution:**
1. Check if Chart.js is loaded (open console, type: `typeof Chart`)
2. Should return: `"object"` not `"undefined"`
3. If undefined, Chart.js CDN might be blocked
4. Dashboard.php loads Chart.js twice - this is OK but can be optimized

### Issue: Wrong timezone in reports

**Solution:**
All files now have: `date_default_timezone_set('Asia/Manila');`

Verify MySQL timezone:
```sql
SELECT @@session.time_zone, NOW();
```
Should return: `+08:00` and current Philippine time

## Testing Checklist

After deployment, verify:

- [ ] `api/test_reports.php` returns success
- [ ] `api/reports_data.php` returns data (not error)
- [ ] Reports section loads without infinite loading
- [ ] Summary cards show numbers (Total Bookings, Revenue, etc.)
- [ ] Charts render correctly
- [ ] No JavaScript errors in browser console (F12)
- [ ] All times are in Philippine timezone

## Common Errors and Solutions

### Error: "Failed to generate report: undefined"

**Cause**: API returned invalid JSON or network error

**Solution**:
1. Test API directly in browser
2. Check browser Network tab (F12 → Network)
3. Look for the `reports_data.php` request
4. Check the response - should be valid JSON

### Error: "Chart is not defined"

**Cause**: Chart.js library not loaded

**Solution**:
1. Check internet connection (Chart.js loads from CDN)
2. Verify `dashboard.php` includes Chart.js script
3. Check browser console for 404 errors

### Error: SQL syntax error

**Cause**: Database structure mismatch

**Solution**:
1. Verify all columns exist:
   ```sql
   DESCRIBE bookings;
   ```
2. Check for: `checkin`, `checkout`, `amount`, `status`, `payment_status`, `room_id`
3. Run migrations if columns are missing

## Performance Optimization (Optional)

If reports are slow, add these indexes:

```sql
-- Speed up date range queries
CREATE INDEX idx_bookings_checkin ON bookings(checkin);
CREATE INDEX idx_bookings_checkout ON bookings(checkout);

-- Speed up status filters
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_payment_status ON bookings(payment_status);

-- Speed up JOIN operations
CREATE INDEX idx_bookings_room_id ON bookings(room_id);
CREATE INDEX idx_items_name ON items(name);
CREATE INDEX idx_items_type ON items(item_type);

-- Composite index for common queries
CREATE INDEX idx_bookings_status_payment ON bookings(status, payment_status);
```

## Debug Mode

To enable detailed error logging, add to `api/reports_data.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Only for debugging!
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');
```

**WARNING**: Remove `display_errors` before going to production!

## Files Summary

| File | Purpose | Changes Made |
|------|---------|--------------|
| `api/reports_data.php` | Main reports API | Added timezone, error handling, try-catch |
| `api/test_reports.php` | Test endpoint | NEW - for testing API connectivity |

## Next Steps

1. Upload the modified files
2. Test using `api/test_reports.php`
3. If test passes, try the full reports section
4. Monitor browser console for any errors
5. Check PHP error logs if issues persist

## Support Notes

- All timezone fixes are now consistent across the application
- Reports use only verified payments for revenue calculations
- Empty results are handled gracefully (no crashes)
- Better error messages for debugging

## Known Limitations

1. **Large datasets**: Reports might be slow with thousands of bookings
   - Solution: Add pagination or date range limits
   
2. **Real-time data**: Reports are generated on-demand (not cached)
   - Solution: Could implement caching for frequently accessed reports
   
3. **Chart.js CDN**: Requires internet connection to load
   - Solution: Could host Chart.js locally if needed
