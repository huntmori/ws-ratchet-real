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
    #[ToArrayKey(key: 'idx', exclude: true)]
    public ?int $idx;

    #[ToArrayKey(key: 'uuid', exclude: false)]
    #[FromArrayKey(key: 'uuid', required: false)]
    public ?string $uuid;

    #[ToArrayKey(key: 'room_name', exclude: false)]
    #[FromArrayKey(key: 'room_name', required: false)]
    public ?string $roomName;

    #[ToArrayKey(key: 'maximum_users', exclude: false)]
    #[FromArrayKey(key: 'maximum_users', required: false)]
    public ?int $maximumUsers;

    #[ToArrayKey(key: 'join_type', exclude: false)]
    #[FromArrayKey(key: 'join_type', required: false)]
    public JoinType $joinType;

    #[ToArrayKey(key: 'open_type', exclude: false)]
    #[FromArrayKey(key: 'open_type', required: false)]
    public OpenType $openType;

    #[ToArrayKey(key: 'join_password', exclude: true)]
    #[FromArrayKey(key: 'join_password', required: false)]
    public ?string $joinPassword;

    #[ToArrayKey(key: 'created_datetime', exclude: false)]
    #[FromArrayKey(key: 'created_datetime', required: false)]
    public ?\DateTime $createdDatetime;

    #[ToArrayKey(key: 'updated_datetime', exclude: false)]
    #[FromArrayKey(key: 'updated_datetime', required: false)]
    public ?\DateTime $updatedDatetime;

    #[ToArrayKey(key: 'is_deleted', exclude: false)]
    #[FromArrayKey(key: 'is_deleted', required: false)]
    public ?bool $isDeleted = false;

    #[ToArrayKey(key: 'deleted_datetime', exclude: false)]
    #[FromArrayKey(key: 'deleted_datetime', required: false)]
    public ?\DateTime $deletedDatetime;

    #[ToArrayKey(key: 'room_state', exclude: false)]
    #[FromArrayKey(key: 'room_state', required: false)]
    public ?RoomState $roomState = RoomState::OPEN;
}