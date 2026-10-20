SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS blog_cms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_cms_db;

-- 1. Bảng người dùng (Users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    role VARCHAR(30) NOT NULL DEFAULT 'Author',
    fullname VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Bảng danh mục (Categories)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
);

-- 3. Bảng bài viết (Posts)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Chèn dữ liệu mẫu
INSERT INTO users (username, role, fullname) VALUES 
('khanhln_admin', 'Administrator', 'Lê Nam Khánh (DTC245220024)'),
('editor_user', 'Editor', 'Biên tập viên nội dung');

INSERT INTO categories (name, description) VALUES 
('An toàn thông tin', 'Các bài viết về bảo mật và Hardening hệ thống'),
('DevOps & Container', 'Triển khai hệ thống với Docker, Prometheus, Loki');

INSERT INTO posts (title, content, category_id, user_id) VALUES 
('Triển khai Nginx Reverse Proxy với HTTPS tự ký', 'Cấu hình thành công SSL/TLS và các Security Headers bảo vệ ứng dụng web.', 1, 1),
('Giám sát hạ tầng Container với Prometheus & Grafana', 'Thiết lập kiến trúc thu thập metrics và quản lý log tập trung bằng Loki.', 2, 1);

-- Hardening DB: Chỉ cấp quyền SELECT, INSERT, UPDATE, DELETE trên blog_cms_db cho wp_app_user (không có quyền DROP DATABASE)
GRANT SELECT, INSERT, UPDATE, DELETE ON blog_cms_db.* TO 'wp_app_user'@'%';

-- Tạo sẵn tài khoản riêng chỉ có quyền đọc thông số cho mysql-exporter ở Bước 2
CREATE USER IF NOT EXISTS 'exporter_user'@'%' IDENTIFIED BY 'Exporter@Pass2026!' WITH MAX_USER_CONNECTIONS 3;
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter_user'@'%';

FLUSH PRIVILEGES;
