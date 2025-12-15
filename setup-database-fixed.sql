-- Create database
CREATE DATABASE IF NOT EXISTS motivlab_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with proper permissions
CREATE USER IF NOT EXISTS 'motivlab_admin'@'localhost' IDENTIFIED BY 'Admin@2024Pass';
GRANT ALL PRIVILEGES ON motivlab_db.* TO 'motivlab_admin'@'localhost';
FLUSH PRIVILEGES;

-- Use the database
USE motivlab_db;

-- Drop tables if they exist to start fresh
DROP TABLE IF EXISTS site_settings;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS portfolio_items;
DROP TABLE IF EXISTS admin_users;

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Portfolio items table - FIXED: Added category column
CREATE TABLE portfolio_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),  -- ADDED THIS COLUMN
    featured_image VARCHAR(500),
    project_url VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(500),
    status ENUM('published', 'draft') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Testimonials table
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    business_name VARCHAR(100),
    review_text TEXT NOT NULL,
    rating INT DEFAULT 5,
    client_photo VARCHAR(500),
    status ENUM('pending', 'approved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, full_name, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@motivlab.name.ng');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'MotivLab'),
('hero_headline', 'Professional Website Solutions for Nigerian Businesses'),
('hero_subtext', 'Get modern, fast websites with powerful admin dashboards. Schools, Restaurants, eCommerce, and more.'),
('contact_phone', '+234 812 345 6789'),
('contact_email', 'info@motivlab.name.ng'),
('whatsapp_number', '+2348123456789'),
('site_logo', ''),
('hero_image', '');

-- Insert sample portfolio items - NOW WITH CATEGORY COLUMN
INSERT INTO portfolio_items (title, description, category, featured_image) VALUES
('Victory Schools', 'Complete school management system with admin dashboard', 'Education', 'assets/images/school-project.jpg'),
('Food Palace Restaurant', 'Online ordering system with menu management', 'Restaurant', 'assets/images/restaurant-project.jpg'),
('Fashion Store NG', 'E-commerce website with product management', 'eCommerce', 'assets/images/ecommerce-project.jpg');

-- Insert sample testimonials
INSERT INTO testimonials (client_name, business_name, review_text, rating, status) VALUES
('Mrs. Adeyemi', 'Victory International School', 'MotivLab transformed our school operations. The admin dashboard makes managing students, results, and admissions so easy!', 5, 'approved'),
('Mr. Chukwu', 'Food Palace Restaurant', 'Our online orders increased by 200% after getting the new website. The admin panel makes menu updates effortless.', 5, 'approved');

-- Verify the data was inserted
SELECT 'Database setup completed successfully!' as status;

-- Show all tables
SHOW TABLES;

-- Show portfolio items structure and data
DESCRIBE portfolio_items;
SELECT * FROM portfolio_items;
