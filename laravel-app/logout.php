<?php
/**
 * ログアウト処理
 * ユーザーのログアウト処理
 */

require_once 'auth.php';

// ログアウト処理
logoutUser();

// 成功メッセージを設定
$_SESSION['success_message'] = 'ログアウトしました。';

// ホームページにリダイレクト
header('Location: /');
exit;
?>
