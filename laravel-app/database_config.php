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

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            // SSL接続（PlanetScale等）を環境変数で有効化
            $enableSsl = isset($_ENV['DB_SSL']) && in_array(strtolower((string)$_ENV['DB_SSL']), ['1', 'true', 'yes']);
            if ($enableSsl && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                // CA未指定でも接続できるように検証は無効化（必要ならDB_SSL_CAで指定）
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            if (!empty($_ENV['DB_SSL_CA']) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $_ENV['DB_SSL_CA'];
            }

            $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("データベース接続エラー: " . $e->getMessage());
        }
    }
}
?> 