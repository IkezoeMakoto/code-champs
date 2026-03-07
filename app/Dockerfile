FROM php:8.4-fpm

# 作業ディレクトリの設定
WORKDIR /var/www/html

# GID を環境変数として定義
ARG WWW_GROUP=docker
ARG DOCKER_GID=1000
ARG TARGET_USER=www-data

# グループを GID 指定で作成
RUN groupadd -g ${DOCKER_GID} ${WWW_GROUP} && \
    usermod -aG ${WWW_GROUP} ${TARGET_USER}

# 依存関係のインストール
RUN apt-get update && apt-get install -y \
    procps \
    curl \
    zip \
    unzip \
    git \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    gosu \
    ca-certificates curl gnupg \
    && docker-php-ext-install pdo_mysql pcntl bcmath

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# アプリケーションファイルのコピー
COPY . /var/www/html

# 権限の設定
RUN chown -R www-data:www-data /var/www/html

# Add Docker's official GPG key:
RUN install -m 0755 -d /etc/apt/keyrings && \
    curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc && \
    chmod a+r /etc/apt/keyrings/docker.asc
# Add the repository to Apt sources:
RUN echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
    $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
    tee /etc/apt/sources.list.d/docker.list > /dev/null
RUN apt-get update && apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

USER www-data

EXPOSE 9000
