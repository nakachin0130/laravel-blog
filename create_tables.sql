-- Laravelアプリケーション用の基本的なテーブル作成

-- laravel_appデータベースを使用
USE laravel_app;

-- ユーザーテーブル
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 記事テーブル
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- カテゴリテーブル
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 記事とカテゴリの関連テーブル
CREATE TABLE IF NOT EXISTS post_category (
    post_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (post_id, category_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- サンプルデータの挿入

-- ユーザーのサンプルデータ
INSERT INTO users (name, email, password) VALUES
('Admin User', 'admin@example.com', 'password123'),
('Test User', 'test@example.com', 'password123'),
('Developer', 'developer@example.com', 'password123');

-- カテゴリのサンプルデータ
INSERT INTO categories (name, description) VALUES
('Technology', 'Programming and technology articles'),
('Lifestyle', 'Daily life articles'),
('Travel', 'Travel articles');

-- 記事のサンプルデータ
INSERT INTO posts (title, content, user_id, status) VALUES
('Getting Started with Laravel', 'Laravel is a wonderful PHP framework.', 1, 'published'),
('PHP Basics', 'Basic PHP syntax explanation.', 1, 'published'),
('Database Design', 'Efficient database design methods.', 2, 'draft'),
('Web Development Best Practices', 'Modern web development techniques.', 3, 'published');

-- 記事とカテゴリの関連付け
INSERT INTO post_category (post_id, category_id) VALUES
(1, 1), -- Getting Started with Laravel -> Technology
(2, 1), -- PHP Basics -> Technology
(3, 1), -- Database Design -> Technology
(4, 1); -- Web Development Best Practices -> Technology

-- テーブル一覧の確認
SHOW TABLES;

-- 各テーブルの構造確認
DESCRIBE users;
DESCRIBE posts;
DESCRIBE categories;
DESCRIBE post_category;

-- サンプルデータの確認
SELECT 'Users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'Posts', COUNT(*) FROM posts
UNION ALL
SELECT 'Categories', COUNT(*) FROM categories;

-- 記事とユーザーの関連データ確認
SELECT p.title, u.name as author, p.status, p.created_at
FROM posts p
JOIN users u ON p.user_id = u.id
ORDER BY p.created_at DESC; 