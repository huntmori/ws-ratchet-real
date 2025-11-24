<?php

namespace App\Request\Room;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Request\BasePayload;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class ChatPayload implements BasePayload
{
    use Buildable, ArraySerializable;

    public const string EVENT_NAME = "room.chat";

    #[FromArrayKey(key: 'room_uuid', required: true)]
    #[ToArrayKey(key: 'room_uuid')]
    private string $roomUuid;

    #[FromArrayKey(key: 'message', required: true)]
    #[ToArrayKey(key: 'message')]
    private string $message;
}