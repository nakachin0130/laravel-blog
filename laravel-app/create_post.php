<?php
/**
 * 記事投稿機能
 * 新しい記事を作成するフォーム
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

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 画像パスを取得（ファイルアップロードから）
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $file_name = $file['name'];
                $file_size = $file['size'];
                $file_tmp = $file['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // 許可する画像形式
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_ext, $allowed_extensions) && $file_size <= 5 * 1024 * 1024) {
                    // アップロードディレクトリの確認と作成
                    $upload_dir = 'assets/uploads/images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // ユニークなファイル名を生成
                    $timestamp = time();
                    $random_string = bin2hex(random_bytes(8));
                    $new_file_name = "post_{$user_id}_{$timestamp}_{$random_string}.{$file_ext}";
                    $upload_path = $upload_dir . $new_file_name;
                    
                    // ファイルをアップロード
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $image_path = $upload_path;
                    }
                }
            }
            
            // 記事を挿入
            $query = "INSERT INTO posts (title, content, image_path, user_id, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$title, $content, $image_path, $user_id, $status]);
            
            $post_id = $pdo->lastInsertId();
            
            // カテゴリを関連付け
            if (!empty($category_ids)) {
                $category_query = "INSERT INTO post_category (post_id, category_id) VALUES (?, ?)";
                $category_stmt = $pdo->prepare($category_query);
                
                foreach ($category_ids as $category_id) {
                    $category_stmt->execute([$post_id, $category_id]);
                }
            }
            
            $message = "記事が正常に投稿されました！";
            
        } catch (PDOException $e) {
            $error_message = "データベースエラー: " . $e->getMessage();
        }
    }
}

// ユーザー一覧を取得
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
    $users = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>記事投稿 - Laravel アプリケーション</title>
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
        
        <h1>📝 記事投稿</h1>
        
        <?php if ($message): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($message); ?>
                <br><a href="/posts.php">記事一覧を見る</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">📝 タイトル *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content">📄 内容 *</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">🖼️ 画像アップロード</label>
                <input type="file" id="image" name="image" accept="image/*" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666; display: block; margin-top: 5px;">
                    📋 対応形式: JPG, PNG, GIF, WebP (最大5MB)
                </small>
            </div>
            
            <div id="image-preview" style="display: none; margin-top: 10px;">
                <img id="preview-img" src="" alt="プレビュー" style="max-width: 300px; max-height: 200px; border-radius: 4px; border: 1px solid #ddd;">
                <button type="button" id="remove-image" style="margin-left: 10px; padding: 5px 10px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">🗑️ 削除</button>
            </div>
            
            <div class="form-group">
                <label for="user_id">👤 著者</label>
                <input type="text" id="user_id" value="<?php echo htmlspecialchars(getCurrentUser()['name']); ?>" readonly style="background-color: #f8f9fa;">
                <small>ログイン中のユーザーが自動的に設定されます</small>
            </div>
            
            <div class="form-group">
                <label for="status">📊 ステータス</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>下書き</option>
                    <option value="published" <?php echo ($_POST['status'] ?? 'draft') === 'published' ? 'selected' : ''; ?>>公開</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>🏷️ カテゴリ</label>
                <div class="checkbox-group">
                    <?php foreach ($categories as $category): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" id="category_<?php echo $category['id']; ?>" 
                                   name="categories[]" value="<?php echo $category['id']; ?>"
                                   <?php echo in_array($category['id'], $_POST['categories'] ?? []) ? 'checked' : ''; ?>>
                            <label for="category_<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">📝 記事を投稿</button>
                <a href="/posts.php" class="btn btn-secondary">📋 記事一覧に戻る</a>
                <a href="/" class="btn btn-secondary">🏠 ホーム</a>
            </div>
        </form>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - 記事投稿機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <script>
        // 画像プレビュー機能
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        // 画像削除機能
        document.getElementById('remove-image').addEventListener('click', function() {
            document.getElementById('image').value = '';
            document.getElementById('image-preview').style.display = 'none';
        });
    </script>
</body>
</html> 