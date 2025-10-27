/**
 * Dashboard JavaScript Modules Index
 * 
 * This file serves as an entry point for all dashboard-related JavaScript modules.
 * It ensures all dashboard functionality is properly loaded and initialized.
 */

// Dashboard Core Module - Main functionality
// This is loaded directly in the HTML as it contains the initialization code

// Section-specific modules are loaded separately as needed:
// - calendar-section.js: Calendar and room management functionality
// - rooms-section.js: Rooms and facilities CRUD operations  
// - bookings-section.js: Booking management and filtering

console.log('Dashboard JavaScript modules structure loaded');

// Export module information for debugging
window.DashboardModules = {
    core: 'dashboard-bootstrap.js',
    calendar: 'calendar-section.js', 
    rooms: 'rooms-section.js',
    bookings: 'bookings-section.js',
    initialized: new Date().toISOString()
};