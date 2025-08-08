<?php
/**
 * ユーザー編集機能
 * 既存のユーザーを編集するフォーム
 */

// エラー表示を有効にする（開発環境用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// ユーザーIDを取得
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
$user = null;

// データベース接続
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ユーザーデータを取得
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error_message = "ユーザーが見つかりません。";
    }
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
}

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';
    
    // バリデーション
    if (empty($name)) {
        $error_message = '名前を入力してください。';
    } elseif (empty($email)) {
        $error_message = 'メールアドレスを入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = '有効なメールアドレスを入力してください。';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error_message = 'パスワードは6文字以上で入力してください。';
    } elseif (!empty($new_password) && $new_password !== $new_password_confirm) {
        $error_message = 'パスワードが一致しません。';
    } else {
        try {
            // メールアドレスの重複チェック（自分以外）
            $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->execute([$email, $user_id]);
            
            if ($check_stmt->fetch()) {
                $error_message = 'このメールアドレスは既に使用されています。';
            } else {
                // パスワードが入力されている場合は更新
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET name = ?, email = ?, password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$name, $email, $hashed_password, $user_id]);
                } else {
                    $query = "UPDATE users SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$name, $email, $user_id]);
                }
                
                $message = "ユーザー「{$name}」が正常に更新されました！";
                
                // 更新されたユーザーデータを再取得
                $query = "SELECT * FROM users WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
        } catch (PDOException $e) {
            $error_message = "データベースエラー: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user ? 'ユーザー編集: ' . htmlspecialchars($user['name']) : 'ユーザー編集'; ?> - Laravel アプリケーション</title>
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
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
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
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .password-section {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="/">ホーム</a>
            <a href="/posts.php">記事一覧</a>
            <a href="/users.php">ユーザー管理</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </div>
        
        <h1>✏️ ユーザー編集</h1>
        
        <?php if ($message): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($message); ?>
                <br><a href="/users.php">ユーザー一覧に戻る</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($user): ?>
            <div class="user-info">
                <strong>👤 編集対象:</strong> <?php echo htmlspecialchars($user['name']); ?><br>
                <strong>📅 登録日:</strong> <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?><br>
                <strong>🔄 最終更新:</strong> <?php echo date('Y-m-d H:i', strtotime($user['updated_at'])); ?>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">👤 名前 *</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? $user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">📧 メールアドレス *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
                </div>
                
                <div class="password-section">
                    <h4>🔒 パスワード変更（オプション）</h4>
                    <p>パスワードを変更する場合のみ入力してください。</p>
                    
                    <div class="form-group">
                        <label for="new_password">🔒 新しいパスワード</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password_confirm">🔒 新しいパスワード確認</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">💾 ユーザーを更新</button>
                    <a href="/users.php" class="btn btn-secondary">📋 ユーザー一覧に戻る</a>
                    <a href="/delete_user.php?id=<?php echo $user_id; ?>" class="btn btn-danger" 
                       onclick="return confirm('このユーザーを削除しますか？関連する投稿・コメントも削除されます。')">
                        🗑️ 削除
                    </a>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3>👤 ユーザーが見つかりません</h3>
                <p>指定されたユーザーIDのユーザーが見つかりませんでした。</p>
                <a href="/users.php" class="btn">ユーザー一覧に戻る</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - ユーザー編集機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
