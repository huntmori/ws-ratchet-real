# ArraySerializable Trait 문서

## 개요

`ArraySerializable` 트레이트는 객체와 배열/JSON 간의 직렬화/역직렬화를 제공합니다.
PHP Attribute를 사용하여 유연한 매핑을 지원합니다.

## 주요 기능

### 1. JSON/배열 → 객체 변환
- `fromJson()`: JSON 문자열 또는 배열에서 객체 생성
- `fromJsonArray()`: JSON 배열을 객체 배열로 변환

### 2. 객체 → JSON/배열 변환
- `toArray()`: 객체를 연관 배열로 변환
- `toJson()`: 객체를 JSON 문자열로 변환

### 3. Attribute 기반 매핑
- `FromArrayKey`: 배열에서 읽을 키 지정
- `ToArrayKey`: 배열로 변환 시 키 지정
- `ArrayKeyIgnore`: 직렬화/역직렬화에서 제외

### 4. 자동 타입 변환
- 기본 타입 (int, float, string, bool, array)
- DateTime / DateTimeImmutable
- Enum (BackedEnum, UnitEnum)
- 중첩 객체 (ArraySerializable 구현 클래스)

## 사용 예시

### 기본 사용

```php
use App\Trait\ArraySerializable;

class User
{
    use ArraySerializable;

    public string $name;
    public int $age;
}

// JSON에서 객체 생성
$user = User::fromJson('{"name": "홍길동", "age": 30}');

// 객체를 배열로 변환
$array = $user->toArray();
// ['name' => '홍길동', 'age' => 30]

// 객체를 JSON으로 변환
$json = $user->toJson();
// '{"name":"홍길동","age":30}'
```

### Attribute를 사용한 매핑

```php
use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Attribute\ArrayKeyIgnore;

class User
{
    use ArraySerializable;

    #[FromArrayKey(key: 'user_id', required: true)]
    #[ToArrayKey(key: 'id')]
    public string $userId;

    #[ToArrayKey(key: 'password', exclude: true)]
    public string $password;

    #[ArrayKeyIgnore]
    public $tempData;
}

// 배열의 'user_id' 키를 userId 프로퍼티에 매핑
$user = User::fromJson(['user_id' => '123', 'password' => 'secret']);

// toArray() 시 'id' 키로 변환, password는 제외, tempData는 무시
$array = $user->toArray();
// ['id' => '123']
```

### Enum 타입 변환

```php
use App\Enum\RoomState;

class Room
{
    use ArraySerializable;

    public RoomState $state;
}

// 문자열 값이 자동으로 Enum으로 변환
$room = Room::fromJson(['state' => 'OPEN']);
// $room->state === RoomState::OPEN

// Enum이 자동으로 값으로 변환
$array = $room->toArray();
// ['state' => 'OPEN']
```

### DateTime 변환

```php
class Event
{
    use ArraySerializable;

    public DateTime $createdAt;
}

// 문자열이 자동으로 DateTime으로 변환
$event = Event::fromJson(['createdAt' => '2024-01-01 12:00:00']);

// DateTime이 ISO 8601 문자열로 변환
$array = $event->toArray();
// ['createdAt' => '2024-01-01 12:00:00']
```

### 중첩 객체

```php
class Address
{
    use ArraySerializable;

    public string $city;
    public string $street;
}

class User
{
    use ArraySerializable;

    public string $name;
    public Address $address;
}

// 중첩된 객체도 자동 변환
$user = User::fromJson([
    'name' => '홍길동',
    'address' => [
        'city' => '서울',
        'street' => '강남대로'
    ]
]);

// 중첩된 객체도 자동으로 배열로 변환
$array = $user->toArray();
```

## Attribute 상세

### FromArrayKey
배열에서 객체로 변환할 때 사용할 키를 지정합니다.

```php
#[FromArrayKey(key: 'array_key', required: false)]
```

**파라미터:**
- `key`: 배열에서 읽을 키 이름
- `required`: 필수 항목 여부 (기본값: false)

### ToArrayKey
객체를 배열로 변환할 때 사용할 키를 지정합니다.

```php
#[ToArrayKey(key: 'array_key', exclude: false)]
```

**파라미터:**
- `key`: 배열에 저장할 키 이름
- `exclude`: 배열 변환 시 제외 여부 (기본값: false)

### ArrayKeyIgnore
직렬화/역직렬화에서 해당 프로퍼티를 완전히 무시합니다.

```php
#[ArrayKeyIgnore]
```

## 지원하는 타입

### 기본 타입
- `int`, `float`, `string`, `bool`, `array`

### 날짜/시간
- `DateTime`
- `DateTimeImmutable`

### Enum
- `BackedEnum` (값이 있는 Enum)
- `UnitEnum` (값이 없는 Enum)

### 객체
- `ArraySerializable`를 구현한 클래스
- `JsonSerializable` 인터페이스를 구현한 클래스
- `fromJson()` 메서드가 있는 클래스

## 에러 처리

### InvalidArgumentException
다음과 같은 경우 예외가 발생합니다:
- 잘못된 JSON 형식
- 필수 키가 누락된 경우
- 타입 변환 실패

```php
try {
    $user = User::fromJson('invalid json');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
    // "Invalid JSON: Syntax error"
}
```

## 모범 사례

1. **보안**: 민감한 데이터는 `exclude: true` 또는 `ArrayKeyIgnore` 사용
2. **필수 필드**: 중요한 필드는 `required: true` 설정
3. **타입 힌팅**: 프로퍼티에 타입을 명시하여 자동 변환 활용
4. **Null 허용**: Nullable 프로퍼티는 `?Type` 형식으로 선언
