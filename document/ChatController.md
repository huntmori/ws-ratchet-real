# ChatController.php 문서

## 개요

`ChatController`는 WebSocket 메시지를 처리하는 메인 컨트롤러입니다.
Ratchet의 `MessageComponentInterface`를 구현하여 WebSocket 이벤트를 처리합니다.

## 주요 기능

### 1. 연결 관리
- 새로운 클라이언트 연결 수락
- 연결 종료 처리
- 연결된 사용자 목록 관리

### 2. 메시지 라우팅
- 클라이언트로부터 받은 메시지를 적절한 핸들러로 라우팅
- RequestDispatcher를 통한 이벤트 기반 메시지 처리

### 3. 에러 처리
- WebSocket 에러 로깅
- 예외 발생 시 클라이언트에게 에러 응답 전송

## 주요 메서드

### onOpen(ConnectionInterface $conn)
새로운 클라이언트가 연결되었을 때 호출됩니다.

```php
function onOpen(ConnectionInterface $conn): void
```

**동작:**
- 연결 정보 로깅
- ConnectionPair 객체 생성
- connections 배열에 추가

### onMessage(ConnectionInterface $from, $msg)
클라이언트로부터 메시지를 받았을 때 호출됩니다.

```php
function onMessage(ConnectionInterface $from, $msg): void
```

**동작:**
1. JSON 메시지 파싱
2. event_name 추출
3. RequestDispatcher를 통해 적절한 핸들러 호출
4. 응답 메시지 생성 및 전송

### onClose(ConnectionInterface $conn)
클라이언트 연결이 종료되었을 때 호출됩니다.

```php
function onClose(ConnectionInterface $conn): void
```

**동작:**
- 연결 종료 로깅
- connections 배열에서 제거
- Redis에서 사용자 정보 삭제

### onError(ConnectionInterface $conn, \Exception $e)
에러가 발생했을 때 호출됩니다.

```php
function onError(ConnectionInterface $conn, \Exception $e): void
```

**동작:**
- 에러 로깅
- 연결 종료

## 메시지 핸들러

### 사용자 관련
- `UserLoginHandler`: 사용자 로그인
- `UserCreateHandler`: 사용자 생성

### 채팅방 관련
- `RoomCreateHandler`: 채팅방 생성
- `RoomJoinHandler`: 채팅방 참여
- `RoomChatHandler`: 채팅 메시지 전송

## 메시지 형식

### 요청 메시지
```json
{
  "event_name": "user_login",
  "payload": {
    "id": "user123",
    "password": "pass123"
  }
}
```

### 응답 메시지
```json
{
  "success": true,
  "event_name": "user_login",
  "message": "로그인 성공",
  "data": {
    "uuid": "...",
    "id": "user123"
  },
  "code": 200
}
```

## 연결 관리

### ConnectionPair
각 연결은 `ConnectionPair` 객체로 관리됩니다:
- `connection`: Ratchet ConnectionInterface
- `user`: 로그인한 User 객체

### 연결 ID
- `spl_object_id($conn)`를 키로 사용하여 connections 배열에서 관리

## Redis 활용

- 사용자 세션 관리
- 채팅방 참여자 목록
- 실시간 데이터 캐싱
