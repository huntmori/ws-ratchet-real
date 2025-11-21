<?php

namespace App\Request\Room;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Enum\JoinType;
use App\Enum\OpenType;
use App\Request\BasePayload;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;


/**
 * RoomCreatePayload
 *
 * @method static RoomCreatePayload builder()
 * @method RoomCreatePayload build()
 *
 * Setter methods:
 * @method RoomCreatePayload roomName(string $roomName)
 * @method RoomCreatePayload maxUsers(int $maxUsers)
 * @method RoomCreatePayload joinType(string $joinType)
 * @method RoomCreatePayload password(?string $password)
 */
class RoomCreatePayload implements BasePayload
{
    use Buildable, ArraySerializable;
    public const string EVENT_NAME = "room.create";
    #[FromArrayKey(key: 'room_name', required: true)]
    #[ToArrayKey(key: 'room_name')]
    public ?string $roomName = null;

    #[FromArrayKey(key: 'maximum_users', required: false)]
    #[ToArrayKey(key: 'maximum_users')]
    public ?int $maximumUsers = 0;

    #[FromArrayKey(key: 'join_type', required: false)]
    #[ToArrayKey(key: 'join_type')]
    public ?JoinType $joinType = null;

    #[FromArrayKey(key: 'open_type', required: false)]
    #[ToArrayKey(key: 'open_type')]
    public ?OpenType $openType = null;

    #[FromArrayKey(key: 'join_password', required: false)]
    #[ToArrayKey(key: 'join_password', exclude: true)]
    public ?string $joinPassword = null;

}