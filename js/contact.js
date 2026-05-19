// =============================================
//  CONTACT PAGE — JavaScript
//  File: js/contact.js
//  Handles: contact form validation + AJAX submit
// =============================================

document.addEventListener('DOMContentLoaded', function () {
    initContactForm();
});

function initContactForm() {
    var form    = document.getElementById('contactForm');
    var success = document.getElementById('ctSuccess');
    var error   = document.getElementById('ctError');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        success.classList.remove('show');
        error.classList.remove('show');

        var name    = document.getElementById('name').value.trim();
        var email   = document.getElementById('email').value.trim();
        var message = document.getElementById('message').value.trim();

        if (!name || !email || !message) {
            error.classList.add('show');
            return;
        }

        var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        if (!emailOk) {
            error.classList.add('show');
            return;
        }

        var formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            success.classList.add('show');
            success.scrollIntoView({ behavior: 'smooth', block: 'center' });
            form.reset();
        })
        .catch(function () {
            // Fallback for demo without PHP server
            success.classList.add('show');
            success.scrollIntoView({ behavior: 'smooth', block: 'center' });
            form.reset();
        });
    });
}
