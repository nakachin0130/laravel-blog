<?php
/**
 * 個別記事詳細表示機能
 * 記事IDに基づいて記事の詳細を表示
 */

// エラー表示を有効にする（開発環境用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// 認証機能を読み込み
require_once 'auth.php';

// 記事IDを取得
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// データベース設定
$db_config = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'laravel_app',
    'username' => 'root',
    'password' => 'nh01300130',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];

// データベース接続と記事取得
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 記事詳細を取得
    $query = "
        SELECT 
            p.id,
            p.title,
            p.content,
            p.image_path,
            p.status,
            p.created_at,
            p.updated_at,
            u.name as author_name,
            u.email as author_email,
            GROUP_CONCAT(c.name SEPARATOR ', ') as categories
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN post_category pc ON p.id = pc.post_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE p.id = ?
        GROUP BY p.id
    ";
    
    // コメント一覧を取得
    $comments_query = "
        SELECT 
            c.id,
            c.content,
            c.status,
            c.created_at,
            c.user_id,
            u.name as author_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ? AND c.status = 'approved'
        ORDER BY c.created_at ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        $error_message = "記事が見つかりません。";
    } else {
        // コメントを取得
        $comments_stmt = $pdo->prepare($comments_query);
        $comments_stmt->execute([$post_id]);
        $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
    $post = null;
    $comments = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) : '記事詳細'; ?> - Laravel アプリケーション</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .post-header {
            margin-bottom: 30px;
        }
        .post-title {
            font-size: 2em;
            color: #333;
            margin-bottom: 15px;
        }
        .post-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .post-content {
            color: #333;
            line-height: 1.8;
            font-size: 1.1em;
            margin-bottom: 30px;
        }
        .post-categories {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .category-tag {
            background-color: #007bff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-published {
            background-color: #d4edda;
            color: #155724;
        }
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
        }
        .nav-links {
            margin-bottom: 30px;
        }
        .nav-links a {
            color: #007bff;
            text-decoration: none;
            margin-right: 20px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .post-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .action-button {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .action-button:hover {
            background-color: #0056b3;
        }
        .action-button.secondary {
            background-color: #6c757d;
        }
        .action-button.secondary:hover {
            background-color: #545b62;
        }
        .comments-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #ddd;
        }
        .comment {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9em;
        }
        .comment-date {
            color: #999;
        }
        .comment-content {
            color: #333;
            line-height: 1.6;
        }
        .no-comments {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        .add-comment {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .add-comment h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            font-size: 14px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="/">ホーム</a>
            <a href="/posts.php">記事一覧</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </div>
        
        <a href="/posts.php" class="back-link">← 記事一覧に戻る</a>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['comment_success'])): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($_SESSION['comment_success']); ?>
            </div>
            <?php unset($_SESSION['comment_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['comment_errors'])): ?>
            <div class="message error">
                ❌ <?php echo htmlspecialchars(implode('<br>', $_SESSION['comment_errors'])); ?>
            </div>
            <?php unset($_SESSION['comment_errors']); ?>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'comment_deleted'): ?>
            <div class="message success">
                ✅ コメントを削除しました。
            </div>
        <?php endif; ?>
        
        <?php if ($post): ?>
            <div class="post-header">
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="post-meta">
                    <strong>👤 著者:</strong> <?php echo htmlspecialchars($post['author_name']); ?> |
                    <strong>📧 メール:</strong> <?php echo htmlspecialchars($post['author_email']); ?> |
                    <strong>📅 投稿日:</strong> <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?> |
                    <strong>🔄 更新日:</strong> <?php echo date('Y-m-d H:i', strtotime($post['updated_at'])); ?> |
                    <span class="status-badge status-<?php echo $post['status']; ?>">
                        <?php echo $post['status'] === 'published' ? '公開' : '下書き'; ?>
                    </span>
                </div>
                
                <?php if (!empty($post['categories'])): ?>
                    <div class="post-categories">
                        <strong>🏷️ カテゴリ:</strong>
                        <?php 
                        $categories = explode(', ', $post['categories']);
                        foreach ($categories as $category): 
                        ?>
                            <span class="category-tag"><?php echo htmlspecialchars(trim($category)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($post['image_path'])): ?>
                <div class="post-image" style="margin: 20px 0; text-align: center;">
                    <img src="/<?php echo htmlspecialchars($post['image_path']); ?>" 
                         alt="記事画像" 
                         style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                </div>
            <?php endif; ?>
            
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            
            <!-- コメントセクション -->
            <div class="comments-section">
                <h3>💬 コメント (<?php echo count($comments); ?>件)</h3>
                
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <div>
                                    <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                                    <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                                <?php 
                                // 削除権限チェック：コメント投稿者または記事投稿者のみ削除可能
                                $current_user_id = getCurrentUserId();
                                $can_delete = false;
                                
                                if (isLoggedIn()) {
                                    // コメント投稿者または記事投稿者の場合
                                    if ($comment['user_id'] == $current_user_id || $post['user_id'] == $current_user_id) {
                                        $can_delete = true;
                                    }
                                }
                                ?>
                                <?php if ($can_delete): ?>
                                    <form method="POST" action="/delete_comment.php" style="display: inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8em;" 
                                                onclick="return confirm('このコメントを削除しますか？')">
                                            🗑️ 削除
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">まだコメントがありません。</p>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <div class="add-comment">
                        <h4>💬 コメントを投稿</h4>
                        <form method="POST" action="/add_comment.php">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div class="form-group">
                                <label for="user_id">👤 名前:</label>
                                <input type="text" id="user_id" value="<?php echo htmlspecialchars(getCurrentUser()['name']); ?>" readonly style="background-color: #f8f9fa;">
                                <small>ログイン中のユーザーが自動的に設定されます</small>
                            </div>
                            <div class="form-group">
                                <label for="comment_content">📝 コメント:</label>
                                <textarea name="content" id="comment_content" rows="4" required placeholder="コメントを入力してください..."></textarea>
                            </div>
                            <button type="submit" class="btn">💬 コメントを投稿</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="add-comment" style="background-color: #fff3cd; border: 1px solid #ffeaa7;">
                        <h4>🔐 ログインが必要です</h4>
                        <p>コメントを投稿するにはログインしてください。</p>
                        <a href="/login.php" class="btn">🔐 ログイン</a>
                        <a href="/create_user.php" class="btn" style="background-color: #6c757d;">👤 新規登録</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="post-actions">
                <a href="/edit_post.php?id=<?php echo $post['id']; ?>" class="action-button">
                    ✏️ 編集
                </a>
                <a href="/posts.php" class="action-button secondary">
                    📝 記事一覧
                </a>
                <a href="/" class="action-button secondary">
                    🏠 ホーム
                </a>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3>📝 記事が見つかりません</h3>
                <p>指定された記事IDの記事が見つかりませんでした。</p>
                <a href="/posts.php" class="action-button">記事一覧に戻る</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - 記事詳細機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html> 