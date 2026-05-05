-- database.sql - تمام ساختار دیتابیس

-- ایجاد دیتابیس
CREATE DATABASE IF NOT EXISTS product_bot;
USE product_bot;

-- ===========================================
-- جدول 1: دسته‌بندی محصولات
-- ===========================================
CREATE TABLE IF NOT EXISTS categories (
                                          id INT PRIMARY KEY AUTO_INCREMENT,
                                          name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
    );

-- ===========================================
-- جدول 2: محصولات
-- ===========================================
CREATE TABLE IF NOT EXISTS products (
                                        id INT PRIMARY KEY AUTO_INCREMENT,
                                        category_id INT,
                                        name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(15,0) NOT NULL,
    price_old DECIMAL(15,0) DEFAULT NULL,
    color VARCHAR(100),
    size VARCHAR(100),
    image_url VARCHAR(500),
    in_stock BOOLEAN DEFAULT TRUE,
    quantity INT DEFAULT 0,
    views INT DEFAULT 0,
    clicks INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_in_stock (in_stock)
    );

-- ===========================================
-- جدول 3: کمپین‌های تبلیغاتی
-- ===========================================
CREATE TABLE IF NOT EXISTS campaigns (
                                         id INT PRIMARY KEY AUTO_INCREMENT,
                                         name VARCHAR(255) NOT NULL,
    description TEXT,
    product_ids TEXT,  -- JSON یا کاما جدا شده
    start_date DATETIME,
    end_date DATETIME,
    interval_seconds INT DEFAULT 86400,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'inactive',
    last_run DATETIME,
    messages_sent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
    );

-- ===========================================
-- جدول 4: تاریخچه ارسال پیام‌ها
-- ===========================================
CREATE TABLE IF NOT EXISTS messages_log (
                                            id INT PRIMARY KEY AUTO_INCREMENT,
                                            campaign_id INT,
                                            product_id INT,
                                            message_text TEXT,
                                            chat_id VARCHAR(100),
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign (campaign_id),
    INDEX idx_product (product_id),
    INDEX idx_status (status)
    );

-- ===========================================
-- جدول 5: آمار روزانه
-- ===========================================
CREATE TABLE IF NOT EXISTS daily_stats (
                                           id INT PRIMARY KEY AUTO_INCREMENT,
                                           stat_date DATE NOT NULL,
                                           messages_sent INT DEFAULT 0,
                                           products_shared INT DEFAULT 0,
                                           total_views INT DEFAULT 0,
                                           total_clicks INT DEFAULT 0,
                                           UNIQUE KEY unique_date (stat_date),
    INDEX idx_date (stat_date)
    );

-- ===========================================
-- جدول 6: کاربران (برای پنل مدیریت)
-- ===========================================
CREATE TABLE IF NOT EXISTS users (
                                     id INT PRIMARY KEY AUTO_INCREMENT,
                                     username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- ===========================================
-- درج اطلاعات نمونه (دمو)
-- ===========================================

-- درج دسته‌بندی‌ها
INSERT INTO categories (name, slug, sort_order) VALUES
                                                    ('الکترونیک', 'electronics', 1),
                                                    ('پوشاک', 'clothing', 2),
                                                    ('خانه و آشپزخانه', 'home', 3),
                                                    ('زیبایی و بهداشت', 'beauty', 4);

-- درج محصولات نمونه
INSERT INTO products (category_id, name, slug, description, price, price_old, color, in_stock, quantity, sort_order) VALUES
                                                                                                                         (1, 'هدفون بی‌سیم', 'wireless-headphone', 'هدفون با کیفیت عالی و باتری ۲۰ ساعته', 850000, 1200000, 'مشکی', TRUE, 15, 1),
                                                                                                                         (1, 'ساعت هوشمند', 'smart-watch', 'ساعت هوشمند با قابلیت اندازه‌گیری ضربان قلب', 2500000, 3200000, 'نقره‌ای', TRUE, 8, 2),
                                                                                                                         (2, 'تیشرت مردانه', 'men-tshirt', 'تیشرت نخودی با کیفیت عالی', 250000, NULL, 'سفید', TRUE, 30, 1),
                                                                                                                         (2, 'شومیز زنانه', 'women-shirt', 'شومیز طرح دار با پارچه خنک', 450000, NULL, 'طرح دار', FALSE, 0, 2),
                                                                                                                         (3, 'قوری چایخوری', 'teapot', 'قوری شیشه ای با استیل', 380000, 550000, 'شفاف', TRUE, 12, 1);

-- درج کمپین نمونه
INSERT INTO campaigns (name, description, product_ids, start_date, end_date, interval_seconds, status) VALUES
    ('کمپین روزانه', 'تبلیغ محصولات پرفروش هر روز', '[1,2,3]', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 86400, 'active');

-- درج کاربر ادمین (رمز: admin123)
INSERT INTO users (username, password, role) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ===========================================
-- ایجاد کاربر دیتابیس (اختیاری)
-- ===========================================
-- CREATE USER IF NOT EXISTS 'bot_user'@'localhost' IDENTIFIED BY 'strong_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON product_bot.* TO 'bot_user'@'localhost';
-- FLUSH PRIVILEGES;