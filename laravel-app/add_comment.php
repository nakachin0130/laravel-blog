<?php
/**
 * コメント投稿処理
 * 記事にコメントを追加する処理
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

// POSTリクエストの確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /posts.php');
    exit;
}

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

// フォームデータの取得
$post_id = (int)($_POST['post_id'] ?? 0);
$user_id = getCurrentUserId(); // ログインしているユーザーのIDを使用
$content = trim($_POST['content'] ?? '');

// バリデーション
$errors = [];

if ($post_id <= 0) {
    $errors[] = '記事IDが無効です。';
}

if ($user_id <= 0) {
    $errors[] = 'ログインが必要です。';
}

if (empty($content)) {
    $errors[] = 'コメントを入力してください。';
}

if (strlen($content) > 1000) {
    $errors[] = 'コメントは1000文字以内で入力してください。';
}

// エラーがある場合はリダイレクト
if (!empty($errors)) {
    $_SESSION['comment_errors'] = $errors;
    $_SESSION['comment_data'] = [
        'post_id' => $post_id,
        'user_id' => $user_id,
        'content' => $content
    ];
    header("Location: /post.php?id=$post_id");
    exit;
}

// データベース接続とコメント投稿
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 記事の存在確認
    $post_query = "SELECT id FROM posts WHERE id = ?";
    $post_stmt = $pdo->prepare($post_query);
    $post_stmt->execute([$post_id]);
    
    if (!$post_stmt->fetch()) {
        $errors[] = '指定された記事が見つかりません。';
        $_SESSION['comment_errors'] = $errors;
        header("Location: /post.php?id=$post_id");
        exit;
    }
    
    // ユーザーの存在確認
    $user_query = "SELECT id FROM users WHERE id = ?";
    $user_stmt = $pdo->prepare($user_query);
    $user_stmt->execute([$user_id]);
    
    if (!$user_stmt->fetch()) {
        $errors[] = '指定されたユーザーが見つかりません。';
        $_SESSION['comment_errors'] = $errors;
        header("Location: /post.php?id=$post_id");
        exit;
    }
    
    // コメントを挿入
    $comment_query = "INSERT INTO comments (post_id, user_id, content, status) VALUES (?, ?, ?, 'pending')";
    $comment_stmt = $pdo->prepare($comment_query);
    $comment_stmt->execute([$post_id, $user_id, $content]);
    
    // 成功メッセージを設定
    $_SESSION['comment_success'] = 'コメントが投稿されました。承認後に表示されます。';
    
} catch (PDOException $e) {
    $errors[] = "データベースエラー: " . $e->getMessage();
    $_SESSION['comment_errors'] = $errors;
}

// 記事詳細ページにリダイレクト
header("Location: /post.php?id=$post_id");
exit;
?> 