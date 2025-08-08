-- コメント機能用のテーブル作成

-- laravel_appデータベースを使用
USE laravel_app;

-- コメントテーブル
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- サンプルコメントデータの挿入
INSERT INTO comments (post_id, user_id, content, status) VALUES
(1, 2, 'Very helpful article! Thank you for the detailed explanation about Laravel.', 'approved'),
(1, 3, 'Great content for beginners. Looking forward to more detailed implementation examples.', 'approved'),
(2, 1, 'PHP basics are explained very clearly. Thank you!', 'approved'),
(2, 2, 'The grammar explanation is very thorough and helpful for learning.', 'approved'),
(4, 1, 'Practical advice about web development best practices. Very useful!', 'approved'),
(4, 3, 'Modern development techniques are very educational. Thank you!', 'pending');

-- テーブル一覧の確認
SHOW TABLES;

-- コメントテーブルの構造確認
DESCRIBE comments;

-- サンプルコメントデータの確認
SELECT c.content, u.name as author, p.title as post_title, c.status, c.created_at
FROM comments c
JOIN users u ON c.user_id = u.id
JOIN posts p ON c.post_id = p.id
ORDER BY c.created_at DESC; 