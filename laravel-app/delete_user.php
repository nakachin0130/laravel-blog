<?php
/**
 * ユーザー削除機能
 * ユーザーを削除する処理
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

// 削除確認が送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && isset($_POST['confirm_delete'])) {
    try {
        // トランザクション開始
        $pdo->beginTransaction();
        
        // 関連するコメントを削除
        $delete_comments_query = "DELETE FROM comments WHERE user_id = ?";
        $delete_comments_stmt = $pdo->prepare($delete_comments_query);
        $delete_comments_stmt->execute([$user_id]);
        
        // 関連する記事を削除（post_categoryも自動的に削除される）
        $delete_posts_query = "DELETE FROM posts WHERE user_id = ?";
        $delete_posts_stmt = $pdo->prepare($delete_posts_query);
        $delete_posts_stmt->execute([$user_id]);
        
        // ユーザーを削除
        $delete_user_query = "DELETE FROM users WHERE id = ?";
        $delete_user_stmt = $pdo->prepare($delete_user_query);
        $delete_user_stmt->execute([$user_id]);
        
        // トランザクションコミット
        $pdo->commit();
        
        $message = "ユーザー「" . htmlspecialchars($user['name']) . "」が正常に削除されました。";
        $user = null; // 削除されたのでnullに設定
        
    } catch (PDOException $e) {
        // エラーが発生した場合はロールバック
        $pdo->rollBack();
        $error_message = "データベースエラー: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user ? 'ユーザー削除: ' . htmlspecialchars($user['name']) : 'ユーザー削除'; ?> - Laravel アプリケーション</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            margin-bottom: 30px;
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
        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
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
        .user-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .confirm-form {
            background-color: #fff3cd;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        .actions {
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
            <a href="/users.php">ユーザー管理</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </div>
        
        <h1>🗑️ ユーザー削除</h1>
        
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
                <h3>⚠️ 削除対象のユーザー</h3>
                <p><strong>👤 名前:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>📧 メールアドレス:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>📅 登録日:</strong> <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></p>
            </div>
            
            <div class="confirm-form">
                <h3>⚠️ 削除の確認</h3>
                <p>このユーザーを削除しますか？この操作は取り消せません。</p>
                <p><strong>削除される内容:</strong></p>
                <ul>
                    <li>ユーザーアカウント</li>
                    <li>関連するすべての記事</li>
                    <li>関連するすべてのコメント</li>
                    <li>記事とカテゴリの関連</li>
                </ul>
                
                <form method="POST" action="">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-danger">🗑️ 削除を実行</button>
                    <a href="/edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-secondary">✏️ 編集に戻る</a>
                    <a href="/users.php" class="btn btn-secondary">📋 ユーザー一覧に戻る</a>
                </form>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3>👤 ユーザーが見つかりません</h3>
                <p>指定されたユーザーIDのユーザーが見つかりませんでした。</p>
                <a href="/users.php" class="btn">ユーザー一覧に戻る</a>
            </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="/users.php" class="btn btn-secondary">📋 ユーザー一覧</a>
            <a href="/create_user.php" class="btn">👤 新しいユーザーを作成</a>
            <a href="/" class="btn btn-secondary">🏠 ホーム</a>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - ユーザー削除機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
