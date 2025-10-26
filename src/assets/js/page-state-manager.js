/**
 * Page State Management & Smart Redirect System
 * 
 * This manages:
 * - Remembering current page/section on refresh
 * - Default starting sections for admin vs guest
 * - Browser back/forward navigation
 */

(function() {
    'use strict';
    
    const STATE_KEY = 'barcie_page_state';
    const ROLE_KEY = 'barcie_user_role';
    
    // Default sections for each role
    const DEFAULT_SECTIONS = {
        admin: 'dashboard',  // Admin starts at dashboard
        guest: 'overview'    // Guest starts at overview
    };
    
    /**
     * Save current page state
     */
    function savePageState(section, role) {
        try {
            const state = {
                section: section,
                role: role,
                timestamp: Date.now(),
                url: window.location.href
            };
            sessionStorage.setItem(STATE_KEY, JSON.stringify(state));
            sessionStorage.setItem(ROLE_KEY, role);
        } catch (e) {
            console.warn('Failed to save page state:', e);
        }
    }
    
    /**
     * Get saved page state
     */
    function getPageState() {
        try {
            const stateJson = sessionStorage.getItem(STATE_KEY);
            if (stateJson) {
                return JSON.parse(stateJson);
            }
        } catch (e) {
            console.warn('Failed to get page state:', e);
        }
        return null;
    }
    
    /**
     * Get user role
     */
    function getUserRole() {
        return sessionStorage.getItem(ROLE_KEY) || null;
    }
    
    /**
     * Clear page state (on logout)
     */
    function clearPageState() {
        try {
            sessionStorage.removeItem(STATE_KEY);
            sessionStorage.removeItem(ROLE_KEY);
        } catch (e) {
            console.warn('Failed to clear page state:', e);
        }
    }
    
    /**
     * Detect current page type
     */
    function detectPageType() {
        const path = window.location.pathname;
        if (path.includes('dashboard.php')) return 'admin';
        if (path.includes('Guest.php')) return 'guest';
        if (path.includes('index.php') || path === '/' || path.endsWith('/barcie_php') || path.endsWith('/barcie_php/')) return 'landing';
        return 'unknown';
    }
    
    /**
     * Get current active section
     */
    function getCurrentSection() {
        // Check URL hash first
        const hash = window.location.hash.substring(1); // Remove #
        if (hash) return hash;
        
        // Check for active content section
        const activeSections = document.querySelectorAll('.content-section.active');
        if (activeSections.length > 0) {
            const activeSection = activeSections[0];
            // Extract section name from ID (remove -section suffix if present)
            const sectionId = activeSection.id || '';
            return sectionId.replace('-section', '');
        }
        
        return null;
    }
    
    /**
     * Navigate to a specific section
     */
    function navigateToSection(sectionName, updateHash = true) {
        console.log('🧭 Navigating to section:', sectionName);
        
        // Update URL hash if requested
        if (updateHash) {
            window.location.hash = sectionName;
        }
        
        // Find the section (try different ID formats)
        const possibleIds = [
            sectionName,
            sectionName + '-section',
            sectionName + 'Section'
        ];
        
        let targetSection = null;
        for (const id of possibleIds) {
            targetSection = document.getElementById(id);
            if (targetSection) break;
        }
        
        if (!targetSection) {
            console.warn('Section not found:', sectionName);
            return false;
        }
        
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
        });
        
        // Show target section
        targetSection.classList.add('active');
        targetSection.style.display = 'block';
        
        // Update sidebar navigation if exists
        updateSidebarNavigation(sectionName);
        
        // Save state
        const pageType = detectPageType();
        if (pageType === 'admin' || pageType === 'guest') {
            savePageState(sectionName, pageType);
        }
        
        return true;
    }
    
    /**
     * Update sidebar active state
     */
    function updateSidebarNavigation(sectionName) {
        // Remove active class from all nav items
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to matching nav item
        const matchingLink = document.querySelector(`.nav-link[href="#${sectionName}"]`);
        if (matchingLink) {
            matchingLink.classList.add('active');
        }
    }
    
    /**
     * Restore page state on load/refresh
     */
    function restorePageState() {
        const pageType = detectPageType();
        
        console.log('🔄 Page type detected:', pageType);
        
        if (pageType === 'admin' || pageType === 'guest') {
            // Check if there's a URL hash
            const urlHash = window.location.hash.substring(1);
            
            if (urlHash) {
                // Use URL hash
                console.log('📍 Using URL hash:', urlHash);
                navigateToSection(urlHash, false);
            } else {
                // Check for saved state
                const savedState = getPageState();
                
                if (savedState && savedState.role === pageType) {
                    // Restore saved section
                    console.log('💾 Restoring saved section:', savedState.section);
                    navigateToSection(savedState.section, true);
                } else {
                    // Use default section for role
                    const defaultSection = DEFAULT_SECTIONS[pageType];
                    console.log('🏠 Using default section:', defaultSection);
                    navigateToSection(defaultSection, true);
                }
            }
        }
    }
    
    /**
     * Handle login success - set role and navigate to default
     */
    function handleLoginSuccess(role) {
        console.log('✅ Login successful, role:', role);
        const defaultSection = DEFAULT_SECTIONS[role];
        savePageState(defaultSection, role);
        
        // Navigate to appropriate page
        if (role === 'admin') {
            window.location.href = 'dashboard.php#' + defaultSection;
        } else if (role === 'guest') {
            window.location.href = 'Guest.php#' + defaultSection;
        }
    }
    
    /**
     * Handle logout - clear state
     */
    function handleLogout() {
        console.log('👋 Logging out, clearing state');
        clearPageState();
        window.location.href = 'index.php';
    }
    
    /**
     * Initialize on page load
     */
    function initialize() {
        console.log('🚀 Page State Manager initialized');
        
        // Restore state on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', restorePageState);
        } else {
            restorePageState();
        }
        
        // Handle hash changes (browser back/forward)
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                navigateToSection(hash, false);
            }
        });
        
        // Handle navigation clicks
        document.addEventListener('click', function(e) {
            const navLink = e.target.closest('.nav-link[href^="#"]');
            if (navLink) {
                e.preventDefault();
                const sectionName = navLink.getAttribute('href').substring(1);
                navigateToSection(sectionName, true);
            }
        });
        
        // Save state periodically (in case of crash)
        setInterval(function() {
            const currentSection = getCurrentSection();
            const pageType = detectPageType();
            if (currentSection && (pageType === 'admin' || pageType === 'guest')) {
                savePageState(currentSection, pageType);
            }
        }, 5000); // Every 5 seconds
    }
    
    // Expose public API
    window.BarcieStateManager = {
        navigate: navigateToSection,
        saveState: savePageState,
        getState: getPageState,
        clearState: clearPageState,
        handleLoginSuccess: handleLoginSuccess,
        handleLogout: handleLogout,
        getCurrentSection: getCurrentSection,
        getUserRole: getUserRole
    };
    
    // Auto-initialize
    initialize();
    
})();
