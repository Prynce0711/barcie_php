# Assets Directory

This directory contains all static assets used throughout the BarCIE International Center website, including stylesheets, images, and JavaScript files.

## 📁 Directory Structure

```
assets/
├── css/          # Stylesheets
├── images/       # Image assets
└── js/           # JavaScript files
```

## 🎨 CSS Folder

Contains all stylesheet files for different sections and features of the website.

### Core Stylesheets

- **`landing-page.css`** - Main landing page styles, hero sections, animations
- **`dashboard.css`** - Admin dashboard base styles
- **`guest.css`** - Guest booking portal base styles

### Feature-Specific Styles

- **`caterings.css`** - Catering services section styling
- **`news.css`** - News and announcements section styles
- **`chatbot.css`** - AI chatbot widget styles
- **`reports.css`** - Reports and analytics section styles

### Enhancement Styles

- **`dashboard-enhancements.css`** - Additional dashboard UI improvements
- **`guest-enhanced.css`** - Enhanced guest portal features
- **`guest-inline.css`** - Inline guest portal styles
- **`admin-online-status.css`** - Admin online status indicators
- **`page-state.css`** - Page state management styles
- **`mobile-responsive.css`** - Mobile responsiveness adjustments
- **`mobile-ux-extras.css`** - Additional mobile UX enhancements

## 🖼️ Images Folder

Organized image assets for different sections of the website.

### Subdirectories

- **`rooms/`** - Room type images and galleries
- **`about/`** - About section images (facility photos, team photos)
- **`brochure/`** - Brochure and promotional material images
- **`Caterings/`** - Catering menu and food images
- **`imageBg/`** - Background images and site logo (barcie_logo.jpg)
- **`Lobby/`** - Lobby and common area photos

### Image Guidelines

- Use optimized images for web (compressed JPEG/PNG)
- Recommended dimensions:
  - Room images: 1200x800px
  - Background images: 1920x1080px
  - Thumbnails: 400x300px
- Supported formats: JPG, PNG, WebP

## 💻 JavaScript Folder

Contains all client-side JavaScript functionality organized by section.

### Core Scripts

- **`popup-manager.js`** - Unified popup system (confirm, error, loading, success) with toast compatibility
- **`page-state-manager.js`** - Page state persistence and management
- **`admin-management-enhanced.js`** - Enhanced admin management features

### Dashboard Scripts (`dashboard/`)

Admin dashboard functionality modules:

- **`index.js`** - Main dashboard initialization
- **`dashboard-bootstrap.js`** - Dashboard bootstrap and setup
- **`bookings-section.js`** - Booking management functionality
- **`calendar-section.js`** - Calendar view and interactions
- **`rooms-section.js`** - Room management features
- **`reports.js`** - Reports and analytics functionality
- **`news-section.js`** - News management features
- **`mobile-enhancements.js`** - Mobile-specific dashboard features

### Guest Scripts (`guest/`)

Guest booking portal functionality:

- **`index.js`** - Main guest portal initialization
- **`guest-bootstrap.js`** - Guest portal bootstrap and setup
- **`guest-inline.js`** - Inline booking features
- **`chatbot.js`** - AI chatbot functionality
- **`pencil-conversion.js`** - Pencil booking to confirmed booking conversion
- **`room-feedback.js`** - Room feedback and review system
- **`sidebar-mobile.js`** - Mobile sidebar navigation

### Landing Scripts (`landing/`)

Landing page functionality:

- **`main.js`** - Main landing page scripts and animations
- **`auth.js`** - Authentication and login handling
- **`verify-components.js`** - Component verification and validation

## 🔧 Usage

### Adding New CSS

1. Create your stylesheet in the appropriate category
2. Follow the existing naming convention
3. Link it in the relevant PHP head component
4. Use CSS custom properties (variables) for consistency

```html
<link
  rel="stylesheet"
  href="assets/css/your-style.css?v=<?php echo $css_version; ?>"
/>
```

### Adding New Images

1. Place images in the appropriate subdirectory
2. Use descriptive filenames (lowercase, hyphens)
3. Optimize images before uploading
4. Update image paths in relevant components

```html
<img src="assets/images/category/your-image.jpg" alt="Description" />
```

### Adding New JavaScript

1. Create your script in the appropriate section folder
2. Follow modular design patterns
3. Include in the relevant component
4. Use cache-busting versioning

```html
<script src="assets/js/section/your-script.js?v=<?php echo $js_version; ?>"></script>
```

## 📝 Best Practices

### CSS

- Use BEM naming convention for classes
- Keep specificity low
- Use CSS variables for colors and spacing
- Comment complex selectors

### JavaScript

- Use ES6+ features
- Keep functions pure and modular
- Handle errors appropriately
- Use meaningful variable names
- Add JSDoc comments for functions

### Images

- Always include alt text
- Use responsive images when possible
- Lazy load images below the fold
- Use WebP with fallbacks for better compression

## 🔄 Cache Busting

All assets use version-based cache busting:

```php
<?php $v = time() . '_' . rand(1000, 9999); ?>
<link rel="stylesheet" href="assets/css/style.css?v=<?php echo $v; ?>">
```

This ensures users always get the latest version of assets.

## 📱 Mobile Considerations

- All CSS files should consider mobile responsiveness
- Test on devices with viewport widths: 320px, 375px, 768px, 1024px
- Use mobile-responsive.css for general mobile adjustments
- Use mobile-ux-extras.css for enhanced mobile UX features

## 🎯 Performance Tips

1. **Minify** CSS and JS in production
2. **Combine** related stylesheets when possible
3. **Compress** images using tools like TinyPNG
4. **Use CDN** for common libraries (Bootstrap, Font Awesome)
5. **Lazy load** non-critical assets

---

For questions or contributions, please refer to the main project README.md
