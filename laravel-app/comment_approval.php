<?php
/**
 * コメント承認管理機能
 * 管理者がコメントを承認・拒否する
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

$message = '';
$error_message = '';

// 承認・拒否処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $action = $_POST['action'] ?? '';
    
    if ($comment_id > 0 && in_array($action, ['approve', 'reject'])) {
        try {
            require 'database_config.php';
            $pdo = getDatabaseConnection();
            
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $query = "UPDATE comments SET status = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$status, $comment_id]);
            
            $message = ($action === 'approve') ? 'コメントを承認しました。' : 'コメントを拒否しました。';
            
        } catch (Exception $e) {
            $error_message = "データベースエラー: " . $e->getMessage();
        }
    }
}

// コメント一覧を取得
try {
    require 'database_config.php';
    $pdo = getDatabaseConnection();
    
    $query = "
        SELECT 
            c.id,
            c.content,
            c.status,
            c.created_at,
            u.name as author_name,
            p.title as post_title,
            p.id as post_id
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN posts p ON c.post_id = p.id
        ORDER BY c.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
    $comments = [];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コメント承認管理 - Laravel アプリケーション</title>
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
        .comment-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .comment-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .comment-content {
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
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
        .stats {
            background-color: #e9ecef;
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
            <a href="/dashboard.php">ダッシュボード</a>
            <a href="/comment_approval.php">コメント承認</a>
        </div>
        
        <h1>📝 コメント承認管理</h1>
        
        <?php if ($message): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($comments)): ?>
            <div class="stats">
                <strong>📊 統計情報:</strong>
                <ul>
                    <li>総コメント数: <?php echo count($comments); ?>件</li>
                    <li>承認待ち: <?php echo count(array_filter($comments, function($c) { return $c['status'] === 'pending'; })); ?>件</li>
                    <li>承認済み: <?php echo count(array_filter($comments, function($c) { return $c['status'] === 'approved'; })); ?>件</li>
                    <li>拒否済み: <?php echo count(array_filter($comments, function($c) { return $c['status'] === 'rejected'; })); ?>件</li>
                </ul>
            </div>
            
            <?php foreach ($comments as $comment): ?>
                <div class="comment-card">
                    <div class="comment-meta">
                        <strong>👤 投稿者:</strong> <?php echo htmlspecialchars($comment['author_name']); ?> |
                        <strong>📅 投稿日:</strong> <?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?> |
                        <strong>📝 記事:</strong> <a href="/post.php?id=<?php echo $comment['post_id']; ?>"><?php echo htmlspecialchars($comment['post_title']); ?></a> |
                        <span class="status-badge status-<?php echo $comment['status']; ?>">
                            <?php 
                            switch($comment['status']) {
                                case 'pending': echo '承認待ち'; break;
                                case 'approved': echo '承認済み'; break;
                                case 'rejected': echo '拒否済み'; break;
                                default: echo '不明'; break;
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                    </div>
                    
                    <?php if ($comment['status'] === 'pending'): ?>
                        <div style="margin-top: 15px;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-approve" onclick="return confirm('このコメントを承認しますか？')">
                                    ✅ 承認
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-reject" onclick="return confirm('このコメントを拒否しますか？')">
                                    ❌ 拒否
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <h3>📝 コメントがありません</h3>
                <p>まだコメントが投稿されていません。</p>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - コメント承認管理機能</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
