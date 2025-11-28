# Application.php 문서

## 개요

`Application` 클래스는 WebSocket 채팅 서버의 메인 애플리케이션 클래스입니다.
서버의 초기화, 의존성 설정, 서버 실행을 담당합니다.

## 주요 기능

### 1. 서버 초기화
- 환경 변수 로드 (.env 파일)
- 로거 초기화 (Monolog)
- 데이터베이스 연결 설정 (Medoo)
- Redis 연결 설정 (Predis)
- DI 컨테이너 설정 (PHP-DI)

### 2. WebSocket 서버 구성
- Ratchet 프레임워크를 사용한 WebSocket 서버 설정
- ChatController를 메시지 핸들러로 등록
- React PHP 이벤트 루프 사용

### 3. 서버 실행
- 지정된 포트에서 WebSocket 서버 실행 (기본: 8888)
- 클라이언트 연결 수락 및 관리

## 주요 메서드

### bootstrap()
서버를 부트스트랩하고 필요한 모든 의존성을 설정합니다.

```php
public function bootstrap(): self
```

### setLogger(ContainerBuilder $containerBuilder)
Monolog 로거를 초기화하고 컨테이너에 등록합니다.

### setEnv(ContainerBuilder $containerBuilder)
.env 파일에서 환경 변수를 로드합니다.

### setMeedo(ContainerBuilder $containerBuilder)
Medoo 데이터베이스 연결을 설정합니다.

### setRedis(ContainerBuilder $containerBuilder)
Predis 클라이언트를 설정합니다.

### run()
WebSocket 서버를 시작하고 실행합니다.

```php
public function run(): void
```

## 사용 예시

```php
$app = new Application();
$app->bootstrap()->run();
```

## 의존성

- `ratchet/ratchet`: WebSocket 서버
- `react/event-loop`: 비동기 이벤트 루프
- `php-di/php-di`: 의존성 주입 컨테이너
- `monolog/monolog`: 로깅
- `catfan/medoo`: 데이터베이스 쿼리 빌더
- `predis/predis`: Redis 클라이언트
- `vlucas/phpdotenv`: 환경 변수 로더

## 설정 파일

### .env
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=chat_db
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## 로그 파일

- `php://stdout`: 표준 출력으로 로그 기록
- 로그 레벨: DEBUG
