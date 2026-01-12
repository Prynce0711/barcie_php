# Components Directory

This directory contains all reusable PHP components and sections used throughout the BarCIE International Center website. Components are organized by section (dashboard, guest portal, landing page) for better maintainability.

## 📁 Directory Structure

```
components/
├── dashboard/      # Admin dashboard components
├── guest/          # Guest booking portal components
└── landing/        # Landing page components
```

## 🎛️ Dashboard Components

Admin dashboard interface components for managing bookings, rooms, and content.

### Main Components

- **`header.php`** - Dashboard header with navigation and user info
- **`sidebar.php`** - Sidebar navigation menu with all admin sections
- **`footer.php`** - Dashboard footer with credits and scripts
- **`data_processing.php`** - Data processing and CRUD operations
- **`item_actions.php`** - Item management actions (add, edit, delete)

### Sections (`sections/`)

#### Core Management Sections

- **`dashboard_section.php`** - Main dashboard overview with statistics
- **`bookings_section.php`** - Booking management interface
- **`bookings_table_content.php`** - Bookings data table component
- **`rooms_section.php`** - Room management interface
- **`rooms_grid_content.php`** - Room grid view
- **`room_list_content.php`** - Room list view
- **`calendar_section.php`** - Calendar view for bookings
- **`news_section.php`** - News and announcements management
- **`reports_section.php`** - Analytics and reports generation
- **`feedback_section.php`** - Customer feedback management

#### Booking Management

- **`pencil_book_management.php`** - Tentative booking management
- **`payment_verification.php`** - Payment verification interface
- **`discount_applications.php`** - Discount and promo management

#### Admin Management

- **`admin_management_enhanced.php`** - Enhanced admin user management

#### Modals

- **`add_item_modal.php`** - Add new item (catering/services) modal
- **`edit_item_modal.php`** - Edit existing item modal
- **`room_calendar_modal.php`** - Room availability calendar modal

##### Admin User Modals (`modals/`)

- **`add_admin_modal_enhanced.php`** - Enhanced add admin modal
- **`add_admin_modal.php`** - Basic add admin modal
- **`edit_admin_modal.php`** - Edit admin user modal
- **`admin_auth_modal.php`** - Admin authentication modal

### Usage Example

```php
<?php include 'components/dashboard/header.php'; ?>
<?php include 'components/dashboard/sidebar.php'; ?>
<?php include 'components/dashboard/sections/dashboard_section.php'; ?>
<?php include 'components/dashboard/footer.php'; ?>
```

## 👤 Guest Components

Guest booking portal components for room reservations and inquiries.

### Main Components

- **`head.php`** - Guest portal head section with meta tags and styles
- **`sidebar.php`** - Guest navigation sidebar with booking steps
- **`footer.php`** - Guest portal footer
- **`bank_qr.php`** - Bank QR code payment information
- **`convert_pencil.php`** - Convert tentative bookings to confirmed

### Sections (`sections/`)

#### Booking Flow

- **`overview.php`** - Booking overview and welcome section
- **`rooms.php`** - Room selection interface
- **`availability.php`** - Room availability checker
- **`booking.php`** - Main booking form and submission
- **`pencil_booking.php`** - Tentative booking form
- **`confirm_addOn.php`** - Add-on services confirmation
- **`discount_application.php`** - Discount code application

#### Additional Features

- **`calendar_room_list.php`** - Calendar with room availability
- **`chatbot.php`** - AI chatbot widget for customer support
- **`feedback.php`** - Guest feedback and review form

### Modals (`modals/`)

- **`availability_modal.php`** - Availability checker modal
- **`room_calendar_modal.php`** - Interactive room calendar modal
- **`image_crop_modal.php`** - Image upload and crop modal

### Usage Example

```php
<?php include 'components/guest/head.php'; ?>
<?php include 'components/guest/sidebar.php'; ?>
<?php include 'components/guest/sections/rooms.php'; ?>
<?php include 'components/guest/footer.php'; ?>
```

## 🏠 Landing Components

Landing page components for the public-facing website.

### Main Components

- **`head.php`** - Landing page head with SEO meta tags and styles
- **`navigation.php`** - Main navigation menu with smooth scrolling
- **`footer.php`** - Landing page footer with contact info and links

### Sections (`sections/`)

