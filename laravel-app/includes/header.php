<?php
/**
 * 共通ヘッダーコンポーネント
 * ナビゲーションとユーザーメニューを含む
 */

// 認証機能を読み込み
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Laravel App'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body>
    <!-- ナビゲーションバー -->
    <nav class="navbar">
        <div class="container d-flex justify-between align-center">
            <div class="navbar-brand">
                <a href="/" class="d-flex align-center">
                    <span style="font-size: 1.5rem; margin-right: 0.5rem;">🚀</span>
                    <span>Laravel App</span>
                </a>
            </div>
            
            <ul class="navbar-nav d-flex align-center">
                <li class="nav-item">
                    <a href="/" class="nav-link">🏠 ホーム</a>
                </li>
                <li class="nav-item">
                    <a href="/posts.php" class="nav-link">📋 記事一覧</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a href="/create_post.php" class="nav-link">📝 新規投稿</a>
                    </li>
                    <li class="nav-item">
                        <a href="/dashboard.php" class="nav-link">📊 ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a href="/users.php" class="nav-link">👥 ユーザー管理</a>
                    </li>
                    <li class="nav-item">
                        <a href="/logout.php" class="nav-link text-danger">🚪 ログアウト</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/login.php" class="nav-link">🔐 ログイン</a>
                    </li>
                    <li class="nav-item">
                        <a href="/create_user.php" class="nav-link">👤 新規登録</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- メインコンテンツ -->
    <main class="container fade-in">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                ❌ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
