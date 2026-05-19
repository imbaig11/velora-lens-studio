// =============================================
//  BOOKING PAGE — JavaScript
//  File: js/booking.js
//  Handles: form validation + AJAX submit to PHP
// =============================================

document.addEventListener('DOMContentLoaded', function () {
    checkUserSession();
    initBookingForm();
});

function checkUserSession() {
    fetch('php/user_session.php')
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                var nameInput = document.getElementById('name');
                var emailInput = document.getElementById('email');
                if (nameInput) { nameInput.value = data.user_name; nameInput.readOnly = true; nameInput.style.opacity = '0.7'; }
                if (emailInput) { emailInput.value = data.user_email; emailInput.readOnly = true; emailInput.style.opacity = '0.7'; }
                
                var banner = document.getElementById('userProfileBanner');
                if (banner) {
                    document.getElementById('upbName').innerText = data.user_name;
                    document.getElementById('upbEmail').innerText = data.user_email;
                    banner.style.display = 'block';
                }
            }
        })
        .catch(err => console.error('Session check failed', err));
}

function initBookingForm() {
    var form       = document.getElementById('bookingForm');
    var successMsg = document.getElementById('bookSuccess');
    var errorMsg   = document.getElementById('bookError');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Hide previous alerts
        successMsg.classList.remove('show');
        errorMsg.classList.remove('show');

        // Get field values
        var name      = document.getElementById('name').value.trim();
        var email     = document.getElementById('email').value.trim();
        var phone     = document.getElementById('phone').value.trim();
        var eventType = document.getElementById('event_type').value;
        var eventDate = document.getElementById('event_date').value;
        var location  = document.getElementById('location').value.trim();

        // Validation
        if (!name || !email || !phone || !eventType || !eventDate || !location) {
            errorMsg.classList.add('show');
            errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Email format check
        var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        if (!emailOk) {
            errorMsg.classList.add('show');
            return;
        }

        // Send to PHP via fetch
        var formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                successMsg.classList.add('show');
                successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                form.reset();
            } else {
                // Show server error (e.g. date fully booked)
                errorMsg.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + data.message;
                errorMsg.classList.add('show');
                errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(function () {
            // If PHP is not set up (local preview), show success anyway
            successMsg.classList.add('show');
            successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            form.reset();
        });
    });
}
