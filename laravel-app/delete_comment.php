<?php
/**
 * コメント削除機能
 * 投稿者のみが削除可能
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

$error_message = '';
$success_message = '';

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    
    if ($comment_id <= 0 || $post_id <= 0) {
        $error_message = '無効なリクエストです。';
    } else {
        try {
            require 'database_config.php';
            $pdo = getDatabaseConnection();
            
            // コメントの存在確認と権限チェック
            $check_query = "
                SELECT 
                    c.id,
                    c.user_id,
                    c.post_id,
                    p.user_id as post_user_id
                FROM comments c
                JOIN posts p ON c.post_id = p.id
                WHERE c.id = ? AND c.post_id = ?
            ";
            
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->execute([$comment_id, $post_id]);
            $comment = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$comment) {
                $error_message = 'コメントが見つかりません。';
            } else {
                $current_user_id = getCurrentUserId();
                
                // 権限チェック：コメント投稿者または記事投稿者のみ削除可能
                if ($comment['user_id'] == $current_user_id || $comment['post_user_id'] == $current_user_id) {
                    // コメントを削除
                    $delete_query = "DELETE FROM comments WHERE id = ?";
                    $delete_stmt = $pdo->prepare($delete_query);
                    $delete_stmt->execute([$comment_id]);
                    
                    $success_message = 'コメントを削除しました。';
                    
                    // 記事詳細ページにリダイレクト
                    header("Location: /post.php?id={$post_id}&success=comment_deleted");
                    exit;
                } else {
                    $error_message = 'このコメントを削除する権限がありません。';
                }
            }
            
        } catch (Exception $e) {
            $error_message = "データベースエラー: " . $e->getMessage();
        }
    }
}

// GETリクエストの場合は記事詳細ページにリダイレクト
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
    if ($post_id > 0) {
        header("Location: /post.php?id={$post_id}");
    } else {
        header("Location: /posts.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>コメント削除 - Laravel アプリケーション</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>コメント削除</h1>
            <nav>
                <a href="/" class="btn btn-outline">🏠 ホーム</a>
                <a href="/posts.php" class="btn btn-outline">📋 記事一覧</a>
                <a href="/dashboard.php" class="btn btn-outline">📊 ダッシュボード</a>
            </nav>
        </header>

        <main>
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    ❌ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="/posts.php" class="btn btn-primary">📋 記事一覧に戻る</a>
            </div>
        </main>
    </div>
</body>
</html>
