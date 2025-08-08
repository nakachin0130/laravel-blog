<?php
/**
 * ログインページ
 * ユーザーのログイン処理
 */

require_once 'auth.php';

// 既にログインしている場合はダッシュボードにリダイレクト
redirectIfLoggedIn();

$error_message = '';
$success_message = '';

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // バリデーション
    if (empty($email)) {
        $error_message = 'メールアドレスを入力してください。';
    } elseif (empty($password)) {
        $error_message = 'パスワードを入力してください。';
    } else {
                                               try {
                    require 'database_config.php';
                    $pdo = getDatabaseConnection();
            
            // ユーザーを検索
            $query = "SELECT id, name, email, password FROM users WHERE email = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && verifyPassword($password, $user['password'])) {
                // ログイン成功
                loginUser($user['id'], $user['name']);
                $success_message = "ログインしました！ダッシュボードに移動します...";
                
                // 3秒後にダッシュボードにリダイレクト
                header("refresh:3;url=/dashboard.php");
            } else {
                $error_message = 'メールアドレスまたはパスワードが正しくありません。';
            }
            
        } catch (Exception $e) {
            $error_message = "データベースエラー: " . $e->getMessage();
        }
    }
}
?>
<?php
$page_title = 'ログイン - Laravel App';
include 'includes/header.php';
?>

<!-- ログインセクション -->
<section class="card" style="max-width: 500px; margin: 2rem auto;">
    <div class="card-header text-center">
        <h2 class="card-title">🔐 ログイン</h2>
        <p style="color: var(--gray-600);">アカウントにログインしてください</p>
    </div>
    
    <div class="card-body">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email" class="form-label">📧 メールアドレス</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       required placeholder="example@email.com">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">🔒 パスワード</label>
                <input type="password" id="password" name="password" class="form-control"
                       required placeholder="パスワードを入力">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">🔐 ログイン</button>
        </form>
        
        <div class="card mt-4" style="background: var(--gray-100);">
            <div class="card-body">
                <h5 style="margin-bottom: var(--spacing-md);">📋 デモアカウント</h5>
                <div class="grid grid-1" style="gap: var(--spacing-sm);">
                    <div style="padding: var(--spacing-sm); background: var(--white); border-radius: var(--border-radius);">
                        <strong>Admin:</strong> admin@example.com / password123
                    </div>
                    <div style="padding: var(--spacing-sm); background: var(--white); border-radius: var(--border-radius);">
                        <strong>Test User:</strong> test@example.com / password123
                    </div>
                    <div style="padding: var(--spacing-sm); background: var(--white); border-radius: var(--border-radius);">
                        <strong>Developer:</strong> dev@example.com / password123
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p style="color: var(--gray-600); margin-bottom: var(--spacing-sm);">
                アカウントをお持ちでない方は <a href="/create_user.php">新規登録</a>
            </p>
            <a href="/" class="btn btn-outline">🏠 ホームに戻る</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
