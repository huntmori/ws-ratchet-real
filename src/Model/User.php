<?php

namespace App\Model;

use App\Attribute\ArrayKeyIgnore;
use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;
use Ratchet\ConnectionInterface;

/**
 * User 모델 클래스
 *
 * 사용자 정보를 담는 모델 클래스
 * 사용자의 기본 정보와 WebSocket 연결 정보를 관리합니다.
 *
 * @method User id(string $id):
 * @method User uuid(string $uuid)
 * @method User password(string $password)
 * @method User createdAt(\DateTime $createdAt)
 * @method string uuid()
 */
class User extends BaseModel
{
    use Buildable,
        ArraySerializable;

    #[ToArrayKey(key: 'idx')]
    public ?int $idx = null;  // 데이터베이스 기본키

    #[ToArrayKey(key: 'uuid', exclude: false)]
    #[FromArrayKey(key: 'uuid', required: false)]
    public ?string $uuid = null;  // 사용자 고유 식별자 (UUID)

    #[ToArrayKey(key: 'id', exclude: false)]
    #[FromArrayKey(key: 'id', required: false)]
    public ?string $id = null;  // 사용자 로그인 ID

    #[ToArrayKey(key: 'password', exclude: true)]  // 배열 변환 시 제외 (보안)
    #[FromArrayKey(key: 'password', required: false)]
    public ?string $password = null;  // 사용자 비밀번호 (해시 저장)

    #[ToArrayKey(key: 'created_at', exclude: false)]
    #[FromArrayKey(key: 'created_at', required: false)]
    public ?\DateTime $createdAt = null;  // 계정 생성 일시

    #[ArrayKeyIgnore]  // 직렬화/역직렬화에서 제외
    public ConnectionInterface $connection;  // WebSocket 연결 객체
}