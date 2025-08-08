<?php
/**
 * コメント承認機能のためのデータベース更新スクリプト
 * commentsテーブルにstatusカラムを追加
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

try {
    require 'database_config.php';
    $pdo = getDatabaseConnection();
    
    // commentsテーブルにstatusカラムが存在するかチェック
    $check_column_query = "
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'laravel_app' 
        AND TABLE_NAME = 'comments' 
        AND COLUMN_NAME = 'status'
    ";
    
    $check_stmt = $pdo->prepare($check_column_query);
    $check_stmt->execute();
    $column_exists = $check_stmt->fetch();
    
    if (!$column_exists) {
        // statusカラムを追加
        $alter_query = "ALTER TABLE comments ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER content";
        $pdo->exec($alter_query);
        echo "✅ commentsテーブルにstatusカラムを追加しました。\n";
        
        // 既存のコメントを承認済みに更新
        $update_query = "UPDATE comments SET status = 'approved' WHERE status IS NULL OR status = ''";
        $pdo->exec($update_query);
        echo "✅ 既存のコメントを承認済みに更新しました。\n";
    } else {
        echo "ℹ️ statusカラムは既に存在します。\n";
    }
    
    // データベース構造を確認
    $describe_query = "DESCRIBE comments";
    $describe_stmt = $pdo->query($describe_query);
    $columns = $describe_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 commentsテーブルの構造:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']} ({$column['Null']})\n";
    }
    
    echo "\n✅ データベース更新が完了しました！\n";
    
} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}
?>
