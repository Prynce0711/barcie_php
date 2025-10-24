# JavaScript Organization Structure

This directory contains all JavaScript files organized by functionality to improve maintainability and development workflow.

## Directory Structure

```
assets/js/
├── dashboard/          # Dashboard (Admin) JavaScript files
│   ├── index.js       # Dashboard modules index and documentation
│   ├── dashboard-bootstrap.js    # Core dashboard functionality
│   ├── calendar-section.js      # Calendar and room management
│   ├── rooms-section.js         # Rooms & facilities CRUD operations
│   └── bookings-section.js      # Booking management and filtering
│
└── guest/             # Guest Portal JavaScript files
    ├── index.js       # Guest modules index and documentation
    ├── guest-bootstrap.js       # Core guest portal functionality
    ├── guest-inline.js          # Additional guest-specific features
    ├── chatbot.js              # Interactive chatbot system
    └── sidebar-mobile.js        # Mobile navigation functionality
```

## Module Descriptions

### Dashboard Modules

- **dashboard-bootstrap.js**: Core dashboard initialization, Bootstrap integration, chart setup, form enhancements, and main functionality coordinator
- **calendar-section.js**: FullCalendar integration, room calendar display, and room status management
- **rooms-section.js**: Room and facility filtering, search functionality, edit forms, and CRUD operations
- **bookings-section.js**: Booking table filtering, status management, and discount processing

### Guest Modules

- **guest-bootstrap.js**: Core guest portal initialization, form handling, booking system, and receipt generation
- **guest-inline.js**: Additional guest-specific features and inline functionality
- **chatbot.js**: Interactive chatbot with knowledge base for guest assistance
- **sidebar-mobile.js**: Mobile-responsive sidebar navigation and menu functionality

## Usage

### Dashboard
```html
<!-- Core dashboard scripts -->
<script src="assets/js/dashboard/dashboard-bootstrap.js"></script>
<!-- Section-specific scripts -->
<script src="assets/js/dashboard/calendar-section.js"></script>
<script src="assets/js/dashboard/rooms-section.js"></script>
<script src="assets/js/dashboard/bookings-section.js"></script>
```

### Guest Portal
```html
<!-- Core guest scripts -->
<script src="assets/js/guest/guest-bootstrap.js"></script>
<script src="assets/js/guest/guest-inline.js" defer></script>
<!-- Feature-specific scripts -->
<script src="assets/js/guest/chatbot.js" defer></script>
<script src="assets/js/guest/sidebar-mobile.js" defer></script>
```

## Benefits of This Organization

1. **Clear Separation of Concerns**: Dashboard and guest functionality are completely separated
2. **Improved Maintainability**: Each file has a specific, well-defined purpose
3. **Better Performance**: Load only the JavaScript needed for each section
4. **Team Collaboration**: Multiple developers can work on different sections without conflicts
5. **Easier Debugging**: Issues can be isolated to specific modules
6. **Scalability**: New features can be added as separate modules

## File Loading Order

### Dashboard
1. External libraries (Bootstrap, Chart.js, FullCalendar)
2. Core dashboard functionality
3. Section-specific modules (loaded as needed)

### Guest Portal
1. External libraries (Bootstrap)
2. Core guest functionality  
3. Additional features (inline, chatbot, mobile)

## Maintenance Notes

- All file references have been updated to reflect the new structure
- Functionality remains identical to the original implementation
- Each module is self-contained but can interact with others through global functions
- Console logging is enabled for debugging module loading

Last updated: October 23, 2025