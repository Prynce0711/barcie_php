/**
 * JavaScript Structure Verification
 * 
 * This file helps verify that all JavaScript files are properly organized
 * and accessible in their new folder structure.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Verify Dashboard Modules (if on dashboard page)
    if (document.getElementById('dashboard-section')) {
        console.log('✅ Dashboard page detected');
        
        // Check if dashboard modules are loaded
        const expectedDashboardFunctions = [
            'initializeDashboard',
            'initializeCharts', 
            'setupSectionNavigation',
            'initializeCalendarNavigation',
            'initializeRoomsFiltering',
            'filterBookings'
        ];
        
        let dashboardScore = 0;
        expectedDashboardFunctions.forEach(func => {
            if (typeof window[func] === 'function') {
                console.log(`✅ Dashboard function found: ${func}`);
                dashboardScore++;
            } else {
                console.log(`❌ Dashboard function missing: ${func}`);
            }
        });
        
        console.log(`Dashboard functionality: ${dashboardScore}/${expectedDashboardFunctions.length} functions loaded`);
    }
    
    // Verify Guest Modules (if on guest page)
    if (document.querySelector('.sidebar-guest') || document.getElementById('chatbotContainer')) {
        console.log('✅ Guest page detected');
        
        // Check if guest modules are loaded
        const expectedGuestFunctions = [
            'initializeGuestPortal',
            'setupBookingForms',
            'toggleChatbot',
            'toggleSidebar'
        ];
        
        let guestScore = 0;
        expectedGuestFunctions.forEach(func => {
            if (typeof window[func] === 'function') {
                console.log(`✅ Guest function found: ${func}`);
                guestScore++;
            } else {
                console.log(`❌ Guest function missing: ${func}`);
            }
        });
        
        console.log(`Guest functionality: ${guestScore}/${expectedGuestFunctions.length} functions loaded`);
    }
    
    // Log module information if available
    if (window.DashboardModules) {
        console.log('📊 Dashboard Modules:', window.DashboardModules);
    }
    
    if (window.GuestModules) {
        console.log('👤 Guest Modules:', window.GuestModules);
    }
});

// Make verification function globally available for manual testing
window.verifyJSStructure = function() {
    console.log('🔍 Manual JavaScript Structure Verification');
    console.log('Current page type detection:');
    console.log('- Dashboard page:', !!document.getElementById('dashboard-section'));
    console.log('- Guest page:', !!(document.querySelector('.sidebar-guest') || document.getElementById('chatbotContainer')));
    console.log('- Available modules:', {
        dashboard: window.DashboardModules,
        guest: window.GuestModules
    });
};