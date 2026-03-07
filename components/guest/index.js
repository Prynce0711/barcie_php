/**
 * Guest JavaScript Modules Index
 * 
 * This file serves as an entry point for all guest-related JavaScript modules.
 * It ensures all guest portal functionality is properly loaded and initialized.
 */

// Guest Core Module - Main guest portal functionality
// This is loaded directly in the HTML as it contains the initialization code

// Additional guest modules:
// - guest-inline.js: Inline guest-specific functionality
// - chatbot.js: Interactive chatbot for guest assistance
// - sidebar-mobile.js: Mobile navigation and sidebar functionality

console.log('Guest JavaScript modules structure loaded');

// Export module information for debugging
window.GuestModules = {
    core: 'guest-bootstrap.js',
    inline: 'guest-inline.js',
    chatbot: 'chatbot.js',
    sidebar: 'sidebar-mobile.js',
    initialized: new Date().toISOString()
};