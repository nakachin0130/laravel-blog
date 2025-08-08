-- MySQL認証方法の変更
-- rootユーザーの認証方法をmysql_native_passwordに変更

-- 現在の認証方法を確認
SELECT User, Host, plugin FROM mysql.user WHERE User='root';

-- rootユーザーの認証方法を変更
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'nh01300130';

-- 変更を適用
FLUSH PRIVILEGES;

-- 変更後の確認
SELECT User, Host, plugin FROM mysql.user WHERE User='root';

-- 終了
EXIT; 