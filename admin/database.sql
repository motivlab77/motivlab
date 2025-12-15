-- =========================================================================
-- MotivLab Website Project Database Schema
-- Last Updated: November 2025
-- Designed for: MySQL/MariaDB
-- =========================================================================

-- Ensure a clean environment by dropping the database and recreating it (OPTIONAL, use only if required)
-- DROP DATABASE IF EXISTS motivlab_db;
-- CREATE DATABASE motivlab_db;
-- USE motivlab_db;

-- --------------------------------------------------
-- 1. Table for Admin Users (Login Credentials & Roles)
-- --------------------------------------------------
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Stored as bcrypt hash
    email VARCHAR(100) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor') NOT NULL DEFAULT 'editor',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default Admin User (Password: 'admin123' hashed with bcrypt)
-- Note: You should generate this hash programmatically, but here's a common static one for development:
-- The hash below is for 'admin123'
INSERT INTO admin_users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$R77O4n4S0uJ2M6HwS4lGie2q/kY1yOQ9k/Q2w5L5s/8x0R6c3QzO6', 'admin@motivlab.com', 'Super Administrator', 'super_admin');

-- --------------------------------------------------
-- 2. Table for Portfolio Items (Projects)
-- --------------------------------------------------
CREATE TABLE portfolio_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    client_name VARCHAR(100),
    service_type VARCHAR(100),
    project_url VARCHAR(255),
    featured_image_id INT, -- Links to media_files table
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- 3. Table for Blog Posts
-- --------------------------------------------------
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    author_id INT, -- Links to admin_users table
    featured_image_id INT, -- Links to media_files table
    views INT DEFAULT 0,
    status ENUM('draft', 'published', 'scheduled') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- 4. Table for Blog Categories (for Posts)
-- --------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- 5. Table for Post-Category Relationships (Many-to-Many)
-- --------------------------------------------------
CREATE TABLE post_category_relations (
    post_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (post_id, category_id),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- 6. Table for Client Testimonials
-- --------------------------------------------------
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_role VARCHAR(100),
    company_name VARCHAR(100),
    quote TEXT NOT NULL,
    client_photo_id INT, -- Links to media_files table
    rating INT CHECK (rating BETWEEN 1 AND 5),
    status ENUM('pending', 'active', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- 7. Table for Contact Form Messages
-- --------------------------------------------------
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'archived') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- 8. Table for Media Manager (File Storage)
-- --------------------------------------------------
CREATE TABLE media_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(512) UNIQUE NOT NULL, -- e.g., /uploads/2025/image.jpg
    mime_type VARCHAR(50),
    file_size INT, -- Size in bytes
    alt_text VARCHAR(255),
    uploaded_by INT, -- Links to admin_users table
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- 9. Table for Site Settings (Global Key-Value Pairs)
-- --------------------------------------------------
CREATE TABLE settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add initial settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'MotivLab', 'The main name of the website.'),
('site_tagline', 'Digital Solutions for Modern Business', 'Short description or tagline.'),
('contact_email', 'info@motivlab.com', 'Primary contact email address.');

-- --------------------------------------------------
-- 10. Table for Pricing Plans (If Applicable - based on service_type)
-- --------------------------------------------------
CREATE TABLE pricing_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    billing_cycle ENUM('monthly', 'quarterly', 'annually', 'one-time') NOT NULL,
    features TEXT, -- JSON or comma-separated list of features
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_featured BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);