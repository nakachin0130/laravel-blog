<?php
/**
 * 記事一覧表示機能（検索機能付き）
 * データベースから記事を取得して表示
 */

// エラー表示を有効にする（開発環境用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// セッション開始
session_start();

// 認証機能を読み込み
require_once 'auth.php';

// クラウド/環境変数対応
require_once 'database_config.php';
// 共通DB接続
$pdo = getDatabaseConnection();

// 検索キーワードを取得
$search_keyword = trim($_GET['search'] ?? '');
$posts = [];
$error_message = '';

// データベース接続
try {
    // 検索クエリを構築
    if (!empty($search_keyword)) {
        // 検索キーワードを正規化（大文字小文字を統一）
        $normalized_keyword = strtolower(trim($search_keyword));
        
        // 検索機能付きクエリ（大文字小文字を区別しない）
        $query = "
            SELECT 
                p.id,
                p.title,
                p.content,
                p.image_path,
                p.status,
                p.created_at,
                u.name as author_name,
                GROUP_CONCAT(c.name SEPARATOR ', ') as categories
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN post_category pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE (
                LOWER(p.title) LIKE ? OR 
                LOWER(p.content) LIKE ? OR
                LOWER(u.name) LIKE ? OR
                LOWER(c.name) LIKE ?
            )
            GROUP BY p.id
            ORDER BY 
                CASE 
                    WHEN LOWER(p.title) LIKE ? THEN 1
                    WHEN LOWER(p.content) LIKE ? THEN 2
                    ELSE 3
                END,
                p.created_at DESC
        ";
        $search_param = "%{$normalized_keyword}%";
        $exact_title_param = "{$normalized_keyword}%";
        $exact_content_param = "{$normalized_keyword}%";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $search_param, $search_param, $search_param, $search_param,
            $exact_title_param, $exact_content_param
        ]);
    } else {
        // 通常の記事一覧クエリ
        $query = "
            SELECT 
                p.id,
                p.title,
                p.content,
                p.image_path,
                p.status,
                p.created_at,
                u.name as author_name,
                GROUP_CONCAT(c.name SEPARATOR ', ') as categories
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN post_category pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
    }
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "データベース接続エラー: " . $e->getMessage();
    $posts = [];
}

