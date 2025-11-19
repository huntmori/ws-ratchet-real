FROM php:8.4-cli

# 필요한 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip sockets pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /app

# composer 파일 복사
COPY composer.json composer.lock ./

# 의존성 설치 (--no-dev 제거하여 모든 패키지 설치)
RUN composer install --optimize-autoloader --no-interaction

# 애플리케이션 파일 복사
COPY . .

# 포트 노출
EXPOSE 8888

# 기본 명령어
CMD ["php", "public/server.php"]