# WebSocket 채팅 서버 프로젝트

## 프로젝트 개요

PHP Ratchet 기반의 실시간 WebSocket 채팅 서버입니다.
사용자 인증, 채팅방 관리, 실시간 메시지 전송 기능을 제공합니다.

## 기술 스택

### 백엔드
- **PHP 8.1+**: 주요 프로그래밍 언어
- **Ratchet**: WebSocket 서버 프레임워크
- **React PHP**: 비동기 이벤트 루프
- **Medoo**: 데이터베이스 쿼리 빌더
- **Predis**: Redis 클라이언트
- **Monolog**: 로깅
- **PHP-DI**: 의존성 주입 컨테이너

### 프론트엔드
- **Vue 3**: UI 프레임워크
- **JavaScript**: 클라이언트 로직

### 데이터베이스
- **MySQL**: 주 데이터베이스
- **Redis**: 세션 및 캐시 관리

## 프로젝트 구조

```
ws-ratchet/
├── src/                      # 서버 소스 코드
│   ├── Attribute/           # PHP Attribute 클래스
│   ├── Controller/          # WebSocket 컨트롤러 및 핸들러
│   ├── Enum/               # 열거형 정의
│   ├── Exception/          # 커스텀 예외 클래스
│   ├── Handler/            # 핸들러 클래스
│   ├── Model/              # 데이터 모델
│   ├── Repository/         # 데이터 접근 계층
│   ├── Request/            # 요청 페이로드
│   ├── Response/           # 응답 객체
│   ├── Trait/              # 재사용 가능한 트레이트
│   └── Application.php     # 메인 애플리케이션
├── public/                  # 클라이언트 파일
│   ├── index.html          # 테스트 클라이언트 UI
│   ├── app.js              # 클라이언트 로직
│   └── server.php          # 서버 진입점
├── document/               # 프로젝트 문서
├── tests/                  # 단위 테스트
├── vendor/                 # Composer 의존성
├── docker/                 # Docker 설정
├── .env                    # 환경 변수
├── composer.json           # PHP 의존성 정의
└── docker-compose.yml      # Docker Compose 설정
```

## 주요 기능

### 1. 사용자 관리
- 사용자 회원가입
- 사용자 로그인/로그아웃
- 사용자 세션 관리

### 2. 채팅방 관리
- 채팅방 생성
- 채팅방 참여/퇴장
- 채팅방 종류:
  - 공개 방 (PUBLIC)
  - 초대 방 (INVITE)
  - 비밀번호 방 (PASSWORD)

### 3. 메시지 전송
- 실시간 채팅 메시지
- 메시지 저장
- 메시지 이력 조회

### 4. 실시간 통신
- WebSocket 기반 양방향 통신
- 이벤트 기반 메시지 라우팅
- 실시간 사용자 상태 관리

## 핵심 아키텍처

### 1. 이벤트 기반 라우팅
클라이언트 요청은 `event_name`에 따라 적절한 핸들러로 라우팅됩니다.

```
Client → ChatController → RequestDispatcher → Handler → Response
```

### 2. Attribute 기반 직렬화
PHP 8 Attribute를 활용하여 객체와 배열 간 변환을 자동화합니다.

### 3. Builder 패턴
메서드 체이닝을 통한 유연한 객체 생성을 지원합니다.

### 4. Repository 패턴
데이터 접근 로직을 캡슐화하여 관심사를 분리합니다.

## 데이터베이스 스키마

### user 테이블
- idx: 기본키
- uuid: 고유 식별자
- id: 로그인 ID
- password: 비밀번호 (해시)
- created_at: 생성 일시

### room 테이블
- idx: 기본키
- uuid: 고유 식별자
- room_name: 방 이름
- maximum_users: 최대 인원
- join_type: 참여 방식 (PUBLIC/INVITE/PASSWORD)
- open_type: 공개 여부 (PUBLIC/PRIVATE)
- join_password: 참여 비밀번호
- room_state: 운영 상태 (OPEN/CLOSE)
- created_datetime: 생성 일시
- updated_datetime: 수정 일시

### users_in_room 테이블
- idx: 기본키
- user_uuid: 사용자 UUID
- room_uuid: 채팅방 UUID
- state: 참여 상태 (JOIN/LEAVE)
- created_datetime: 생성 일시
- updated_datetime: 수정 일시

### room_message 테이블
- idx: 기본키
- uuid: 고유 식별자
- room_uuid: 채팅방 UUID
- user_uuid: 작성자 UUID
- type: 메시지 타입
- message: 메시지 내용
- created_datetime: 생성 일시

## API 이벤트

### 사용자 관련
- `user_create`: 사용자 생성
- `user_login`: 사용자 로그인

### 채팅방 관련
- `room_create`: 채팅방 생성
- `room_join`: 채팅방 참여
- `room_chat`: 채팅 메시지 전송

## 환경 설정

### .env 파일
```bash
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=chat_db
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## 실행 방법

### Docker 사용
```bash
docker-compose up -d
```

### 수동 실행
```bash
# 의존성 설치
composer install

# 서버 시작
php public/server.php
```

### 클라이언트 접속
브라우저에서 `public/index.html` 파일을 열어 테스트합니다.

## 개발 가이드

### 새로운 이벤트 핸들러 추가

1. `RequestHandlerInterface`를 구현하는 핸들러 클래스 생성
2. `RequestDispatcher`에 이벤트명과 핸들러 매핑 추가
3. Request 페이로드 클래스 생성 (필요시)

### 새로운 모델 추가

1. `BaseModel`을 상속하는 모델 클래스 생성
2. `ArraySerializable`, `Buildable` 트레이트 사용
3. Attribute로 필드 매핑 정의
4. Repository 클래스 생성

## 테스트

```bash
# PHPUnit 테스트 실행
vendor/bin/phpunit
```

## 문서

자세한 문서는 `document/` 폴더를 참조하세요:

- [Application.md](./Application.md) - 메인 애플리케이션
- [ChatController.md](./ChatController.md) - 채팅 컨트롤러
- [ArraySerializable.md](./ArraySerializable.md) - 직렬화 트레이트

## 라이선스

이 프로젝트는 학습 목적으로 작성되었습니다.
