-- SHOP & TUFF database — fresh install.
-- phpMyAdmin > Import > choose this file > Go.  (Creates database ecom_store.)
-- WARNING: drops and recreates products, users, orders, order_items.
CREATE DATABASE IF NOT EXISTS ecom_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecom_store;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS products;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(500) NOT NULL,
  description TEXT,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  email VARCHAR(150) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100),
  zip VARCHAR(20) NOT NULL,
  country VARCHAR(100) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  shipping DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

INSERT INTO products (id, name, category, price, image, description) VALUES
  (1, 'short baggy jeans', 'new arrivals', 17, 'https://i.pinimg.com/1200x/aa/25/27/aa2527818fb3e7cfbea600b45ea69f94.jpg', 'stylish baggy jeans for men.'),
  (2, 'adidas', 'Shoes', 75, 'https://i.pinimg.com/1200x/06/75/f6/0675f6e6e4bc7a55ee5efee5bfe2186b.jpg', 'clean fit use this.'),
  (3, 'baggy jeans', 'Jeans', 17, 'https://i.pinimg.com/1200x/97/1d/cb/971dcb02e1d092fbb8b0c7b0b020ec8c.jpg', 'stylish baggy jeans for men.'),
  (4, 't-shirt', 'T-shirts', 22, 'https://i.pinimg.com/1200x/e8/50/f4/e850f4db13e7adb1204d3367e89dcbda.jpg', 'fit t-shirt for men.'),
  (5, 'adidas new', 'Shoes', 120, 'https://i.pinimg.com/736x/66/90/ca/6690ca7846e16be973d134ec95088732.jpg', 'adidas new shoes.'),
  (6, 'fit', 'Outfit', 40, 'https://i.pinimg.com/736x/6b/a3/d1/6ba3d13fc706952977b0c7bbb91226f7.jpg', 'fit for men.'),
  (7, 'glass', 'new arrivals', 17, 'https://i.pinimg.com/736x/e5/ab/f6/e5abf66f5bf4d0352f03b132865cfe29.jpg', 'glass for men.'),
  (8, 'date fits', 'Outfit', 75, 'https://i.pinimg.com/736x/75/3c/89/753c89a28e3eec955e53e842160d63eb.jpg', 'date ur girl.'),
  (9, 'outside', 'Outfit', 17, 'https://i.pinimg.com/1200x/8e/62/d6/8e62d6011ddf81115be7001be9592b33.jpg', 'stylish baggy jeans for men.'),
  (10, 'vintage t-shirt', 'T-shirts', 22, 'https://i.pinimg.com/736x/14/8c/ce/148ccefbf6488c700f9064fe69cb0eab.jpg', 'vintage shirt.'),
  (11, 'Usa jeans', 'Jeans', 17, 'https://i.pinimg.com/1200x/af/76/bd/af76bd5a7a965d185872477fd9380069.jpg', 'damm.'),
  (12, 'diamond jeans', 'Jeans', 20, 'https://i.pinimg.com/1200x/9a/0b/ac/9a0bacd14d3d60eb29d2ea8ee59812b5.jpg', 'diamond pattern jeans.');

-- To make yourself an admin: register on the site first, then run
--   UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
