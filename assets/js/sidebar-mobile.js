// Mobile sidebar toggle functions
function toggleSidebar() { const sidebar = document.querySelector('.sidebar-guest'); const overlay = document.querySelector('.sidebar-overlay'); if (sidebar.classList.contains('open')) { closeSidebar(); } else { openSidebar(); } }
function openSidebar() { const sidebar = document.querySelector('.sidebar-guest'); const overlay = document.querySelector('.sidebar-overlay'); sidebar.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
function closeSidebar() { const sidebar = document.querySelector('.sidebar-guest'); const overlay = document.querySelector('.sidebar-overlay'); sidebar.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }

document.addEventListener('DOMContentLoaded', function () { const navButtons = document.querySelectorAll('.sidebar-guest button[onclick*="showSection"]'); navButtons.forEach(button => { button.addEventListener('click', function () { if (window.innerWidth <= 768) { setTimeout(closeSidebar, 300); } }); }); window.addEventListener('resize', function () { if (window.innerWidth > 768) { closeSidebar(); } }); });
