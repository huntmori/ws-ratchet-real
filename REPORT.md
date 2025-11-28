# 프로젝트 분석 보고서

## 1. 프로젝트 개요

### 프로젝트 명
**ws-ratchet** - PHP 기반 실시간 WebSocket 채팅 서버

### 프로젝트 목적
실시간 채팅 기능을 제공하는 WebSocket 서버 애플리케이션으로, 사용자 관리, 채팅방 생성/참가, 실시간 메시지 송수신 기능을 구현

### 개발 환경
- **언어**: PHP 8.4
- **실행 환경**: CLI (Command Line Interface)
- **컨테이너화**: Docker Compose
- **운영체제**: Windows (개발), Linux (Docker 컨테이너)

---

## 2. 기술 스택

### 2.1 핵심 프레임워크 및 라이브러리

#### WebSocket 서버
- **cboden/ratchet** (v0.4.4): PHP WebSocket 라이브러리
- **ratchet/rfc6455**: WebSocket 프로토콜 RFC6455 구현체

#### 데이터베이스
- **MariaDB 11**: 메인 데이터베이스
- **catfan/medoo** (v2.2): 경량 PHP 데이터베이스 프레임워크 (ORM)

#### 캐싱 & 세션
- **Redis 7**: 인메모리 데이터베이스
- **predis/predis** (v3.2): PHP Redis 클라이언트

#### 의존성 관리 및 기타
- **php-di/php-di** (v7.1): PHP 의존성 주입 컨테이너
- **vlucas/phpdotenv** (v5.6): 환경변수 관리
- **monolog/monolog** (v3.9): 로깅 라이브러리

#### 테스트
- **phpunit/phpunit** (v11.5): PHP 단위 테스트 프레임워크

---

## 3. 아키텍처 구조

### 3.1 디렉토리 구조

```
ws-ratchet/
├── .docker/              # Docker 관련 설정
├── docker/              # Docker 볼륨 데이터
├── public/              # 공개 디렉토리
│   └── server.php       # WebSocket 서버 엔트리 포인트
├── src/                 # 소스 코드
│   ├── Application.php  # 애플리케이션 부트스트랩
│   ├── Attribute/       # PHP 어트리뷰트 정의
│   ├── Controller/      # 컨트롤러 계층
│   │   ├── ChatController.php
│   │   ├── RequestDispatcher.php
│   │   ├── Room/        # 채팅방 관련 핸들러
│   │   └── User/        # 사용자 관련 핸들러
│   ├── Model/           # 도메인 모델
│   │   ├── BaseModel.php
│   │   ├── Room.php
│   │   ├── User.php
│   │   ├── UsersInRoom.php
│   │   └── RoomMessage.php
│   ├── Repository/      # 데이터 접근 계층
│   │   ├── BaseRepository.php
│   │   ├── RoomRepository.php
│   │   ├── UserRepository.php
│   │   ├── RoomMessageRepository.php
│   │   └── UsersInRoomRepository.php
│   ├── Request/         # 요청 페이로드
│   │   ├── BaseRequest.php
│   │   ├── BasePayload.php
│   │   ├── Room/
│   │   └── User/
│   ├── Response/        # 응답 객체
│   ├── Enum/            # 열거형 정의
│   ├── Trait/           # 재사용 가능한 트레이트
│   └── Exception/       # 예외 클래스
├── tests/               # 테스트 코드
│   └── Repository/
├── vendor/              # Composer 의존성
├── .env                 # 환경변수 설정
├── composer.json        # PHP 의존성 정의
├── docker-compose.yml   # Docker Compose 설정
├── Dockerfile           # Docker 이미지 빌드 설정
├── init.sql             # 데이터베이스 초기화 스크립트
└── phpunit.xml          # PHPUnit 테스트 설정
```

### 3.2 아키텍처 패턴

#### MVC + Repository 패턴
- **Model**: 도메인 모델 (`src/Model/`)
- **Controller**: 요청 핸들러 (`src/Controller/`)
- **Repository**: 데이터 접근 계층 (`src/Repository/`)

