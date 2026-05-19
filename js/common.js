// =============================================
//  VELORA LENS STUDIO — Shared JavaScript
//  Used by ALL pages: navbar, hamburger, fade-in
// =============================================

document.addEventListener('DOMContentLoaded', function () {
    initNavbar();
    initHamburger();
    initFadeIn();
    setActiveNavLink();
});

// Navbar scroll effect
function initNavbar() {
    var navbar = document.getElementById('navbar');
    if (!navbar) return;
    window.addEventListener('scroll', function () {
        if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Mobile hamburger menu
function initHamburger() {
    var hamburger = document.getElementById('hamburger');
    var navMenu   = document.getElementById('navMenu');
    if (!hamburger || !navMenu) return;
    hamburger.addEventListener('click', function () {
        navMenu.classList.toggle('open');
    });
    var navLinks = navMenu.querySelectorAll('.nav-link');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            navMenu.classList.remove('open');
        });
    });
}

// Fade-in scroll reveal
function initFadeIn() {
    var elements = document.querySelectorAll('.fade-in');
    function check() {
        elements.forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight - 80) {
                el.classList.add('visible');
            }
        });
    }
    check();
    window.addEventListener('scroll', check);
}

// Highlight active nav link based on current page
function setActiveNavLink() {
    var currentPage = window.location.pathname.split('/').pop() || 'index.html';
    var links = document.querySelectorAll('.nav-link');
    links.forEach(function (link) {
        var href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
}
