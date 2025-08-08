<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 * 
 * 基本的なLaravelアプリケーション構造
 */

// エラー表示を有効にする（開発環境用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// 認証機能を読み込み
require_once 'auth.php';

// ページタイトルを設定
$page_title = 'ホーム - Laravel App';

// ヘッダーを読み込み
include 'includes/header.php';
?>

<!-- ヒーローセクション -->
<section class="card gradient-bg text-center" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: var(--white); margin-bottom: var(--spacing-2xl);">
    <div style="padding: var(--spacing-2xl) 0;">
        <h1 style="font-size: var(--font-size-4xl); margin-bottom: var(--spacing-lg); color: var(--white);">
            🚀 Laravel アプリケーション
        </h1>
        <p style="font-size: var(--font-size-lg); opacity: 0.9; margin-bottom: var(--spacing-xl);">
            モダンなWeb開発のためのプラットフォーム
        </p>
        <div class="d-flex justify-center" style="gap: var(--spacing-md);">
            <a href="/posts.php" class="btn btn-primary btn-lg">📋 記事一覧を見る</a>
            <?php if (!isLoggedIn()): ?>
                <a href="/login.php" class="btn btn-outline btn-lg" style="border-color: var(--white); color: var(--white);">🔐 ログイン</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- メインコンテンツ -->
<div class="grid grid-2" style="gap: var(--spacing-xl);">
    <!-- 認証セクション -->
    <div class="card floating-card">
        <div class="card-header">
            <h3 class="card-title">🔐 認証機能</h3>
        </div>
        <div class="card-body">
            <?php if (isLoggedIn()): ?>
                <?php $current_user = getCurrentUser(); ?>
                <div class="d-flex align-center" style="margin-bottom: var(--spacing-lg);">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--white); font-weight: bold; margin-right: var(--spacing-md);">
                        <?php echo strtoupper(substr($current_user['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4 style="margin-bottom: var(--spacing-xs);">ようこそ、<?php echo htmlspecialchars($current_user['name']); ?>さん</h4>
                        <p style="color: var(--gray-600); font-size: var(--font-size-sm);">📧 <?php echo htmlspecialchars($current_user['email']); ?></p>
                    </div>
                </div>
                <div class="d-flex" style="gap: var(--spacing-sm);">
                    <a href="/dashboard.php" class="btn btn-primary">📊 ダッシュボード</a>
                    <a href="/create_post.php" class="btn btn-secondary">📝 新規投稿</a>
                    <a href="/logout.php" class="btn btn-danger">🚪 ログアウト</a>
                </div>
            <?php else: ?>
                <p style="color: var(--gray-600); margin-bottom: var(--spacing-lg);">
                    アカウントにログインして機能を利用してください。
                </p>
                <div class="d-flex" style="gap: var(--spacing-sm);">
                    <a href="/login.php" class="btn btn-primary">🔐 ログイン</a>
                    <a href="/create_user.php" class="btn btn-outline">👤 新規登録</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 機能セクション -->
    <div class="card floating-card">
        <div class="card-header">
            <h3 class="card-title">⚡ 主要機能</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-1" style="gap: var(--spacing-md);">
                <a href="/posts.php" style="text-decoration: none; color: inherit; display: block;">
                    <div class="d-flex align-center" style="padding: var(--spacing-md); background: var(--gray-100); border-radius: var(--border-radius); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='var(--gray-200)'" onmouseout="this.style.background='var(--gray-100)'">
                        <span style="font-size: 1.5rem; margin-right: var(--spacing-md);">📋</span>
                        <div>
                            <h5 style="margin-bottom: var(--spacing-xs);">記事管理</h5>
                            <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin: 0;">記事の作成・編集・削除</p>
                        </div>
                    </div>
                </a>
                <a href="/post.php?id=1" style="text-decoration: none; color: inherit; display: block;">
                    <div class="d-flex align-center" style="padding: var(--spacing-md); background: var(--gray-100); border-radius: var(--border-radius); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='var(--gray-200)'" onmouseout="this.style.background='var(--gray-100)'">
                        <span style="font-size: 1.5rem; margin-right: var(--spacing-md);">💬</span>
                        <div>
                            <h5 style="margin-bottom: var(--spacing-xs);">コメント機能</h5>
                            <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin: 0;">記事へのコメント投稿</p>
                        </div>
                    </div>
                </a>
                <a href="/users.php" style="text-decoration: none; color: inherit; display: block;">
                    <div class="d-flex align-center" style="padding: var(--spacing-md); background: var(--gray-100); border-radius: var(--border-radius); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='var(--gray-200)'" onmouseout="this.style.background='var(--gray-100)'">
                        <span style="font-size: 1.5rem; margin-right: var(--spacing-md);">👥</span>
                        <div>
                            <h5 style="margin-bottom: var(--spacing-xs);">ユーザー管理</h5>
                            <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin: 0;">ユーザーの登録・管理</p>
                        </div>
                    </div>
                </a>
                <a href="/posts.php" style="text-decoration: none; color: inherit; display: block;">
                    <div class="d-flex align-center" style="padding: var(--spacing-md); background: var(--gray-100); border-radius: var(--border-radius); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='var(--gray-200)'" onmouseout="this.style.background='var(--gray-100)'">
                        <span style="font-size: 1.5rem; margin-right: var(--spacing-md);">🔍</span>
                        <div>
                            <h5 style="margin-bottom: var(--spacing-xs);">記事検索</h5>
                            <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin: 0;">キーワードで記事を検索</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 統計セクション -->
<section class="card mt-5">
    <div class="card-header">
        <h3 class="card-title">📊 アプリケーション統計</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-4">
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin-bottom: var(--spacing-xs);">
                    📝
                </div>
                <h4>記事数</h4>
                <p style="color: var(--gray-600);">管理された記事の総数</p>
            </div>
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: bold; color: var(--success-color); margin-bottom: var(--spacing-xs);">
                    👥
                </div>
                <h4>ユーザー数</h4>
                <p style="color: var(--gray-600);">登録済みユーザー数</p>
            </div>
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: bold; color: var(--info-color); margin-bottom: var(--spacing-xs);">
                    💬
                </div>
                <h4>コメント数</h4>
                <p style="color: var(--gray-600);">投稿されたコメント数</p>
            </div>
            <div class="text-center">
                <div style="font-size: 2rem; font-weight: bold; color: var(--warning-color); margin-bottom: var(--spacing-xs);">
                    🏷️
                </div>
                <h4>カテゴリ数</h4>
                <p style="color: var(--gray-600);">記事カテゴリ数</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 