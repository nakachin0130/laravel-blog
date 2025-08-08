<?php
/**
 * データベース接続設定
 * Laravel用のMySQLデータベース設定
 */

// データベース設定（環境変数対応・重複宣言を防ぐ）
if (!isset($db_config)) {
    $db_config = [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => 3306,
        'database' => $_ENV['DB_NAME'] ?? 'laravel_app',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? 'nh01300130',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci'
    ];
}

// データベース接続関数（重複宣言を防ぐ）
if (!function_exists('getDatabaseConnection')) {
    function getDatabaseConnection() {
        global $db_config;
        
        // $db_configが存在しない場合のフォールバック
        if (!isset($db_config) || !is_array($db_config)) {
            $db_config = [
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => 3306,
                'database' => $_ENV['DB_NAME'] ?? 'laravel_app',
                'username' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASS'] ?? 'nh01300130',
                'charset' => 'utf8'
            ];
        }
        
        try {
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("データベース接続エラー: " . $e->getMessage());
        }
    }
}
?> 