// ハイライト機能（大文字小文字を区別しない）
function highlightKeyword($text, $keyword) {
    if (empty($keyword)) {
        return htmlspecialchars($text);
    }
    
    // キーワードを正規化
    $normalized_keyword = strtolower(trim($keyword));
    
    // 大文字小文字を区別しないでハイライト
    $highlighted = preg_replace(
        '/(' . preg_quote($normalized_keyword, '/') . ')/i',
        '<mark style="background-color: #ffeb3b; padding: 2px 4px; border-radius: 3px;">$1</mark>',
        htmlspecialchars($text)
    );
    
    return $highlighted;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($search_keyword) ? "検索結果: {$search_keyword}" : '記事一覧'; ?> - Laravel アプリケーション</title>
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
        .search-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        .search-input {
            width: 70%;
            padding: 12px;
            border: 2px solid #007bff;
            border-radius: 6px;
            font-size: 16px;
            margin-right: 10px;
        }
        .search-button {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        .search-button:hover {
            background-color: #0056b3;
        }
        .clear-search {
            padding: 12px 16px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            margin-left: 10px;
        }
        .clear-search:hover {
            background-color: #545b62;
            color: white;
            text-decoration: none;
        }
        .search-results {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .post-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .post-title {
            font-size: 1.5em;
            color: #007bff;
            margin-bottom: 10px;
            text-decoration: none;
        }
        .post-title:hover {
            text-decoration: underline;
        }
        .post-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .post-content {
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .post-categories {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .category-tag {
            background-color: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-published {
            background-color: #d4edda;
            color: #155724;
        }
        .status-draft {
            background-color: #fff3cd;
            color: #856404;
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
        .no-results {
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        .no-results h3 {
            color: #6c757d;
            margin-bottom: 15px;
        }
        .no-results p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        mark {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="/">ホーム</a>
            <a href="/posts.php">記事一覧</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
        </div>
        
        <h1>📝 記事一覧</h1>
        
        <!-- 検索フォーム -->
        <div class="search-form">
            <form method="GET" action="/posts.php">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search_keyword); ?>" 
                       placeholder="キーワードを入力して記事を検索..." 
                       class="search-input">
                <button type="submit" class="search-button">🔍 検索</button>
                <?php if (!empty($search_keyword)): ?>
                    <a href="/posts.php" class="clear-search">❌ 検索をクリア</a>
                <?php endif; ?>
            </form>
            <div style="margin-top: 10px; font-size: 0.9em; color: #666;">
                <strong>🔍 検索機能の特徴:</strong>
                <ul style="margin: 5px 0 0 20px; padding: 0;">
                    <li>📝 記事タイトル・内容・著者名・カテゴリから検索</li>
                    <li>🔤 大文字小文字を区別しない（例：「Laravel」と「laravel」は同じ）</li>
                    <li>🔍 部分一致検索（例：「カリ」で「カリキュラム」がヒット）</li>
                    <li>📊 検索結果は関連性順で表示（タイトル→内容→その他）</li>
                </ul>
            </div>
        </div>
        
        <!-- 検索結果表示 -->
        <?php if (!empty($search_keyword)): ?>
            <div class="search-results">
                <strong>🔍 検索結果:</strong> 
                「<?php echo htmlspecialchars($search_keyword); ?>」で検索した結果: 
                <strong><?php echo count($posts); ?>件</strong>の記事が見つかりました。
            </div>
        <?php endif; ?>
        
        <div style="margin-bottom: 20px;">
            <a href="/create_post.php" style="display: inline-block; padding: 12px 24px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; font-size: 16px;">
                📝 新しい記事を投稿
            </a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($posts)): ?>
            <div class="stats">
                <strong>📊 統計情報:</strong>
                <ul>
                    <li>総記事数: <?php echo count($posts); ?>件</li>
                    <li>公開記事: <?php echo count(array_filter($posts, function($post) { return $post['status'] === 'published'; })); ?>件</li>
                    <li>下書き: <?php echo count(array_filter($posts, function($post) { return $post['status'] === 'draft'; })); ?>件</li>
                    <?php if (!empty($search_keyword)): ?>
                        <li>検索キーワード: 「<?php echo htmlspecialchars($search_keyword); ?>」</li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <h2 class="post-title">
                        <a href="/post.php?id=<?php echo $post['id']; ?>" style="color: inherit; text-decoration: none;">
                            <?php echo highlightKeyword($post['title'], $search_keyword); ?>
                        </a>
                    </h2>
                    
                    <div class="post-meta">
                        <strong>👤 著者:</strong> <?php echo htmlspecialchars($post['author_name']); ?> |
                        <strong>📅 投稿日:</strong> <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?> |
                        <span class="status-badge status-<?php echo $post['status']; ?>">
                            <?php echo $post['status'] === 'published' ? '公開' : '下書き'; ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($post['image_path'])): ?>
                        <?php 
                            $rawPath = $post['image_path'];
                            $imageSrc = (preg_match('/^https?:\\/\\\//', $rawPath)) ? $rawPath : '/' . ltrim($rawPath, '/');
                        ?>
                        <div style="margin: 15px 0; text-align: center;">
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>" 
                                 alt="記事画像" 
                                 style="max-width: 100%; max-height: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['categories'])): ?>
                        <div class="post-categories">
                            <strong>🏷️ カテゴリ:</strong>
                            <?php 
                            $categories = explode(', ', $post['categories']);
                            foreach ($categories as $category): 
                            ?>
                                <span class="category-tag"><?php echo htmlspecialchars(trim($category)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-content">
                        <?php 
                        $content = $post['content'];
                        if (strlen($content) > 200) {
                            $short_content = substr($content, 0, 200) . '...';
                            echo highlightKeyword($short_content, $search_keyword);
                        } else {
                            echo highlightKeyword($content, $search_keyword);
                        }
                        ?>
                    </div>
                    
                    <a href="/post.php?id=<?php echo $post['id']; ?>" style="color: #007bff; text-decoration: none;">
                        📖 続きを読む →
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php if (!empty($search_keyword)): ?>
                <div class="no-results">
                    <h3>🔍 検索結果がありません</h3>
                    <p>「<?php echo htmlspecialchars($search_keyword); ?>」に一致する記事が見つかりませんでした。</p>
                    <p>別のキーワードで検索してみてください。</p>
                    <a href="/posts.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                        📋 すべての記事を見る
                    </a>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 50px;">
                    <h3>📝 記事がありません</h3>
                    <p>まだ記事が投稿されていません。</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 0.9em;">
            <p>Laravel アプリケーション - 記事一覧機能（検索機能付き）</p>
            <p>PHP Version: <?php echo PHP_VERSION; ?> | Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html> 