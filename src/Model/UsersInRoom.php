<?php

namespace App\Model;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Enum\InRoomStatus;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;
use DateTime;

/**
 * UsersInRoom 모델 클래스
 *
 * 사용자와 채팅방 간의 관계를 나타내는 모델 클래스
 * 사용자의 채팅방 참여/퇴장 상태를 관리합니다.
 *
 * @method static UsersInRoom builder()
 * @method UsersInRoom build()
 *
 * Setter methods:
 * @method UsersInRoom idx(?int $idx)
 * @method UsersInRoom userUuid(?string $userUuid)
 * @method UsersInRoom roomUuid(?string $roomUuid)
 * @method UsersInRoom state(?InRoomStatus $status)
 * @method UsersInRoom createdDatetime(?DateTime $createdDatetime)
 * @method UsersInRoom updatedDatetime(?DateTime $updatedDatetime)
 */
class UsersInRoom extends BaseModel
{
    use Buildable, ArraySerializable;

    #[FromArrayKey(key: 'idx', required: false)]
    #[ToArrayKey(key: 'idx', exclude: false)]
    public ?int $idx = null;  // 데이터베이스 기본키

    #[FromArrayKey(key: 'user_uuid', required: false)]
    #[ToArrayKey(key: 'user_uuid', exclude: false)]
    public ?string $userUuid = null;  // 사용자 UUID

    #[FromArrayKey(key: 'room_uuid', required: false)]
    #[ToArrayKey(key: 'room_uuid', exclude: false)]
    public ?string $roomUuid = null;  // 채팅방 UUID

    #[FromArrayKey(key: 'state', required: false)]
    #[ToArrayKey(key: 'state', exclude: false)]
    public ?InRoomStatus $state;  // 참여 상태 (JOIN/LEAVE)

    #[FromArrayKey(key: 'created_datetime', required: false)]
    #[ToArrayKey(key: 'created_datetime', exclude: false)]
    public ?DateTime $createdDatetime = null;  // 레코드 생성 일시

    #[FromArrayKey(key: 'updated_datetime', required: false)]
    #[ToArrayKey(key: 'updated_datetime', exclude: false)]
    public ?DateTime $updatedDatetime = null;  // 레코드 수정 일시
}