-- wsapp 데이터베이스는 환경변수로 이미 생성됨
USE wsapp;

-- root 사용자에게 모든 호스트 접근 허용
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

-- 기존 wsuser 모두 삭제 (구체적인 호스트 제거)
DROP USER IF EXISTS 'wsuser'@'localhost';
DROP USER IF EXISTS 'wsuser'@'127.0.0.1';
DROP USER IF EXISTS 'wsuser'@'::1';
DROP USER IF EXISTS 'wsuser'@'172.%';
DROP USER IF EXISTS 'wsuser'@'172.18.%';
DROP USER IF EXISTS 'wsuser'@'172.18.0.1';

-- wsuser@'%'만 생성 (모든 호스트에서 접근 가능)
CREATE USER IF NOT EXISTS 'wsuser'@'%' IDENTIFIED BY 'wspass';

-- 전체 권한 부여
GRANT ALL PRIVILEGES ON *.* TO 'wsuser'@'%' WITH GRANT OPTION;

-- user 테이블 생성
CREATE TABLE IF NOT EXISTS `user` (
    `idx` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `id` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`idx`),
    UNIQUE KEY `user_uuid_unique` (`uuid`),
    UNIQUE KEY `user_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 권한 적용
FLUSH PRIVILEGES;

-- 생성 확인
SELECT user, host, plugin FROM mysql.user WHERE user IN ('root', 'wsuser');
