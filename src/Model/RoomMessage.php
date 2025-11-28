<?php

namespace App\Model;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Enum\ChatType;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

/**
 * RoomMessage 모델 클래스
 *
 * 채팅방 메시지 정보를 담는 모델 클래스
 * 사용자가 채팅방에서 보낸 메시지를 저장하고 관리합니다.
 */
class RoomMessage extends BaseModel
{
    use Buildable, ArraySerializable;

    #[FromArrayKey(key: 'idx', required: false)]
    #[ToArrayKey(key: 'idx', exclude: false)]
    private ?int $idx = null;  // 데이터베이스 기본키

    #[FromArrayKey(key: 'uuid', required: false)]
    #[ToArrayKey(key: 'uuid', exclude: false)]
    private ?string $uuid = null;  // 메시지 고유 식별자 (UUID)

    #[FromArrayKey(key: 'room_uuid', required: false)]
    #[ToArrayKey(key: 'room_uuid', exclude: false)]
    private ?string $roomUuid = null;  // 메시지가 속한 채팅방 UUID

    #[FromArrayKey(key: 'user_uuid', required: false)]
    #[ToArrayKey(key: 'user_uuid', exclude: false)]
    private ?string $userUuid = null;  // 메시지 작성자 UUID

    #[FromArrayKey(key: 'type', required: false)]
    #[ToArrayKey(key: 'type', exclude: false)]
    private ?ChatType $type = null;  // 메시지 타입 (SIMPLE_TEXT 등)

    #[FromArrayKey(key: 'message', required: false)]
    #[ToArrayKey(key: 'message', exclude: false)]
    private ?string $message = null;  // 메시지 내용

    #[FromArrayKey(key: 'created_datetime', required: false)]
    #[ToArrayKey(key: 'created_datetime', exclude: false)]
    private ?\DateTime $createdDatetime = null;  // 메시지 생성 일시

}