-- MotivLab Database Schema

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Portfolio Items Table
CREATE TABLE IF NOT EXISTS portfolio_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    category ENUM('school', 'restaurant', 'ecommerce', 'salon', 'realestate', 'logistics', 'events', 'portfolio') NOT NULL,
    description TEXT NOT NULL,
    long_description TEXT,
    featured_image VARCHAR(255),
    client_name VARCHAR(100),
    completion_date DATE,
    demo_url VARCHAR(255),
    status ENUM('published', 'draft') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio Gallery Table
CREATE TABLE IF NOT EXISTS portfolio_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(200),
    display_order INT DEFAULT 0,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio_items(id) ON DELETE CASCADE
);

-- Pricing Plans Table
CREATE TABLE IF NOT EXISTS pricing_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT NOT NULL,
    plan_name VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    features TEXT NOT NULL,
    delivery_time VARCHAR(50),
    support_level VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio_items(id) ON DELETE CASCADE
);

-- Blog Posts Table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL,
    excerpt TEXT,
    content TEXT NOT NULL,
    featured_image VARCHAR(255),
    author_id INT NOT NULL,
    status ENUM('published', 'draft') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admin_users(id)
);

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    business_name VARCHAR(100) NOT NULL,
    review_text TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    client_photo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    website_type VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Homepage Settings Table
CREATE TABLE IF NOT EXISTS homepage_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(100),
    site_logo VARCHAR(255),
    site_favicon VARCHAR(255),
    primary_color VARCHAR(7),
    secondary_color VARCHAR(7),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    whatsapp_number VARCHAR(20),
    address TEXT,
    facebook_url VARCHAR(255),
    twitter_url VARCHAR(255),
    instagram_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Default Admin User (password: admin123 - CHANGE THIS!)
INSERT INTO admin_users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@motivlab.name.ng', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'super_admin');

-- Insert Default Site Settings
INSERT INTO site_settings (site_name, contact_email, contact_phone, whatsapp_number, address) 
VALUES ('MotivLab', 'info@motivlab.name.ng', '+234 XXX XXX XXXX', '+234XXXXXXXXXX', 'Lagos, Nigeria');

-- Insert Default Homepage Settings
INSERT INTO homepage_settings (setting_key, setting_value) VALUES
('hero_headline', 'Grow Your Business With Smart, Modern Websites'),
('hero_subtext', 'Beautiful designs, fast performance, and powerful admin dashboards.'),
('hero_image', 'hero-mockup.png');