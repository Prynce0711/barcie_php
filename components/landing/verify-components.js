// Component Verification Script
// This script verifies that all critical components are loaded correctly

(function() {
  'use strict';

  console.log('🔍 Component Verification Script Loaded');

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', verifyComponents);
  } else {
    verifyComponents();
  }

  function verifyComponents() {
    console.log('✅ Starting component verification...');

    // Required elements for the current landing page build
    const requiredElements = {
      'Navigation': document.querySelector('nav'),
      'Hero Section': document.getElementById('home'),
      'About Section': document.getElementById('about'),
      'Vision & Mission Section': document.getElementById('vision-mission'),
      'News Section': document.getElementById('news'),
      'Event Stylists Section': document.getElementById('event-stylists'),
      'Caterings Section': document.getElementById('caterings'),
      'Brochure Section': document.getElementById('brochure'),
      'Contact Section': document.getElementById('contact'),
    };

    // Optional/legacy elements (kept for compatibility checks)
    const optionalElements = {
      'Features Section': document.getElementById('features'),
      'Services Section': document.getElementById('services'),
      'Admin Login Modal': document.getElementById('adminLoginModal'),
      'Admin Login Form': document.getElementById('admin-login-form')
    };

    let allPresent = true;

    for (const [name, element] of Object.entries(requiredElements)) {
      if (element) {
        console.log(`✅ ${name}: Found`);
      } else {
        console.warn(`⚠️ ${name}: Missing`);
        allPresent = false;
      }
    }

    for (const [name, element] of Object.entries(optionalElements)) {
      if (element) {
        console.log(`✅ ${name}: Found (optional)`);
      } else {
        console.info(`[INFO] ${name}: Not present (optional)`);
      }
    }

    // Check for State Manager
    if (window.BarcieStateManager) {
      console.log('✅ State Manager: Loaded');
    } else {
      console.warn('⚠️ State Manager: Not loaded');
      allPresent = false;
    }

    // Final verification result
    if (allPresent) {
      console.log('✅ All components verified successfully!');
    } else {
      console.warn('⚠️ Some components are missing. Check the warnings above.');
    }
  }
})();
