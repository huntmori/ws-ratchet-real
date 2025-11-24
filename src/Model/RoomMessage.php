<?php

namespace App\Model;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Enum\ChatType;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class RoomMessage extends BaseModel
{
    use Buildable, ArraySerializable;

    #[FromArrayKey(key: 'idx', required: false)]
    #[ToArrayKey(key: 'idx', exclude: false)]
    private ?int $idx = null;

    #[FromArrayKey(key: 'uuid', required: false)]
    #[ToArrayKey(key: 'uuid', exclude: false)]
    private ?string $uuid = null;

    #[FromArrayKey(key: 'room_uuid', required: false)]
    #[ToArrayKey(key: 'room_uuid', exclude: false)]
    private ?string $roomUuid = null;

    #[FromArrayKey(key: 'user_uuid', required: false)]
    #[ToArrayKey(key: 'user_uuid', exclude: false)]
    private ?string $userUuid = null;

    #[FromArrayKey(key: 'type', required: false)]
    #[ToArrayKey(key: 'type', exclude: false)]
    private ?ChatType $type = null;

    #[FromArrayKey(key: 'message', required: false)]
    #[ToArrayKey(key: 'message', exclude: false)]
    private ?string $message = null;

    #[FromArrayKey(key: 'created_datetime', required: false)]
    #[ToArrayKey(key: 'created_datetime', exclude: false)]
    private ?\DateTime $createdDatetime = null;

}