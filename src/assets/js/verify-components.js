/**
 * Component Structure Verification
 * 
 * This file helps verify that all components are properly organized
 * and accessible in their new folder structure.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🏗️ Component Structure Verification Started');
    
    // Check if we're on the dashboard
    if (document.getElementById('dashboard-section') || document.querySelector('.sidebar')) {
        console.log('✅ Dashboard page detected');
        console.log('📊 Dashboard components should be loaded from: components/dashboard/');
        
        // Check for dashboard-specific elements
        const dashboardElements = [
            'dashboard-section',
            'calendar-section', 
            'rooms',
            'bookings'
        ];
        
        let dashboardScore = 0;
        dashboardElements.forEach(elementId => {
            if (document.getElementById(elementId)) {
                console.log(`✅ Dashboard section found: ${elementId}`);
                dashboardScore++;
            } else {
                console.log(`❌ Dashboard section missing: ${elementId}`);
            }
        });
        
        console.log(`Dashboard components: ${dashboardScore}/${dashboardElements.length} sections loaded`);
    }
    
    // Check if we're on the guest portal
    if (document.querySelector('.sidebar-guest') || document.getElementById('chatbotContainer')) {
        console.log('✅ Guest portal page detected');
        console.log('👤 Guest components should be loaded from: components/guest/');
        
        // Check for guest-specific elements
        const guestElements = [
            '.sidebar-guest',
            '#chatbotContainer',
            '.content-section'
        ];
        
        let guestScore = 0;
        guestElements.forEach(selector => {
            if (document.querySelector(selector)) {
                console.log(`✅ Guest element found: ${selector}`);
                guestScore++;
            } else {
                console.log(`❌ Guest element missing: ${selector}`);
            }
        });
        
        console.log(`Guest components: ${guestScore}/${guestElements.length} elements found`);
    }
    
    // Component loading verification
    console.log('🔍 Component Structure Analysis:');
    console.log('- Components are now organized under: /components/');
    console.log('- Dashboard components: /components/dashboard/');
    console.log('- Guest components: /components/guest/');
    console.log('- JavaScript remains in: /assets/js/{dashboard|guest}/');
    
    // Log any component-related errors
    window.addEventListener('error', function(e) {
        if (e.filename && e.filename.includes('components/')) {
            console.error('🚨 Component loading error:', e.filename, e.message);
        }
    });
});

// Make verification function globally available
window.verifyComponentStructure = function() {
    console.log('🔍 Manual Component Structure Verification');
    console.log('📁 Current structure:');
    console.log('├── components/');
    console.log('│   ├── dashboard/     # Admin components');
    console.log('│   │   ├── sections/  # Dashboard page sections');
    console.log('│   │   └── *.php      # Dashboard layout files');
    console.log('│   └── guest/         # Guest portal components');
    console.log('│       ├── sections/  # Guest page sections');
    console.log('│       └── *.php      # Guest layout files');
    console.log('└── assets/js/');
    console.log('    ├── dashboard/     # Dashboard JavaScript');
    console.log('    └── guest/         # Guest JavaScript');
    
    console.log('\n📊 Page detection:');
    console.log('- Dashboard page:', !!document.getElementById('dashboard-section'));
    console.log('- Guest page:', !!(document.querySelector('.sidebar-guest') || document.getElementById('chatbotContainer')));
};

console.log('🏗️ Component structure verification loaded. Run verifyComponentStructure() for manual check.');