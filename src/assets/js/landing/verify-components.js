// Landing Page Component Verification Script
// Tests all functionality of the modularized landing page

console.log('🚀 BarCIE Landing Page Component Verification');
console.log('============================================');

// Component verification results
const verificationResults = {
  components: {},
  functionality: {},
  styling: {},
  overall: true
};

// Test component loading
function testComponentLoading() {
  console.log('\n📁 Testing Component Loading...');
  
  const componentTests = {
    'Navigation': document.getElementById('mainNavbar'),
    'Hero Section': document.getElementById('home'),
    'About Section': document.getElementById('about'),
    'Features Section': document.getElementById('features'),
    'Services Section': document.getElementById('services'),
    'Contact Section': document.getElementById('contact'),
    'User Auth Modal': document.getElementById('user-auth'),
    'Admin Login Modal': document.getElementById('admin-login'),
    'Footer': document.querySelector('footer')
  };

  Object.entries(componentTests).forEach(([name, element]) => {
    const exists = element !== null;
    verificationResults.components[name] = exists;
    console.log(`${exists ? '✅' : '❌'} ${name}: ${exists ? 'Loaded' : 'Missing'}`);
  });
}

// Test JavaScript functionality
function testJavaScriptFunctionality() {
  console.log('\n⚡ Testing JavaScript Functionality...');
  
  const functionTests = {
    'Smooth Scrolling': typeof scrollToSection === 'function',
    'Modal Management': typeof showSection === 'function' && typeof closeSection === 'function',
    'Password Toggle': typeof togglePassword === 'function',
    'AOS Library': typeof AOS !== 'undefined',
    'Form Elements': document.getElementById('user-login-form') !== null,
    'Admin Form': document.getElementById('admin-login-form') !== null
  };

  Object.entries(functionTests).forEach(([name, passed]) => {
    verificationResults.functionality[name] = passed;
    console.log(`${passed ? '✅' : '❌'} ${name}: ${passed ? 'Working' : 'Failed'}`);
  });
}

// Test CSS and styling
function testStyling() {
  console.log('\n🎨 Testing CSS and Styling...');
  
  const navbar = document.getElementById('mainNavbar');
  const heroSection = document.getElementById('home');
  const glassCards = document.querySelectorAll('.glass-card');
  
  const styleTests = {
    'CSS Variables': getComputedStyle(document.documentElement).getPropertyValue('--primary-color'),
    'Navbar Styling': navbar ? getComputedStyle(navbar).position === 'fixed' : false,
    'Hero Background': heroSection ? getComputedStyle(heroSection).backgroundImage !== 'none' : false,
    'Glass Cards': glassCards.length > 0,
    'Bootstrap Classes': document.querySelector('.container') !== null,
    'Font Awesome Icons': document.querySelector('.fas, .fab') !== null
  };

  Object.entries(styleTests).forEach(([name, passed]) => {
    verificationResults.styling[name] = !!passed;
    console.log(`${passed ? '✅' : '❌'} ${name}: ${passed ? 'Applied' : 'Missing'}`);
  });
}

// Test animations and transitions
function testAnimations() {
  console.log('\n🎭 Testing Animations...');
  
  // Test AOS initialization
  const aosElements = document.querySelectorAll('[data-aos]');
  const hasAOSElements = aosElements.length > 0;
  
  console.log(`${hasAOSElements ? '✅' : '❌'} AOS Elements: ${aosElements.length} found`);
  
  // Test glass card hover effects
  const glassCards = document.querySelectorAll('.glass-card');
  console.log(`${glassCards.length > 0 ? '✅' : '❌'} Glass Cards: ${glassCards.length} found`);
  
  // Test button animations
  const customButtons = document.querySelectorAll('.btn-primary-custom, .btn-outline-custom');
  console.log(`${customButtons.length > 0 ? '✅' : '❌'} Custom Buttons: ${customButtons.length} found`);
}

// Test responsive design
function testResponsive() {
  console.log('\n📱 Testing Responsive Design...');
  
  const navbar = document.querySelector('.navbar-toggler');
  const containers = document.querySelectorAll('.container');
  const responsiveClasses = document.querySelectorAll('[class*="col-"], [class*="d-"], [class*="mb-"], [class*="me-"]');
  
  console.log(`${navbar ? '✅' : '❌'} Mobile Navigation Toggle: ${navbar ? 'Present' : 'Missing'}`);
  console.log(`${containers.length > 0 ? '✅' : '❌'} Bootstrap Containers: ${containers.length} found`);
  console.log(`${responsiveClasses.length > 0 ? '✅' : '❌'} Responsive Classes: ${responsiveClasses.length} found`);
}

