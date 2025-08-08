# Laravel ブログアプリケーション

## 📝 概要
PHPで作成されたブログアプリケーションです。記事投稿、コメント機能、ユーザー管理、検索機能を備えています。

## 🚀 デプロイ方法

### Vercelでの公開（推奨）

1. **GitHubにアップロード**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/yourusername/your-repo-name.git
   git push -u origin main
   ```

2. **Vercelでデプロイ**
   - [Vercel](https://vercel.com)にアクセス
   - GitHubアカウントでログイン
   - 「New Project」をクリック
   - GitHubリポジトリを選択
   - 自動でデプロイ完了

### 環境変数の設定

Vercelのダッシュボードで以下の環境変数を設定：

```
DB_HOST=your-database-host
DB_NAME=your-database-name
DB_USER=your-database-user
DB_PASS=your-database-password
```

## 🛠️ 必要な修正

### 1. データベース接続の修正
`database_config.php`を環境変数に対応させる：

```php
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => 3306,
    'database' => $_ENV['DB_NAME'] ?? 'laravel_app',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];
```

### 2. 画像アップロード機能の修正
クラウドストレージ（Cloudinary等）を使用する必要があります。

## 📁 ファイル構成

```
laravel-app/
├── index.php              # ホームページ
├── posts.php              # 記事一覧・検索機能
├── post.php               # 記事詳細
├── create_post.php        # 記事投稿
├── login.php              # ログイン
├── register.php           # ユーザー登録
├── dashboard.php          # ダッシュボード
├── users.php              # ユーザー管理
├── comment_approval.php   # コメント承認
├── auth.php               # 認証機能
├── database_config.php    # データベース設定
├── assets/
│   ├── css/
│   │   └── style.css      # スタイルシート
│   └── uploads/
│       └── images/        # アップロード画像
└── includes/
    ├── header.php         # ヘッダー
    └── footer.php         # フッター
```

## 🔧 技術スタック

- **フロントエンド**: HTML, CSS, JavaScript
- **バックエンド**: PHP 8.2+
- **データベース**: MySQL/PostgreSQL
- **認証**: セッションベース認証
- **画像アップロード**: ファイルシステム

## 🌟 機能一覧

- ✅ ユーザー認証（ログイン・登録）
- ✅ 記事投稿・編集・削除
- ✅ 画像アップロード機能
- ✅ コメント機能（承認制）
- ✅ 検索機能（部分一致・大文字小文字区別なし）
- ✅ ユーザー管理
- ✅ レスポンシブデザイン

## 📞 サポート

問題が発生した場合は、GitHubのIssuesで報告してください。