#### Dispatcher 패턴
- `RequestDispatcher`: 이벤트 기반 메시지 라우팅
- 각 이벤트 타입(`user.create`, `room.create`, `room.join`, `room.chat` 등)에 대한 핸들러 등록

#### 의존성 주입 (Dependency Injection)
- PHP-DI 컨테이너 사용
- 자동 와이어링(Autowiring) 활성화
- 서비스 및 리포지토리 간 의존성 자동 주입

---

## 4. 데이터베이스 설계

### 4.1 테이블 구조

#### `user` 테이블
```sql
- idx (BIGINT, PK, AUTO_INCREMENT)
- uuid (TEXT, UNIQUE)
- id (VARCHAR(255), UNIQUE)
- password (VARCHAR(255))
- created_at (TIMESTAMP)
```
**용도**: 사용자 정보 저장

#### `room` 테이블
```sql
- idx (BIGINT, PK, AUTO_INCREMENT)
- uuid (TEXT, UNIQUE)
- room_name (TEXT)
- maximum_users (BIGINT)
- join_type (TEXT)          # PUBLIC/PRIVATE
- open_type (TEXT)          # PUBLIC/PRIVATE
- join_password (TEXT)
- room_state (TEXT)         # 방 상태
- created_datetime (DATETIME)
- updated_datetime (DATETIME)
- is_deleted (TINYINT(1))
- deleted_datetime (DATETIME)
```
**용도**: 채팅방 정보 저장

#### `users_in_room` 테이블
```sql
- idx (BIGINT, PK, AUTO_INCREMENT)
- user_uuid (TEXT)
- room_uuid (TEXT)
- state (TEXT)              # JOIN/LEAVE
- created_datetime (DATETIME)
- updated_datetime (DATETIME)
```
**용도**: 사용자-채팅방 연결 관계 저장

### 4.2 데이터베이스 특징
- **문자 인코딩**: UTF-8 (utf8mb4_unicode_ci)
- **스토리지 엔진**: InnoDB
- **트랜잭션 지원**: Yes
- **외래키 제약**: 없음 (애플리케이션 레벨에서 관리)

---

## 5. 주요 기능

### 5.1 사용자 관리
- **사용자 생성** (`user.create`)
  - ID 중복 검사
  - 비밀번호 해싱 저장
  - UUID 자동 생성

- **사용자 로그인** (`user.login`)
  - 인증 처리
  - 세션 관리 (Connection과 User 매핑)

### 5.2 채팅방 관리
- **채팅방 생성** (`room.create`)
  - 공개/비공개 방 생성
  - 최대 인원 제한 설정
  - 비밀번호 설정 (선택적)

- **채팅방 참가** (`room.join`)
  - 입장 가능 여부 확인
  - 비밀번호 검증 (비공개방)
  - 참가자 수 제한 확인

### 5.3 실시간 채팅
- **메시지 전송** (`room.chat`)
  - 실시간 메시지 브로드캐스트
  - 채팅 기록 저장
  - 채팅 타입 분류 (`ChatType` enum)

### 5.4 연결 관리
- **WebSocket 연결**
  - 연결 성공 시 환영 메시지
  - Connection ID 자동 할당
  - 연결 상태 추적

- **연결 종료**
  - 자동 정리(cleanup)
  - 연결 해제 로깅

---

## 6. 인프라 구성

### 6.1 Docker Compose 서비스

#### app (WebSocket 서버)
```yaml
- 이미지: PHP 8.4-CLI (커스텀 빌드)
- 포트: 8888
- 의존성: MariaDB, Redis
- 볼륨 마운트: ./src, ./public, ./.env
- 헬스체크: MariaDB, Redis 완료 후 시작
```

#### mariadb (데이터베이스)
```yaml
- 이미지: MariaDB 11
- 포트: 33306 (호스트) → 3306 (컨테이너)
- 볼륨: ./docker/ws-app-real-mariadb
- 초기화: init.sql 자동 실행
- 헬스체크: healthcheck.sh
```

