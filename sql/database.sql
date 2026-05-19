-- ============================================================
--  VELORA LENS STUDIO — Full MySQL Database Setup
--  HOW TO USE:
--    1. Open phpMyAdmin → http://localhost/phpmyadmin/
--    2. Click the "SQL" tab
--    3. Paste ALL of this and click "Go"
--    All tables + sample data will be created automatically.
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS photography_booking
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE photography_booking;

-- ============================================================
--  TABLE 1: bookings
--  Stores all booking form submissions from booking.html
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL           COMMENT 'Client full name',
    email       VARCHAR(150)  NOT NULL           COMMENT 'Client email',
    phone       VARCHAR(25)   NOT NULL           COMMENT 'Client phone number',
    event_type  VARCHAR(60)   NOT NULL           COMMENT 'Type of event (Wedding, Mehndi, etc)',
    event_date  DATE          NOT NULL           COMMENT 'Date of the event',
    location    VARCHAR(150)  NOT NULL           COMMENT 'Event city/venue',
    package     VARCHAR(60)   DEFAULT NULL       COMMENT 'Preferred package selected',
    message     TEXT          DEFAULT NULL       COMMENT 'Additional notes from client',
    status      ENUM('New','Confirmed','Cancelled','Completed')
                              DEFAULT 'New'      COMMENT 'Booking status',
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email      (email),
    INDEX idx_event_date (event_date),
    INDEX idx_status     (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 2: contact_messages
--  Stores all messages submitted via contact.html
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL          COMMENT 'Sender name',
    email      VARCHAR(150)  NOT NULL          COMMENT 'Sender email',
    phone      VARCHAR(25)   DEFAULT NULL      COMMENT 'Sender phone (optional)',
    subject    VARCHAR(200)  DEFAULT NULL      COMMENT 'Message subject',
    message    TEXT          NOT NULL          COMMENT 'Message body',
    is_read    TINYINT(1)    DEFAULT 0         COMMENT '0 = unread, 1 = read',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email   (email),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 3: packages
--  Master list of photography packages offered
-- ============================================================
CREATE TABLE IF NOT EXISTS packages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL         COMMENT 'Package display name',
    tag         VARCHAR(50)  NOT NULL         COMMENT 'Category tag (Basic/Standard/Premium)',
    price_pkr   DECIMAL(10,2) NOT NULL        COMMENT 'Price in Pakistani Rupees',
    duration    VARCHAR(80)  NOT NULL         COMMENT 'Coverage duration description',
    features    TEXT         NOT NULL         COMMENT 'Comma-separated list of features',
    is_featured TINYINT(1)   DEFAULT 0        COMMENT '1 = highlighted as Most Popular',
    is_active   TINYINT(1)   DEFAULT 1        COMMENT '1 = visible on site',
    sort_order  INT          DEFAULT 0,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE 4: gallery_items
--  Tracks images used in the gallery with metadata
-- ============================================================
CREATE TABLE IF NOT EXISTS gallery_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename    VARCHAR(100) NOT NULL         COMMENT 'Image filename (e.g. img1.jpg)',
    alt_text    VARCHAR(200) NOT NULL         COMMENT 'Image alt text / caption',
    category    VARCHAR(60)  NOT NULL         COMMENT 'Gallery category for filter',
    sort_order  INT          DEFAULT 0        COMMENT 'Display order',
    is_active   TINYINT(1)   DEFAULT 1,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  SAMPLE DATA: bookings
-- ============================================================
INSERT INTO bookings (name, email, phone, event_type, event_date, location, package, message, status) VALUES
('Ali Ahmed',       'ali.ahmed@email.com',    '+92 300 1111111', 'Wedding',     '2026-06-15', 'Pearl Continental, Lahore',        'Premium',  'Full Barat + Walima coverage needed.',         'Confirmed'),
('Sara Khan',       'sara.khan@email.com',    '+92 321 2222222', 'Mehndi',      '2026-07-20', 'Karachi',                           'Standard', 'Evening mehndi ceremony, 5 hours.',           'New'),
('Fatima Malik',    'fatima.m@email.com',     '+92 333 3333333', 'PreWedding',  '2026-08-01', 'Shalimar Garden, Lahore',           'Basic',    'Outdoor pre-wedding shoot at sunset.',        'New'),
('Usman Raza',      'usman.r@email.com',      '+92 311 4444444', 'Wedding',     '2026-09-10', 'Islamabad',                         'Premium',  'Full day coverage with drone.',               'Confirmed'),
('Hina Nawaz',      'hina.nawaz@email.com',   '+92 345 5555555', 'Fashion',     '2026-05-25', 'Velora Studio, Lahore',             'Basic',    'Bridal portfolio shoot.',                     'Completed'),
('Ahmed Sultan',    'ahmed.s@email.com',      '+92 300 6666666', 'Event',       '2026-10-05', 'Multan',                            'Standard', 'Corporate gala dinner coverage.',             'New'),
('Zara Siddiqui',   'zara.s@email.com',       '+92 321 7777777', 'Wedding',     '2026-11-12', 'Avari Hotel, Lahore',               'Premium',  'Complete 3-day wedding package.',             'New'),
('Danish Qureshi',  'danish.q@email.com',     '+92 333 8888888', 'Portrait',    '2026-06-30', 'Lahore',                            'Basic',    'Family portrait session.',                    'Confirmed');


-- ============================================================
--  SAMPLE DATA: contact_messages
-- ============================================================
INSERT INTO contact_messages (name, email, phone, subject, message, is_read) VALUES
('Nadia Akhtar',    'nadia.a@email.com',    '+92 300 9191919', 'Pricing Query',         'Hi! I wanted to ask about your packages for a wedding in December 2026. Can you please send me a detailed quote?',                            1),
('Kamran Shafi',    'kamran.s@email.com',   '+92 321 8282828', 'Availability Check',    'Are you available on 15th October 2026 for a walima in Rawalpindi? Please confirm as soon as possible.',                                      0),
('Maryam Ismail',   'maryam.i@email.com',   NULL,              'Portfolio Request',     'I saw your work on Instagram and absolutely loved it! Could you share more examples of indoor barat photography?',                            0),
('Hassan Mirza',    'hassan.m@email.com',   '+92 311 7373737', 'Drone Coverage',        'Do you offer drone coverage for outdoor wedding venues? We are planning an outdoor ceremony in Lahore in February.',                          1),
('Saba Tariq',      'saba.t@email.com',     NULL,              'Custom Package',        'We need photography + full cinematic film but our budget is limited. Can we discuss a custom package within PKR 120,000?',                   0);


-- ============================================================
--  SAMPLE DATA: packages
-- ============================================================
INSERT INTO packages (name, tag, price_pkr, duration, features, is_featured, sort_order) VALUES
('Silver Moments', 'Basic',    35000.00,  '2-Hour Session',      '2-Hour Photography,50 Edited Images,1 Photographer,HD Delivery,Online Gallery,7-Day Delivery',                                          0, 1),
('Golden Stories', 'Standard', 85000.00,  '5-Hour Coverage',     '5-Hour Photography,150 Edited Photos,2 Photographers,Video Highlights Reel,Online Gallery,5-Day Delivery',                              1, 2),
('Royal Legacy',   'Premium',  185000.00, 'Full Wedding Day',    'Full Day Coverage,400+ Edited Images,3 Photographers,Full Cinematic Film,Drone Aerial Coverage,Same Day Highlight,USB Delivery,Pre-Wedding Shoot,Rush Delivery,Project Manager', 0, 3);


-- ============================================================
--  SAMPLE DATA: gallery_items
-- ============================================================
INSERT INTO gallery_items (filename, alt_text, category, sort_order) VALUES
('img1.jpg',  'Royal Wedding Ceremony',       'wedding',  1),
('img2.jpg',  'Mehndi Night Celebration',     'mehndi',   2),
('img3.jpg',  'Mountain Couple Shoot',        'outdoor',  3),
('img4.jpg',  'Grand Barat Night',            'barat',    4),
('img5.jpg',  'Walima Celebration',           'wedding',  5),
('img6.jpg',  'Bridal Fashion Shoot',         'fashion',  6),
('img7.jpg',  'Aerial Wedding Shot',          'drone',    7),
('img8.jpg',  'Autumn Outdoor Session',       'outdoor',  8),
('img9.jpg',  'Luxury Fashion Editorial',     'fashion',  9),
('img10.jpg', 'Studio Event Coverage',        'event',   10),
('img11.jpg', 'Barat Portrait',               'barat',   11),
('img12.jpg', 'Mehndi Evening',               'mehndi',  12);


-- ============================================================
--  TABLE 5: admins
--  Admin user accounts for the backend panel
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(60)    NOT NULL UNIQUE    COMMENT 'Admin login username',
    password_hash VARCHAR(255)   NOT NULL           COMMENT 'bcrypt password hash via password_hash()',
    full_name     VARCHAR(100)   NOT NULL           COMMENT 'Display name shown in sidebar',
    email         VARCHAR(150)   DEFAULT NULL       COMMENT 'Admin email (optional)',
    is_active     TINYINT(1)     DEFAULT 1          COMMENT '1 = active, 0 = disabled',
    last_login    DATETIME       DEFAULT NULL       COMMENT 'Timestamp of last successful login',
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_username  (username),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  DEFAULT ADMIN ACCOUNT
--  Username : admin
--  Password : Admin@1234
--  ⚠️  CHANGE THIS PASSWORD after your first login!
--  To generate a new hash run in PHP:
--      echo password_hash('YourNewPassword', PASSWORD_BCRYPT);
-- ============================================================
INSERT INTO admins (username, password_hash, full_name, email) VALUES
(
    'admin',
    '$2y$10$LTmDaXirEKTg103XknonE.2Hi9Zl5KNYRl67XEi0TZy4BASFax1cG',
    'Velora Admin',
    'admin@veloralens.pk'
);
-- ⚠️ Default password is: Admin@1234  — change after first login!


-- ============================================================
--  TABLE 6: users
--  Client accounts for the user portal (register / login)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)   NOT NULL           COMMENT 'Client full name',
    email         VARCHAR(150)   NOT NULL UNIQUE     COMMENT 'Login email (must match booking email)',
    phone         VARCHAR(25)    DEFAULT NULL        COMMENT 'Optional phone number',
    password_hash VARCHAR(255)   NOT NULL            COMMENT 'bcrypt password hash',
    is_active     TINYINT(1)     DEFAULT 1           COMMENT '1 = active, 0 = banned',
    last_login    DATETIME       DEFAULT NULL,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  MIGRATION: link bookings to users via user_id
--  Run this ONCE after creating the users table.
--  Safe to skip if starting fresh (bookings table is new).
-- ============================================================
-- ALTER TABLE bookings ADD COLUMN user_id INT UNSIGNED DEFAULT NULL AFTER id;
-- ALTER TABLE bookings ADD INDEX idx_user_id (user_id);
-- ALTER TABLE bookings ADD CONSTRAINT fk_booking_user
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;


-- ============================================================
--  USEFUL QUERIES (copy into phpMyAdmin to run)
-- ============================================================

-- View all bookings newest first:
-- SELECT * FROM bookings ORDER BY created_at DESC;

-- View only new bookings:
-- SELECT * FROM bookings WHERE status = 'New' ORDER BY event_date ASC;

-- View unread contact messages:
-- SELECT * FROM contact_messages WHERE is_read = 0;

-- Count bookings by event type:
-- SELECT event_type, COUNT(*) as total FROM bookings GROUP BY event_type;

-- Total revenue from completed bookings (by package):
-- SELECT b.package, p.price_pkr, COUNT(*) as count
-- FROM bookings b
-- LEFT JOIN packages p ON b.package = p.tag
-- WHERE b.status = 'Completed'
-- GROUP BY b.package;
