<?php
/**
 * ユーザー管理機能
 * ユーザー一覧の表示と管理
 */

// エラー表示を有効にする（開発環境用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

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

// データベース接続
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ユーザー一覧を取得（投稿数とコメント数も含む）
    $query = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.created_at,
            u.updated_at,
            COUNT(DISTINCT p.id) as post_count,
            COUNT(DISTINCT c.id) as comment_count
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        LEFT JOIN comments c ON u.id = c.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー管理 - Laravel アプリケーション</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        .user-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .user-name {
            font-size: 1.3em;
            color: #007bff;
            font-weight: bold;
        }
        .user-email {
            color: #666;
            font-size: 0.9em;
        }
        .user-stats {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }
        .stat-item {
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .user-actions {
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            font-size: 14px;
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
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
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
        .stats {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .user-date {
            color: #666;
            font-size: 0.8em;
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
        
        <h1>👥 ユーザー管理</h1>
        
        <div style="margin-bottom: 20px;">
            <a href="/create_user.php" class="btn btn-success">
                👤 新しいユーザーを追加
            </a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($users)): ?>
            <div class="stats">
                <strong>📊 統計情報:</strong>
                <ul>
                    <li>総ユーザー数: <?php echo count($users); ?>人</li>
                    <li>投稿者数: <?php echo count(array_filter($users, function($user) { return $user['post_count'] > 0; })); ?>人</li>
                    <li>コメント投稿者数: <?php echo count(array_filter($users, function($user) { return $user['comment_count'] > 0; })); ?>人</li>
                </ul>
            </div>
            
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-header">
                        <div>
                            <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="user-email">📧 <?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="user-date">
                            📅 登録日: <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            📝 投稿数: <?php echo $user['post_count']; ?>件
                        </div>
                        <div class="stat-item">
                            💬 コメント数: <?php echo $user['comment_count']; ?>件
                        </div>
                    </div>
                    
                    <div class="user-actions">
                        <a href="/edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">
                            ✏️ 編集
                        </a>
                        <a href="/user_posts.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">
                            📝 投稿一覧
                        </a>
                        <a href="/user_comments.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">
                            💬 コメント一覧
                        </a>
                        <a href="/delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('このユーザーを削除しますか？関連する投稿・コメントも削除されます。')">
                            🗑️ 削除
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3>👥 ユーザーがありません</h3>
                <p>まだユーザーが登録されていません。</p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - ユーザー管理機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
