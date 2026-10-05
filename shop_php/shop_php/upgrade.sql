-- Run this if you already imported the PREVIOUS version and want to keep your data.
-- (XAMPP's MariaDB supports ADD COLUMN IF NOT EXISTS.)
USE ecom_store;

ALTER TABLE products ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users    ADD COLUMN IF NOT EXISTS role ENUM('customer','admin') NOT NULL DEFAULT 'customer';
ALTER TABLE orders   ADD COLUMN IF NOT EXISTS status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending';

-- Then make yourself admin:
-- UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
