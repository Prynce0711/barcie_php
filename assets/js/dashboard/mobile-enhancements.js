/**
 * Mobile Enhancements for Dashboard
 * Improves touch interactions and mobile usability
 */

console.log('📱 mobile-enhancements.js loading...');

// Detect if device is mobile
const isMobile = () => {
  return window.innerWidth <= 768 || 
         /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
};

// Improved sidebar toggle for mobile
function enhancedToggleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = getOrCreateOverlay();
  const body = document.body;
  
  if (!sidebar) return;
  
  const isOpen = sidebar.classList.contains('show') || sidebar.classList.contains('open');
  
  if (isOpen) {
    sidebar.classList.remove('show', 'open');
    overlay.classList.remove('show', 'active');
    body.classList.remove('sidebar-open');
  } else {
    sidebar.classList.add('show', 'open');
    overlay.classList.add('show', 'active');
    body.classList.add('sidebar-open');
  }
}

// Get or create sidebar overlay
function getOrCreateOverlay() {
  let overlay = document.querySelector('.sidebar-overlay');
  
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', enhancedToggleSidebar);
  }
  
  return overlay;
}

// Enhanced touch feedback
function addTouchFeedback() {
  const touchElements = document.querySelectorAll('.btn, .card, .list-group-item, .nav-link');
  
  touchElements.forEach(element => {
    element.addEventListener('touchstart', function() {
      this.style.opacity = '0.7';
    });
    
    element.addEventListener('touchend', function() {
      setTimeout(() => {
        this.style.opacity = '1';
      }, 150);
    });
    
    element.addEventListener('touchcancel', function() {
      this.style.opacity = '1';
    });
  });
}

// Swipe to navigate images
function enableImageSwipe() {
  const imageContainers = document.querySelectorAll('.image-slider-container');
  
  imageContainers.forEach(container => {
    let touchStartX = 0;
    let touchEndX = 0;
    
    container.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    container.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe(container);
    }, { passive: true });
    
    function handleSwipe(container) {
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;
      
      if (Math.abs(diff) > swipeThreshold) {
        // Find the item ID from the container
        const itemId = container.id.replace('imageCarousel', '');
        
        if (diff > 0) {
          // Swipe left - next image
          if (typeof navigateImage === 'function' && itemId) {
            navigateImage(itemId, 1);
          }
        } else {
          // Swipe right - previous image
          if (typeof navigateImage === 'function' && itemId) {
            navigateImage(itemId, -1);
          }
        }
      }
    }
  });
}

// Add pull-to-refresh functionality (optional)
function enablePullToRefresh() {
  let startY = 0;
  let pullDistance = 0;
  const threshold = 80;
  
  const mainContent = document.querySelector('.main-content');
  if (!mainContent) return;
  
  mainContent.addEventListener('touchstart', (e) => {
    if (window.scrollY === 0) {
      startY = e.touches[0].pageY;
    }
  }, { passive: true });
  
  mainContent.addEventListener('touchmove', (e) => {
    if (window.scrollY === 0 && startY > 0) {
      pullDistance = e.touches[0].pageY - startY;
      
      if (pullDistance > 0 && pullDistance < threshold * 2) {
        e.preventDefault();
        // Visual feedback could be added here
      }
    }
  });
  
  mainContent.addEventListener('touchend', () => {
    if (pullDistance > threshold) {
      // Refresh the page
      location.reload();
    }
    startY = 0;
    pullDistance = 0;
  }, { passive: true });
}

// Optimize table display for mobile
function optimizeTablesForMobile() {
  if (!isMobile()) return;
  
  const tables = document.querySelectorAll('table');
  
  tables.forEach(table => {
    // Add data-label attributes for mobile view
    const headers = table.querySelectorAll('thead th');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      cells.forEach((cell, index) => {
        if (headers[index]) {
          cell.setAttribute('data-label', headers[index].textContent.trim());
        }
      });
    });
  });
}

// Improve form input experience on mobile
function enhanceMobileFormInputs() {
  // Prevent zoom on input focus for iOS
  const inputs = document.querySelectorAll('input, select, textarea');
  
  inputs.forEach(input => {
    // Add proper input types for mobile keyboards
    if (input.name && input.name.includes('email')) {
      input.type = 'email';
    }
    if (input.name && input.name.includes('tel')) {
      input.type = 'tel';
    }
    if (input.name && input.name.includes('number') || input.name.includes('price')) {
      input.type = 'number';
    }
  });
}

