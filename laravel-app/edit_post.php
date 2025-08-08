<?php
/**
 * 記事編集機能
 * 既存の記事を編集するフォーム
 */

// エラー表示を有効にする（開発環境用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// 認証機能を読み込み
require_once 'auth.php';

// ログインが必要
requireLogin();

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

$message = '';
$error_message = '';
$post = null;

// データベース接続
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 記事データを取得（自分の投稿のみ）
    $query = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id, getCurrentUserId()]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        $error_message = "記事が見つからないか、編集権限がありません。";
    }
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
}

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $post) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = getCurrentUserId(); // ログインしているユーザーのIDを使用
    $status = $_POST['status'] ?? 'draft';
    $category_ids = $_POST['categories'] ?? [];
    
    // バリデーション
    if (empty($title)) {
        $error_message = 'タイトルを入力してください。';
    } elseif (empty($content)) {
        $error_message = '内容を入力してください。';
    } else {
        try {
            // 記事を更新
            $query = "UPDATE posts SET title = ?, content = ?, user_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$title, $content, $user_id, $status, $post_id]);
            
            // 既存のカテゴリ関連を削除
            $delete_query = "DELETE FROM post_category WHERE post_id = ?";
            $delete_stmt = $pdo->prepare($delete_query);
            $delete_stmt->execute([$post_id]);
            
            // 新しいカテゴリを関連付け
            if (!empty($category_ids)) {
                $category_query = "INSERT INTO post_category (post_id, category_id) VALUES (?, ?)";
                $category_stmt = $pdo->prepare($category_query);
                
                foreach ($category_ids as $category_id) {
                    $category_stmt->execute([$post_id, $category_id]);
                }
            }
            
            $message = "記事が正常に更新されました！";
            
            // 更新された記事データを再取得
            $query = "SELECT * FROM posts WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$post_id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error_message = "データベースエラー: " . $e->getMessage();
        }
    }
}

// ユーザー一覧とカテゴリ一覧を取得
try {
    $users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
    // 現在の記事のカテゴリを取得
    $current_categories = [];
    if ($post) {
        $category_query = "SELECT category_id FROM post_category WHERE post_id = ?";
        $category_stmt = $pdo->prepare($category_query);
        $category_stmt->execute([$post_id]);
        $current_categories = $category_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
    $users = [];
    $categories = [];
    $current_categories = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? '記事編集: ' . htmlspecialchars($post['title']) : '記事編集'; ?> - Laravel アプリケーション</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            height: 200px;
            resize: vertical;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .checkbox-item input[type="checkbox"] {
            width: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
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
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .post-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="/">ホーム</a>
            <a href="/posts.php">記事一覧</a>
            <a href="/create_post.php">記事投稿</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </div>
        
        <h1>✏️ 記事編集</h1>
        
        <?php if ($message): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($message); ?>
                <br><a href="/post.php?id=<?php echo $post_id; ?>">記事を確認</a> | 
                <a href="/posts.php">記事一覧に戻る</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($post): ?>
            <div class="post-info">
                <strong>📝 編集対象:</strong> <?php echo htmlspecialchars($post['title']); ?><br>
                <strong>📅 作成日:</strong> <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?><br>
                <strong>🔄 最終更新:</strong> <?php echo date('Y-m-d H:i', strtotime($post['updated_at'])); ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">📝 タイトル *</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo htmlspecialchars($_POST['title'] ?? $post['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">📄 内容 *</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($_POST['content'] ?? $post['content']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="user_id">👤 著者</label>
                    <select id="user_id" name="user_id">
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo ($_POST['user_id'] ?? $post['user_id']) == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">📊 ステータス</label>
                    <select id="status" name="status">
                        <option value="draft" <?php echo ($_POST['status'] ?? $post['status']) === 'draft' ? 'selected' : ''; ?>>下書き</option>
                        <option value="published" <?php echo ($_POST['status'] ?? $post['status']) === 'published' ? 'selected' : ''; ?>>公開</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>🏷️ カテゴリ</label>
                    <div class="checkbox-group">
                        <?php 
                        $selected_categories = $_POST['categories'] ?? $current_categories;
                        foreach ($categories as $category): 
                        ?>
                            <div class="checkbox-item">
                                <input type="checkbox" id="category_<?php echo $category['id']; ?>" 
                                       name="categories[]" value="<?php echo $category['id']; ?>"
                                       <?php echo in_array($category['id'], $selected_categories) ? 'checked' : ''; ?>>
                                <label for="category_<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">💾 記事を更新</button>
                    <a href="/post.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">👁️ 記事を確認</a>
                    <a href="/posts.php" class="btn btn-secondary">📋 記事一覧に戻る</a>
                    <a href="/delete_post.php?id=<?php echo $post_id; ?>" class="btn btn-danger" 
                       onclick="return confirm('この記事を削除しますか？この操作は取り消せません。')">🗑️ 削除</a>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3>📝 記事が見つかりません</h3>
                <p>指定された記事IDの記事が見つかりませんでした。</p>
                <a href="/posts.php" class="btn">記事一覧に戻る</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - 記事編集機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html> 