<?php

namespace App\Model;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Enum\JoinType;
use App\Enum\OpenType;
use App\Enum\RoomState;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

/**
 * Room 모델 클래스
 *
 * 채팅방 정보를 담는 모델 클래스
 * 채팅방의 설정, 상태, 메타데이터를 관리합니다.
 *
 * @method $this idx(int|null $value)
 * @method $this uuid(string|null $value)
 * @method $this roomName(string|null $value)
 * @method $this maximumUsers(int|null $value)
 * @method $this joinType(JoinType $value)
 * @method $this openType(OpenType $value)
 * @method $this joinPassword(string|null $value)
 * @method $this createdDatetime(\DateTime|null $value)
 * @method $this updatedDatetime(\DateTime|null $value)
 * @method $this isDeleted(bool $value)
 * @method $this deletedDatetime(\DateTime|null $value)
 * @method $this roomState(RoomState $value)
 */
class Room extends BaseModel
{
    use ArraySerializable, Buildable;

    #[FromArrayKey(key: 'idx', required: false)]
    #[ToArrayKey(key: 'idx', exclude: true)]  // 배열 변환 시 제외
    public ?int $idx;  // 데이터베이스 기본키

    #[ToArrayKey(key: 'uuid', exclude: false)]
    #[FromArrayKey(key: 'uuid', required: false)]
    public ?string $uuid;  // 채팅방 고유 식별자 (UUID)

    #[ToArrayKey(key: 'room_name', exclude: false)]
    #[FromArrayKey(key: 'room_name', required: false)]
    public ?string $roomName;  // 채팅방 이름

    #[ToArrayKey(key: 'maximum_users', exclude: false)]
    #[FromArrayKey(key: 'maximum_users', required: false)]
    public ?int $maximumUsers;  // 최대 참여 가능 인원

    #[ToArrayKey(key: 'join_type', exclude: false)]
    #[FromArrayKey(key: 'join_type', required: false)]
    public JoinType $joinType;  // 참여 방식 (공개/초대/비밀번호)

    #[ToArrayKey(key: 'open_type', exclude: false)]
    #[FromArrayKey(key: 'open_type', required: false)]
    public OpenType $openType;  // 공개 여부 (공개/비공개)

    #[ToArrayKey(key: 'join_password', exclude: true)]  // 배열 변환 시 제외 (보안)
    #[FromArrayKey(key: 'join_password', required: false)]
    public ?string $joinPassword;  // 참여 비밀번호 (joinType이 PASSWORD일 때)

    #[ToArrayKey(key: 'created_datetime', exclude: false)]
    #[FromArrayKey(key: 'created_datetime', required: false)]
    public ?\DateTime $createdDatetime;  // 채팅방 생성 일시

    #[ToArrayKey(key: 'updated_datetime', exclude: false)]
    #[FromArrayKey(key: 'updated_datetime', required: false)]
    public ?\DateTime $updatedDatetime;  // 채팅방 수정 일시

    #[ToArrayKey(key: 'is_deleted', exclude: false)]
    #[FromArrayKey(key: 'is_deleted', required: false)]
    public ?bool $isDeleted = false;  // 삭제 여부 (소프트 삭제)

    #[ToArrayKey(key: 'deleted_datetime', exclude: false)]
    #[FromArrayKey(key: 'deleted_datetime', required: false)]
    public ?\DateTime $deletedDatetime;  // 삭제 일시

    #[ToArrayKey(key: 'room_state', exclude: false)]
    #[FromArrayKey(key: 'room_state', required: false)]
    public ?RoomState $roomState = RoomState::OPEN;  // 채팅방 운영 상태 (기본값: OPEN)
}