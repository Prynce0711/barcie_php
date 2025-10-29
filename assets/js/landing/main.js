// Landing Page JavaScript Functionality
// BarCIE International Center Landing Page

document.addEventListener('DOMContentLoaded', function() {
  // Initialize AOS (Animate On Scroll)
  AOS.init({
    duration: 1000,
    once: true,
    offset: 100
  });

  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.getElementById('mainNavbar');
    if (navbar && window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else if (navbar) {
      navbar.classList.remove('scrolled');
    }
  });

  // Update active nav link on scroll
  window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 100;
      if (pageYOffset >= sectionTop) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === `#${current}`) {
        link.classList.add('active');
      }
    });
  });

  // Close modal when clicking outside
  document.addEventListener('click', function(e) {
    const modals = document.querySelectorAll('.content-section');
    modals.forEach(modal => {
      if (!modal.classList.contains('hidden') && e.target === modal) {
        closeSection(modal.id);
      }
    });
  });

  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const openModals = document.querySelectorAll('.content-section:not(.hidden)');
      openModals.forEach(modal => {
        closeSection(modal.id);
      });
    }
  });

  // Loading animation for page
  window.addEventListener('load', function() {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease';
    setTimeout(() => {
      document.body.style.opacity = '1';
    }, 100);
  });
});

// Smooth scrolling for anchor links
function scrollToSection(sectionId) {
  const section = document.getElementById(sectionId);
  if (section) {
    section.scrollIntoView({
      behavior: 'smooth'
    });
  }
}

// Show/Hide Sections for modals
function closeSection(id) {
  const element = document.getElementById(id);
  if (element) {
    element.classList.add('hidden');
    document.body.style.overflow = 'auto';
  }
}

function showSection(id) {
  document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('hidden'));
  const element = document.getElementById(id);
  if (element) {
    element.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

// Password Toggle
function togglePassword(id) {
  const input = document.getElementById(id);
  if (input) {
    input.type = input.type === "password" ? "text" : "password";
  }
}