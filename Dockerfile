FROM php:8.2-apache

# 必要な拡張機能をインストール（pdo_mysql, mysqli, zip, curl）
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libzip-dev \
       libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# ドキュメントルートにコードを配置
WORKDIR /var/www/html
COPY . /var/www/html

# Apacheの設定（必要に応じて）
EXPOSE 80

# 環境変数（例）
ENV PHP_DISPLAY_ERRORS=0


