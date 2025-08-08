<?php
/**
 * 認証機能ヘルパー
 * ログイン・認証関連の共通関数
 */

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ユーザーがログインしているかチェック
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * ログインしているユーザーのIDを取得
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * ログインしているユーザーの情報を取得
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        require 'database_config.php';
        $pdo = getDatabaseConnection();
        
        $query = "SELECT id, name, email, created_at FROM users WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([getCurrentUserId()]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // エラーログを記録（開発環境のみ）
        error_log("getCurrentUser error: " . $e->getMessage());
        return null;
    }
}

/**
 * ユーザーをログインさせる
 * @param int $user_id
 * @param string $user_name
 * @return void
 */
function loginUser($user_id, $user_name) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $user_name;
    $_SESSION['login_time'] = time();
}

/**
 * ユーザーをログアウトさせる
 * @return void
 */
function logoutUser() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['login_time']);
    session_destroy();
}

/**
 * ログインが必要なページでリダイレクト
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = 'ログインが必要です。';
        header('Location: /login.php');
        exit;
    }
}

/**
 * 既にログインしている場合はダッシュボードにリダイレクト
 * @return void
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: /dashboard.php');
        exit;
    }
}

/**
 * パスワードを検証
 * @param string $password
 * @param string $hashed_password
 * @return bool
 */
function verifyPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

/**
 * パスワードをハッシュ化
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?>
