FROM php:8.2-apache

# 必要な拡張機能をインストール（pdo_mysql, mysqli, zip, curl）
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       ca-certificates \
       libzip-dev \
       libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# ドキュメントルートにコードを配置
WORKDIR /var/www/html
COPY . /var/www/html

# ApacheのDocumentRootをアプリ配下に変更
ENV APACHE_DOCUMENT_ROOT=/var/www/html/laravel-app
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/sites-available/default-ssl.conf || true

# DirectoryIndex を明示
RUN bash -lc 'echo "<Directory ${APACHE_DOCUMENT_ROOT}>\n    DirectoryIndex index.php index.html\n</Directory>" \
    > /etc/apache2/conf-available/z-app-dirindex.conf' \
    && a2enconf z-app-dirindex

# Apacheの設定（必要に応じて）
EXPOSE 80

# 環境変数（例）
ENV PHP_DISPLAY_ERRORS=0


