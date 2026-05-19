# Velora Lens Studio - Future Work & Roadmap

This document outlines potential features, improvements, and next steps for the Velora Lens Studio project.

## 1. Dynamic Content Management (Admin Panel)
Currently, gallery images and pricing packages are hardcoded into the HTML files.
- **Packages Management:** Build out the "Packages" tab in the Admin Dashboard to allow adding, editing, and deleting packages. Update `pricing.html` to fetch this data dynamically from the database via an API endpoint.
- **Gallery Management:** Build out the "Gallery Items" tab to allow uploading new photos, assigning categories, and deleting old ones. Update `gallery.html` to pull images dynamically.

## 2. Email Notifications (PHPMailer)
Currently, the system only updates the database without sending external notifications.
- **Booking Confirmation:** Send an automated email to the client when they submit a booking.
- **Status Updates:** Send an email to the client when an Admin confirms, completes, or cancels their booking.
- **Welcome Email:** Send a welcome email when a new user registers for the Client Portal.
- **Admin Alerts:** Notify the admin via email when a new booking or contact message is received.

## 3. Advanced Client Portal Features
- **Forgot Password Flow:** Add a password recovery system allowing clients to reset their passwords securely via an emailed link.
- **Profile Management:** Add a "My Profile" tab in the client dashboard so users can update their personal information (e.g., phone number) or change their password.
- **Invoice/Receipt Generation:** Generate downloadable PDF invoices or receipts for confirmed and completed bookings.

## 4. Security & Hardening
- **CSRF Tokens:** Implement Cross-Site Request Forgery (CSRF) protection on all forms (login, register, booking, contact) to prevent malicious automated submissions.
- **Rate Limiting:** Add login attempt rate limiting to prevent brute-force attacks on both the Admin and User login pages.
- **Input Sanitization:** Perform a comprehensive audit to ensure all user inputs are strictly sanitized before being rendered to the page (to prevent XSS) and securely parameterized (already implemented for SQLi).

## 5. Live Deployment
Steps required to take the application live on the internet:
- **Domain & Hosting:** Purchase a domain (e.g., `veloralens.pk`) and configure web hosting (e.g., Hostinger, Namecheap).
- **Database Migration:** Export the local MySQL database and import it into the live server's database.
- **Environment Configuration:** Update `php/config.php` with the live database credentials and ensure debugging errors are disabled in production.
- **SSL Certificate:** Configure HTTPS to ensure all data (especially passwords and personal information) is encrypted in transit.

## 6. Frontend Polish
- **SEO Optimization:** Add proper `<meta>` description, keywords, and Open Graph tags to all HTML pages for better search engine ranking.
- **Favicon:** Add a branded favicon to all pages.
- **Dynamic Footer Links:** Make footer contact links (phone, email, WhatsApp) clickable with `tel:`, `mailto:`, and WhatsApp API links.
