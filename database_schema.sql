-- =============================================
-- PizzaHut Ghana Database Schema
-- =============================================
-- Run this SQL in phpMyAdmin to create all tables
-- =============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS pizzahut_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pizzahut_db;

-- =============================================
-- 1. Admin Users Table
-- =============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, full_name, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pizzahut.com.gh');

-- =============================================
-- 2. Menu Items Table
-- =============================================
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    category VARCHAR(50) DEFAULT 'Pizza',
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_available (is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, image_path) VALUES
('Pepperoni Classic', 'Spicy pepperoni with double cheese on crispy crust', 45.00, 'Images/pepperoni_cheese_pizza-1.jpg'),
('Cheese Overload', 'Extra cheesy, soft base, perfect for cheese lovers', 40.00, 'Images/maxresdefault.jpg'),
('Loaded Supreme', 'All your favourite toppings in one delicious box', 52.00, 'Images/l-intro-1692046780.jpg'),
('Cottage Crust', 'Light cottage cheese crust, crispy outside, soft inside', 48.00, 'Images/Cottage-Cheese-Pizza-Crust-10-of-10.jpg'),
('4 Cheese Pizza', 'Four rich cheeses melted on a golden crust', 50.00, 'Images/4-Cheese_Pizza_2.jpg'),
('Crispy Pan', 'Thick, crispy pan base with rich tomato sauce', 55.00, 'Images/crispy-pan-pizza-1-2-scaled.webp'),
('Ghana Special', 'Spiced to match the authentic Ghanaian taste', 47.00, 'Images/pepperoni_cheese_pizza-1.jpg'),
('Chef\'s Choice', 'Rotating specials hand-picked by our master chefs', 60.00, 'Images/maxresdefault.jpg');

-- =============================================
-- 3. Customer Orders Table
-- =============================================
CREATE TABLE IF NOT EXISTS customer_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    menu_item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    item_price DECIMAL(10, 2) NOT NULL,
    order_status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_order_number (order_number),
    INDEX idx_customer_phone (customer_phone),
    INDEX idx_order_status (order_status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. Contact Messages Table
-- =============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- View to get unread notification count
-- =============================================
CREATE OR REPLACE VIEW notification_count AS
SELECT 
    (SELECT COUNT(*) FROM contact_messages WHERE is_read = FALSE) as unread_messages,
    (SELECT COUNT(*) FROM customer_orders WHERE order_status = 'pending') as pending_orders,
    ((SELECT COUNT(*) FROM contact_messages WHERE is_read = FALSE) + 
     (SELECT COUNT(*) FROM customer_orders WHERE order_status = 'pending')) as total_notifications;

-- =============================================
-- Done! All tables created successfully
-- =============================================