// Smooth scroll to top button for mobile
function addScrollToTop() {
  // Create scroll to top button
  const scrollBtn = document.createElement('button');
  scrollBtn.className = 'scroll-to-top';
  scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
  scrollBtn.style.cssText = `
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    font-size: 18px;
    cursor: pointer;
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  `;
  
  document.body.appendChild(scrollBtn);
  
  // Show/hide based on scroll position
  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      scrollBtn.style.opacity = '1';
      scrollBtn.style.visibility = 'visible';
    } else {
      scrollBtn.style.opacity = '0';
      scrollBtn.style.visibility = 'hidden';
    }
  });
  
  // Scroll to top on click
  scrollBtn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

// Optimize modal display for mobile
function optimizeModalsForMobile() {
  if (!isMobile()) return;
  
  // Observe for modal openings
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.classList && node.classList.contains('modal') && node.classList.contains('show')) {
          // Adjust modal for mobile
          const modalDialog = node.querySelector('.modal-dialog');
          if (modalDialog) {
            modalDialog.style.margin = '10px';
            modalDialog.style.maxWidth = 'calc(100% - 20px)';
          }
        }
      });
    });
  });
  
  observer.observe(document.body, { childList: true, subtree: true });
}

// Improve touch scrolling
function improveScrolling() {
  // Add momentum scrolling for iOS
  document.body.style.webkitOverflowScrolling = 'touch';
  
  // Improve modal scrolling
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    modal.style.webkitOverflowScrolling = 'touch';
  });
}

// Add haptic feedback (if supported)
function addHapticFeedback() {
  if (!('vibrate' in navigator)) return;
  
  // Add to important buttons
  const importantButtons = document.querySelectorAll('.btn-primary, .btn-success, .btn-danger');
  
  importantButtons.forEach(button => {
    button.addEventListener('click', () => {
      navigator.vibrate(10); // Short vibration
    });
  });
}

// Handle orientation change
function handleOrientationChange() {
  window.addEventListener('orientationchange', () => {
    // Close sidebar if open
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar && sidebar.classList.contains('show')) {
      sidebar.classList.remove('show', 'open');
      if (overlay) overlay.classList.remove('show', 'active');
      document.body.classList.remove('sidebar-open');
    }
    
    // Re-calculate dimensions
    setTimeout(() => {
      window.dispatchEvent(new Event('resize'));
    }, 100);
  });
}

// Improve double-tap zoom prevention on specific elements
function preventDoubleTapZoom() {
  let lastTap = 0;
  
  const elements = document.querySelectorAll('.btn, .card, img');
  
  elements.forEach(element => {
    element.addEventListener('touchend', (e) => {
      const currentTime = new Date().getTime();
      const tapLength = currentTime - lastTap;
      
      if (tapLength < 300 && tapLength > 0) {
        e.preventDefault();
      }
      
      lastTap = currentTime;
    });
  });
}

// Initialize all mobile enhancements
function initializeMobileEnhancements() {
  if (!isMobile()) {
    console.log('📱 Desktop detected, skipping mobile enhancements');
    return;
  }
  
  console.log('📱 Initializing mobile enhancements...');
  
  try {
    // Override global toggleSidebar with enhanced version
    window.toggleSidebar = enhancedToggleSidebar;
    
    // Initialize enhancements
    addTouchFeedback();
    enableImageSwipe();
    optimizeTablesForMobile();
    enhanceMobileFormInputs();
    addScrollToTop();
    optimizeModalsForMobile();
    improveScrolling();
    addHapticFeedback();
    handleOrientationChange();
    preventDoubleTapZoom();
    
    // Create overlay element
    getOrCreateOverlay();
    
    console.log('✅ Mobile enhancements initialized');
  } catch (error) {
    console.error('❌ Error initializing mobile enhancements:', error);
  }
}

// Run when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeMobileEnhancements);
} else {
  initializeMobileEnhancements();
}

// Re-initialize on dynamic content changes
window.addEventListener('load', () => {
  setTimeout(initializeMobileEnhancements, 500);
});

// Export functions
window.mobileEnhancements = {
  isMobile,
  enhancedToggleSidebar,
  addTouchFeedback,
  enableImageSwipe,
  optimizeTablesForMobile
};

console.log('✅ mobile-enhancements.js loaded');