- **`hero.php`** - Hero section with main call-to-action
- **`about.php`** - About BarCIE section
- **`vision_mission.php`** - Vision and mission statement
- **`services.php`** - Services overview
- **`caterings.php`** - Catering services showcase
- **`event_stylists.php`** - Event styling services
- **`features.php`** - Key features and amenities
- **`brochure.php`** - Downloadable brochures section
- **`news.php`** - Latest news and announcements
- **`contact.php`** - Contact form and information

### Modals (`modals/`)

- **`admin_login_modal.php`** - Admin login modal (legacy - now moved to admin.php)

### Usage Example

```php
<!doctype html>
<html lang="en">
<?php include 'components/landing/head.php'; ?>
<body>
  <?php include 'components/landing/navigation.php'; ?>
  <?php include 'components/landing/sections/hero.php'; ?>
  <?php include 'components/landing/sections/about.php'; ?>
  <?php include 'components/landing/footer.php'; ?>
</body>
</html>
```

## 🏗️ Component Architecture

### Design Principles

1. **Modularity** - Each component handles a specific feature
2. **Reusability** - Components can be included in multiple pages
3. **Separation of Concerns** - Logic separated from presentation
4. **DRY (Don't Repeat Yourself)** - Common elements extracted to components

### Component Types

#### Layout Components
- Header, Footer, Sidebar
- Define the overall page structure
- Include navigation and branding

#### Section Components
- Content-specific sections
- Feature or page-specific functionality
- Can be composed of smaller components

#### Modal Components
- Overlay dialogs and popups
- Form submissions and confirmations
- Data input and editing interfaces

## 📋 Best Practices

### Creating New Components

1. **Identify reusable elements** - Don't create components for one-time use
2. **Follow naming conventions** - Use descriptive, lowercase filenames with underscores
3. **Keep components focused** - One component = one responsibility
4. **Document dependencies** - Note required CSS, JS, or other components

### Component Structure

```php
<?php
// components/section/your_component.php

// Dependencies check or session validation
// require_once 'database/db_connect.php';

// PHP logic and data preparation
$data = fetchData();
?>

<!-- HTML structure -->
<section id="your-component" class="component-class">
  <div class="container">
    <!-- Content -->
  </div>
</section>

<!-- Component-specific scripts -->
<script>
  // Component initialization
</script>
```

### Including Components

```php
// Absolute path method
<?php include __DIR__ . '/components/dashboard/header.php'; ?>

// Relative path method (ensure correct context)
<?php include 'components/dashboard/header.php'; ?>
```

## 🔄 Data Flow

### Dashboard Components
1. User interacts with component
2. Component sends request to API endpoint
3. API processes and returns data
4. Component updates UI with response

### Guest Components
1. Guest fills form/selects options
2. Data validated on client-side
3. Submitted to API endpoint
4. Response displayed via toast notifications
5. UI updates accordingly

### Landing Components
1. Static content loaded from database
2. Interactive elements use JavaScript
3. Forms submit to API endpoints
4. Smooth scrolling between sections

## 🎨 Styling Components

Each component includes its styles in the corresponding CSS file in `assets/css/`:

- Dashboard components → `dashboard.css`
- Guest components → `guest.css`
- Landing components → `landing-page.css`

### Custom Styles

```html
<style scoped>
  /* Component-specific styles */
  #your-component {
    /* Styles here */
  }
</style>
```

## 🧪 Testing Components

### Individual Component Testing

1. Create a test page that includes only the component
2. Verify rendering and functionality
3. Test responsive behavior
4. Check for console errors

### Integration Testing

1. Test component within full page context
2. Verify interactions with other components
3. Test data flow and state management

## 📱 Responsive Design

All components should be responsive:

- Mobile: 320px - 767px
- Tablet: 768px - 1023px
- Desktop: 1024px and up

Use Bootstrap's responsive utilities and custom media queries.

## 🔐 Security Considerations

- Always sanitize user input
- Use prepared statements for database queries
- Validate data on server-side
- Implement CSRF protection
- Check user permissions before displaying sensitive components

## 🚀 Performance

- Lazy load non-critical components
- Minimize database queries (use joins)
- Cache frequently accessed data
- Optimize component rendering
- Use async loading for heavy components

---

For questions or contributions, please refer to the main project README.md
