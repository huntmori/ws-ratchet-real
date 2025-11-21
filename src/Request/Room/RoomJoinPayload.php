<?php

namespace App\Request\Room;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Request\BasePayload;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

/**
 * @method static self builder()
 * @method self roomUuid(?string $value)
 * @method ?string roomUuid()
 * @method self roomPassword(?string $value)
 * @method ?string roomPassword()
 * @method self build()
 */
class RoomJoinPayload implements BasePayload
{
    use Buildable, ArraySerializable;

    public const string EVENT_NAME = "room.join";

    #[FromArrayKey(key: 'room_uuid', required: true)]
    #[ToArrayKey(key: 'room_uuid')]
    private ?string $roomUuid = null;

    #[FromArrayKey(key: 'room_password',required: false)]
    #[ToArrayKey(key: 'room_password')]
    private ?string $roomPassword = null;

}