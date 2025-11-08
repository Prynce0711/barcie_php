# Room Images Upgrade - Multiple Images with Navigation & Zoom

## 🎯 Overview
This upgrade adds support for **multiple images per room/facility** with:
- ✅ Upload up to 10 images per room
- ✅ Left/Right navigation arrows on cards
- ✅ Full-screen image viewer modal
- ✅ Zoom in/out controls
- ✅ Image counter (e.g., "1 / 5")
- ✅ Delete individual images during edit
- ✅ Keyboard navigation (arrows, +/-, 0 for reset)

## 📋 Installation Steps

### Step 1: Run Database Migration
You need to add the `images` column to your database. Run this script once:

```bash
# Navigate to your browser and run:
http://localhost/barcie_php/database/add_images_column.php
```

Or run directly via command line:
```bash
cd c:\xampp\htdocs\barcie_php
php database/add_images_column.php
```

### Step 2: Verify Changes
All code changes have already been applied to:
- ✅ `components/dashboard/sections/add_item_modal.php` - Multiple image upload
- ✅ `components/dashboard/sections/rooms_grid_content.php` - Image carousel & viewer
- ✅ `components/dashboard/data_processing.php` - Backend processing
- ✅ `database/fetch_items.php` - Include images in queries
- ✅ `api/items.php` - Include images in API response

## 🎨 Features

### 1. Upload Multiple Images
When adding or editing a room:
- Select multiple images (up to 10)
- Preview thumbnails with numbering
- All images are stored in JSON format

### 2. Card Image Navigation
On the rooms grid:
- Left/Right arrow buttons to navigate between images
- Image counter shows current position (e.g., "1 / 3")
- Smooth fade transitions between images

### 3. Full-Screen Viewer
Click the zoom button (🔍) to open:
- Full-screen modal with large image display
- Left/Right navigation arrows
- Zoom In/Out buttons (+/-)
- Reset zoom button (↻)
- Keyboard shortcuts:
  - `←` / `→` : Navigate images
  - `+` / `=` : Zoom in
  - `-` : Zoom out
  - `0` : Reset zoom

### 4. Edit Mode
When editing a room:
- See all current images with thumbnails
- Delete individual images with × button
- Add new images (respects 10 image limit)
- Deleted images are removed from server

## 🗂️ Database Schema

### New Column
```sql
ALTER TABLE items ADD COLUMN images TEXT NULL AFTER image;
```

### Data Format
The `images` column stores a JSON array:
```json
["uploads/1699564123_abc123.jpg", "uploads/1699564124_def456.png"]
```

### Backward Compatibility
- The old `image` column is kept for compatibility
- Existing single images are migrated to the new format
- Legacy code will still work

## 🔧 Technical Details

### File Upload Limits
- Maximum: 10 images per room
- Max file size: 20MB per image
- Allowed formats: JPG, JPEG, PNG, GIF, WebP
- Validation: MIME type and getimagesize() checks

### Image Storage
- Directory: `/uploads/`
- Naming: `timestamp_uniqueid_index.extension`
- Permissions: 0644 (read for all, write for owner)

### JavaScript Functions
```javascript
navigateImage(itemId, direction)     // Navigate carousel on card
openImageViewer(itemId, images)      // Open full-screen viewer
viewerNavigate(direction)            // Navigate in viewer
zoomImage(delta)                     // Zoom in/out
resetZoom()                          // Reset to 100%
```

## 🐛 Troubleshooting

### Images Not Uploading
1. Check PHP upload limits in `php.ini`:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 20M
   ```
2. Verify `/uploads/` directory is writable:
   ```bash
   chmod 755 c:\xampp\htdocs\barcie_php\uploads
   ```

### Images Not Displaying
1. Run the migration script if you haven't
2. Clear browser cache
3. Check browser console for errors
4. Verify images are in the database:
   ```sql
   SELECT id, name, images FROM items WHERE images IS NOT NULL;
   ```

### Old Images Not Showing
The migration script should handle this, but you can manually fix:
```sql
UPDATE items 
SET images = JSON_ARRAY(image) 
WHERE image IS NOT NULL AND image != '' AND (images IS NULL OR images = '');
```

## 📱 Mobile Responsive
All features work on mobile:
- Touch-friendly navigation buttons
- Swipe support in modal (via Bootstrap)
- Responsive image sizing

## 🚀 Future Enhancements
Possible additions:
- Image reordering (drag & drop)
- Bulk image upload via ZIP
- Image optimization/compression
- CDN integration
- Image captions/descriptions

## 📝 Notes
- Maximum 10 images is a soft limit (can be changed in code)
- Images are stored on the server, not in database
- JSON format allows easy extension for metadata
- Backward compatible with single-image rooms

---

**Questions?** Check the code comments or contact the development team.