#### redis (캐싱)
```yaml
- 이미지: Redis 7-Alpine
- 포트: 6379
- 볼륨: ./docker/ws-app-real-redis
- 데이터 영속성: AOF (Append Only File)
- 헬스체크: redis-cli ping
```

### 6.2 네트워크
- **네트워크 타입**: Bridge
- **네트워크 이름**: app-network
- **서비스 간 통신**: 컨테이너 이름으로 통신

---

## 7. 설정 관리

### 7.1 환경 변수 (.env)

```env
# 데이터베이스 설정
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=wsapp
DB_USERNAME=wsuser
DB_PASSWORD=your_database_password

# MariaDB Root 비밀번호
MARIADB_ROOT_PASSWORD=your_root_password

# Redis 설정
REDIS_HOST=redis
REDIS_PORT=6379

# 애플리케이션 설정
APP_PORT=8888

# 포트 매핑
MARIADB_HOST_PORT=33306
REDIS_HOST_PORT=6379
```

---

## 8. 실행 방법

### 8.1 Docker Compose 사용 (권장)

```bash
# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지 및 볼륨 삭제
docker-compose down -v

# 앱 컨테이너 재빌드
docker-compose build --no-cache app
```

### 8.2 로컬 실행

```bash
# WebSocket 서버 실행
php ./public/server.php

# 웹 서버 실행 (클라이언트)
cd public
php -S localhost:3000
```

---

## 9. API 명세 (WebSocket 이벤트)

### 9.1 사용자 생성
```json
{
  "event_name": "user.create",
  "payload": {
    "id": "kknd",
    "password": "1q2w3e"
  }
}
```

### 9.2 사용자 로그인
```json
{
  "event_name": "user.login",
  "payload": {
    "id": "kknd",
    "password": "1q2w3e"
  }
}
```

### 9.3 채팅방 생성
```json
{
  "event_name": "room.create",
  "payload": {
    "room_name": "PUBLIC ROOM",
    "maximum_users": 8,
    "join_type": "PUBLIC",
    "open_type": "PUBLIC"
  }
}
```

### 9.4 채팅방 참가
```json
{
  "event_name": "room.join",
  "payload": {
    "room_uuid": "ada21635-c6df-11f0-bf31-e25046673686",
    "room_password": null
  }
}
```

### 9.5 채팅 메시지 전송
```json
{
  "event_name": "room.chat",
  "payload": {
    "room_uuid": "ada21635-c6df-11f0-bf31-e25046673686",
    "message": "Hello, World!"
  }
}
```

---

## 10. 테스트

### 10.1 테스트 프레임워크
- PHPUnit 11.5

### 10.2 테스트 구조
```
tests/
└── Repository/
    └── (리포지토리 레이어 테스트)
```

### 10.3 테스트 실행
```bash
./vendor/bin/phpunit
```

### 10.4 테스트 설정
- 부트스트랩: `vendor/autoload.php`
- 테스트 대상: `src/` 디렉토리
- 컬러 출력: 활성화
- 캐시 디렉토리: `.phpunit.cache`

---

## 11. 로깅

### 11.1 로깅 라이브러리
- Monolog 3.9

### 11.2 로그 출력
- **출력 위치**: stdout (표준 출력)
- **로그 레벨**: DEBUG
- **로깅 내용**:
  - 연결 시작/종료
  - 메시지 수신/전송
  - 데이터베이스 쿼리
  - 에러 및 예외
  - 주기적인 시스템 상태 (60초마다)

### 11.3 로그 예시
```
[info] logger 초기화 완료
[info] env 로드 완료
[info] Medoo 초기화 완료
[info] redis client 초기화 완료
[info] WebSocket server started on 0.0.0.0:8888
[info] === 새 연결 ===
[info] ID: 123456789
[info] 주소: 192.168.1.100:54321
```

---

## 12. 코드 구조 및 설계 패턴

### 12.1 주요 트레이트(Trait)
- **ArraySerializable**: 배열 직렬화/역직렬화 지원
- **Buildable**: 빌더 패턴 구현
- **EnumUtils**: Enum 유틸리티 메서드