// Test form functionality
function testForms() {
  console.log('\n📝 Testing Form Functionality...');
  
  const userLoginForm = document.getElementById('user-login-form');
  const userSignupForm = document.getElementById('user-signup-form');
  const adminForm = document.getElementById('admin-login-form');
  
  const formTests = {
    'User Login Form': userLoginForm !== null,
    'User Signup Form': userSignupForm !== null,
    'Admin Login Form': adminForm !== null,
    'Form Validation Elements': document.querySelector('[id$="-msg"]') !== null,
    'Password Toggles': document.querySelectorAll('[onclick*="togglePassword"]').length > 0
  };

  Object.entries(formTests).forEach(([name, passed]) => {
    console.log(`${passed ? '✅' : '❌'} ${name}: ${passed ? 'Present' : 'Missing'}`);
  });
}

// Test external resources
function testExternalResources() {
  console.log('\n🌐 Testing External Resources...');
  
  const resources = {
    'Bootstrap CSS': document.querySelector('link[href*="bootstrap"]') !== null,
    'Font Awesome': document.querySelector('link[href*="font-awesome"]') !== null,
    'AOS CSS': document.querySelector('link[href*="aos"]') !== null,
    'Bootstrap JS': document.querySelector('script[src*="bootstrap"]') !== null,
    'AOS JS': document.querySelector('script[src*="aos"]') !== null
  };

  Object.entries(resources).forEach(([name, loaded]) => {
    console.log(`${loaded ? '✅' : '❌'} ${name}: ${loaded ? 'Loaded' : 'Missing'}`);
  });
}

// Manual testing functions
function manualTests() {
  console.log('\n🧪 Manual Testing Functions Available:');
  console.log('=====================================');
  
  window.testModal = function(modalId) {
    showSection(modalId);
    console.log(`✅ Opened modal: ${modalId}`);
    setTimeout(() => {
      closeSection(modalId);
      console.log(`✅ Closed modal: ${modalId}`);
    }, 2000);
  };
  
  window.testScroll = function(sectionId) {
    scrollToSection(sectionId);
    console.log(`✅ Scrolled to section: ${sectionId}`);
  };
  
  window.testPasswordToggle = function() {
    const passwordInput = document.getElementById('admin-password');
    if (passwordInput) {
      togglePassword('admin-password');
      console.log(`✅ Password visibility toggled for admin-password`);
    }
  };
  
  console.log('• testModal("admin-login") - Test admin modal');
  console.log('• testModal("user-auth") - Test user modal');
  console.log('• testScroll("about") - Test smooth scrolling');
  console.log('• testPasswordToggle() - Test password toggle');
}

// Generate overall report
function generateReport() {
  console.log('\n📊 Component Verification Report');
  console.log('================================');
  
  const componentsPassed = Object.values(verificationResults.components).filter(Boolean).length;
  const componentsTotal = Object.keys(verificationResults.components).length;
  
  const functionalityPassed = Object.values(verificationResults.functionality).filter(Boolean).length;
  const functionalityTotal = Object.keys(verificationResults.functionality).length;
  
  const stylingPassed = Object.values(verificationResults.styling).filter(Boolean).length;
  const stylingTotal = Object.keys(verificationResults.styling).length;
  
  console.log(`Components: ${componentsPassed}/${componentsTotal} passed`);
  console.log(`Functionality: ${functionalityPassed}/${functionalityTotal} passed`);
  console.log(`Styling: ${stylingPassed}/${stylingTotal} passed`);
  
  const overallScore = ((componentsPassed + functionalityPassed + stylingPassed) / 
                       (componentsTotal + functionalityTotal + stylingTotal)) * 100;
  
  console.log(`\n🎯 Overall Score: ${overallScore.toFixed(1)}%`);
  
  if (overallScore >= 90) {
    console.log('🎉 Excellent! All components working properly.');
  } else if (overallScore >= 75) {
    console.log('✅ Good! Minor issues detected.');
  } else {
    console.log('⚠️ Issues detected. Please check failed tests.');
  }
}

// Run all tests
function runAllTests() {
  testComponentLoading();
  testJavaScriptFunctionality();
  testStyling();
  testAnimations();
  testResponsive();
  testForms();
  testExternalResources();
  manualTests();
  generateReport();
  
  console.log('\n🔧 Verification Complete!');
  console.log('========================');
  console.log('Run individual manual tests using the functions listed above.');
}

// Auto-run tests when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', runAllTests);
} else {
  runAllTests();
}