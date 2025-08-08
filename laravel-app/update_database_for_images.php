<?php
/**
 * データベース更新スクリプト
 * postsテーブルにimage_pathカラムを追加
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
    
    // postsテーブルにimage_pathカラムが存在するかチェック
    $check_column_query = "
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'laravel_app' 
        AND TABLE_NAME = 'posts' 
        AND COLUMN_NAME = 'image_path'
    ";
    
    $check_stmt = $pdo->prepare($check_column_query);
    $check_stmt->execute();
    $column_exists = $check_stmt->fetch();
    
    if (!$column_exists) {
        // image_pathカラムを追加
        $alter_query = "ALTER TABLE posts ADD COLUMN image_path VARCHAR(255) NULL AFTER content";
        $pdo->exec($alter_query);
        echo "✅ postsテーブルにimage_pathカラムを追加しました。\n";
    } else {
        echo "ℹ️ image_pathカラムは既に存在します。\n";
    }
    
    // データベース構造を確認
    $describe_query = "DESCRIBE posts";
    $describe_stmt = $pdo->query($describe_query);
    $columns = $describe_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 postsテーブルの構造:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']} ({$column['Null']})\n";
    }
    
    echo "\n✅ データベース更新が完了しました！\n";
    
} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}
?>
