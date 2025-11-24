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
CREATE TABLE `room` (
                        `idx` bigint(20) NOT NULL AUTO_INCREMENT,
                        `uuid` text      NOT NULL,
                        `room_name` text      NOT NULL,
                        `maximum_users` bigint(20) NOT NULL,
                        `join_type` text      DEFAULT NULL,
                        `open_type` text      DEFAULT NULL,
                        `join_password` text      DEFAULT NULL,
                        `created_datetime` datetime DEFAULT current_timestamp(),
                        `updated_datetime` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
                        `deleted_datetime` datetime DEFAULT NULL,
                        `room_state` text      NOT NULL,
                        PRIMARY KEY (`idx`),
                        UNIQUE KEY `room_pk` (`uuid`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user` (
                        `idx` bigint(20) NOT NULL AUTO_INCREMENT,
                        `uuid` text NOT NULL,
                        `id` varchar(255) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `created_at` timestamp NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`idx`),
                        UNIQUE KEY `user_uuid_unique` (`uuid`) USING HASH,
                        UNIQUE KEY `user_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_in_room` (
                                 `idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'auto increament',
                                 `user_uuid` text      NOT NULL,
                                 `room_uuid` text      NOT NULL,
                                 `state` text      NOT NULL DEFAULT 'JOIN' COMMENT 'JOIN, LEAVE',
                                 `created_datetime` datetime DEFAULT current_timestamp(),
                                 `updated_datetime` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                                 PRIMARY KEY (`idx`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- 권한 적용
FLUSH PRIVILEGES;

-- 생성 확인
SELECT user, host, plugin FROM mysql.user WHERE user IN ('root', 'wsuser');