### 12.2 어트리뷰트(Attribute)
- **FromArrayKey**: 배열에서 객체로 변환 시 키 매핑
- **ToArrayKey**: 객체에서 배열로 변환 시 키 매핑
- **ArrayKeyIgnore**: 직렬화 시 필드 제외

### 12.3 열거형(Enum)
- **JoinType**: 참가 유형 (PUBLIC, PRIVATE)
- **OpenType**: 공개 유형 (PUBLIC, PRIVATE)
- **RoomState**: 방 상태
- **InRoomStatus**: 입장 상태 (JOIN, LEAVE)
- **ChatType**: 채팅 메시지 타입

### 12.4 예외 처리
- **ApiException**: API 관련 예외 처리
- 전역 에러 핸들링 (try-catch)
- 상세한 에러 로깅

---

## 13. 보안 고려사항

### 13.1 인증 및 권한
- 비밀번호 해싱 (구현 필요 확인)
- UUID 기반 식별자 사용
- 세션 관리

### 13.2 데이터베이스
- SQL Injection 방지 (Medoo ORM 사용)
- PDO Prepared Statements

### 13.3 개선 필요 사항
- HTTPS/WSS 사용 권장
- JWT 토큰 기반 인증 고려
- Rate Limiting 구현
- 입력 데이터 검증 강화

---

## 14. 성능 및 확장성

### 14.1 현재 구성
- 단일 서버 구성
- Redis를 통한 캐싱
- ReactPHP EventLoop 기반 비동기 처리

### 14.2 주기적 모니터링
- 60초마다 연결 수 및 사용자 수 로깅
- 시스템 상태 모니터링

### 14.3 확장 가능성
- Redis Pub/Sub을 통한 수평 확장 가능
- 로드 밸런서 추가 가능
- 마이크로서비스 전환 가능

---

## 15. 개발 환경 설정

### 15.1 요구 사항
- Docker & Docker Compose
- PHP 8.4 (로컬 개발 시)
- Composer

### 15.2 IDE 설정
- PHPStorm 설정 파일: `.idea/`
- PHP 버전: 8.4
- Composer 의존성 자동 로드

---

## 16. 향후 개선 방향

### 16.1 기능 개선
- [ ] 파일 첨부 기능
- [ ] 사용자 프로필 이미지
- [ ] 채팅방 검색 기능
- [ ] 읽음/안읽음 표시
- [ ] 타이핑 인디케이터

### 16.2 기술 개선
- [ ] 더 강력한 인증 시스템 (JWT)
- [ ] WebSocket 클러스터링
- [ ] 메시지 큐 도입
- [ ] 캐싱 전략 최적화
- [ ] 테스트 커버리지 확대

### 16.3 운영 개선
- [ ] CI/CD 파이프라인
- [ ] 모니터링 및 알림 시스템
- [ ] 로그 집계 및 분석
- [ ] 백업 자동화
- [ ] 성능 프로파일링

---

## 17. 결론

**ws-ratchet**은 PHP 8.4와 Ratchet WebSocket 라이브러리를 기반으로 구축된 실시간 채팅 서버 애플리케이션입니다. 깔끔한 아키텍처, 의존성 주입, Repository 패턴을 통해 유지보수가 용이한 코드베이스를 갖추고 있으며, Docker Compose를 통한 컨테이너화로 개발 및 배포가 간편합니다.

주요 강점:
- 명확한 계층 구조 (Controller, Repository, Model)
- 이벤트 기반 메시지 처리 (Dispatcher 패턴)
- 의존성 주입을 통한 느슨한 결합
- Docker를 통한 일관된 개발 환경
- 상세한 로깅 시스템

개선이 필요한 부분:
- 보안 강화 (인증/권한 시스템)
- 테스트 커버리지 확대
- 수평 확장성 고려
- 프로덕션 레벨 에러 핸들링

전반적으로 실시간 채팅 기능의 핵심 요구사항을 충족하는 잘 설계된 프로젝트입니다.

---

**보고서 작성일**: 2025-11-26
**프로젝트 경로**: `C:\Users\kknd5050\projects\ws-ratchet`