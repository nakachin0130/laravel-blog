<?php
/**
 * ダッシュボード
 * ログイン後のメインページ
 */

require_once 'auth.php';

// ログインが必要
requireLogin();

$user = getCurrentUser();
$error_message = '';
$user_stats = [];
$recent_posts = [];
$recent_comments = [];

       try {
           require 'database_config.php';
           $pdo = getDatabaseConnection();
    
    $user_id = getCurrentUserId();
    
    // ユーザー統計を取得
    $stats_query = "
        SELECT 
            COUNT(DISTINCT p.id) as total_posts,
            COUNT(DISTINCT c.id) as total_comments,
            COUNT(DISTINCT CASE WHEN p.status = 'published' THEN p.id END) as published_posts,
            COUNT(DISTINCT CASE WHEN c.status = 'approved' THEN c.id END) as approved_comments
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        LEFT JOIN comments c ON u.id = c.user_id
        WHERE u.id = ?
    ";
    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->execute([$user_id]);
    $user_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // 最近の投稿を取得
    $posts_query = "
        SELECT 
            p.id,
            p.title,
            p.status,
            p.created_at,
            COUNT(DISTINCT c.id) as comment_count
        FROM posts p
        LEFT JOIN comments c ON p.id = c.post_id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ";
    $posts_stmt = $pdo->prepare($posts_query);
    $posts_stmt->execute([$user_id]);
    $recent_posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 最近のコメントを取得
    $comments_query = "
        SELECT 
            c.id,
            c.content,
            c.status,
            c.created_at,
            p.title as post_title,
            p.id as post_id
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ";
    $comments_stmt = $pdo->prepare($comments_query);
    $comments_stmt->execute([$user_id]);
    $recent_comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "データベースエラー: " . $e->getMessage();
}
?>
<?php
$page_title = 'ダッシュボード - Laravel App';
include 'includes/header.php';
?>

<!-- ユーザー情報セクション -->
<section class="card floating-card">
    <div class="d-flex justify-between align-center">
        <div class="d-flex align-center">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--white); font-size: 1.5rem; font-weight: bold; margin-right: var(--spacing-lg);">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div>
                <h2 style="margin-bottom: var(--spacing-xs);">👋 ようこそ、<?php echo htmlspecialchars($user['name']); ?>さん</h2>
                <p style="color: var(--gray-600); margin-bottom: var(--spacing-xs);">📧 <?php echo htmlspecialchars($user['email']); ?></p>
                <p style="color: var(--gray-600); font-size: var(--font-size-sm);">📅 登録日: <?php echo date('Y-m-d', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
        <div class="d-flex" style="gap: var(--spacing-sm);">
            <a href="/create_post.php" class="btn btn-primary">📝 新規投稿</a>
            <a href="/posts.php" class="btn btn-secondary">📋 記事一覧</a>
            <a href="/logout.php" class="btn btn-danger">🚪 ログアウト</a>
        </div>
    </div>
</section>

<?php if ($error_message): ?>
    <div class="alert alert-danger">
        ❌ <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- 統計セクション -->
<section class="grid grid-3" style="gap: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
    <div class="card text-center">
        <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: var(--spacing-sm);">📝</div>
        <h3>投稿数</h3>
        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin-bottom: var(--spacing-xs);">
            <?php echo $user_stats['total_posts'] ?? 0; ?>
        </div>
        <p style="color: var(--gray-600);">公開済み: <?php echo $user_stats['published_posts'] ?? 0; ?>件</p>
    </div>
    
    <div class="card text-center">
        <div style="font-size: 3rem; color: var(--success-color); margin-bottom: var(--spacing-sm);">💬</div>
        <h3>コメント数</h3>
        <div style="font-size: 2rem; font-weight: bold; color: var(--success-color); margin-bottom: var(--spacing-xs);">
            <?php echo $user_stats['total_comments'] ?? 0; ?>
        </div>
        <p style="color: var(--gray-600);">承認済み: <?php echo $user_stats['approved_comments'] ?? 0; ?>件</p>
    </div>
    
    <div class="card text-center">
        <div style="font-size: 3rem; color: var(--info-color); margin-bottom: var(--spacing-sm);">📊</div>
        <h3>総アクティビティ</h3>
        <div style="font-size: 2rem; font-weight: bold; color: var(--info-color); margin-bottom: var(--spacing-xs);">
            <?php echo ($user_stats['total_posts'] ?? 0) + ($user_stats['total_comments'] ?? 0); ?>
        </div>
        <p style="color: var(--gray-600);">投稿・コメント合計</p>
    </div>
</section>

<!-- クイックアクション -->
<section class="card">
    <div class="card-header">
        <h3 class="card-title">⚡ クイックアクション</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-4">
            <div class="text-center">
                <a href="/create_post.php" class="d-block" style="text-decoration: none; color: inherit;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📝</div>
                    <h5>新規投稿</h5>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">新しい記事を作成</p>
                </a>
            </div>
            <div class="text-center">
                <a href="/posts.php" class="d-block" style="text-decoration: none; color: inherit;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📋</div>
                    <h5>記事管理</h5>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">投稿した記事を編集</p>
                </a>
            </div>
            <div class="text-center">
                <a href="/users.php" class="d-block" style="text-decoration: none; color: inherit;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">👥</div>
                    <h5>ユーザー管理</h5>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">ユーザー一覧を表示</p>
                </a>
            </div>
            <div class="text-center">
                <a href="/comment_approval.php" class="d-block" style="text-decoration: none; color: inherit;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">💬</div>
                    <h5>コメント承認</h5>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">コメントを承認・拒否</p>
                </a>
            </div>
            <div class="text-center">
                <a href="/" class="d-block" style="text-decoration: none; color: inherit;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🏠</div>
                    <h5>ホーム</h5>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">メインページに戻る</p>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- コンテンツグリッド -->
<section class="grid grid-2" style="gap: var(--spacing-xl);">
    <!-- 最近の投稿 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📝 最近の投稿</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_posts)): ?>
                <?php foreach ($recent_posts as $post): ?>
                    <div style="padding: var(--spacing-md) 0; border-bottom: 1px solid var(--gray-200);">
                        <div style="margin-bottom: var(--spacing-xs);">
                            <a href="/post.php?id=<?php echo $post['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </div>
                        <div style="color: var(--gray-600); font-size: var(--font-size-sm);">
                            📅 <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?>
                            💬 <?php echo $post['comment_count']; ?>件のコメント
                            <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                <?php echo $post['status']; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--gray-600); text-align: center; padding: var(--spacing-xl);">まだ投稿がありません。</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 最近のコメント -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💬 最近のコメント</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_comments)): ?>
                <?php foreach ($recent_comments as $comment): ?>
                    <div style="padding: var(--spacing-md) 0; border-bottom: 1px solid var(--gray-200);">
                        <div style="margin-bottom: var(--spacing-xs);">
                            <a href="/post.php?id=<?php echo $comment['post_id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                                <?php echo htmlspecialchars($comment['post_title']); ?>
                            </a>
                        </div>
                        <div style="color: var(--gray-600); font-size: var(--font-size-sm); margin-bottom: var(--spacing-xs);">
                            📅 <?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?>
                            <span class="badge badge-<?php echo $comment['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                <?php echo $comment['status']; ?>
                            </span>
                        </div>
                        <div style="color: var(--gray-700); font-size: var(--font-size-sm);">
                            <?php echo htmlspecialchars(substr($comment['content'], 0, 100)); ?>
                            <?php if (strlen($comment['content']) > 100): ?>...<?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--gray-600); text-align: center; padding: var(--spacing-xl);">まだコメントがありません。</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
