<?php

namespace App\Model;

use App\Enum\JoinType;
use App\Enum\OpenType;
use App\Enum\RoomState;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class Room extends BaseModel
{
    use ArraySerializable, Buildable;

    public ?int $idx;

    public ?string $uuid;
    public ?string $roomName;

    public ?int $maximumUsers;

    public JoinType $joinType;
    public OpenType $openType;

    public ?string $joinPassword;

    public ?\DateTime $createdDatetime;
    public ?\DateTime $updatedDatetime;

    public bool $isDeleted = false;
    public ?\DateTime $deletedDatetime;

    public RoomState $roomState = RoomState::OPEN;
}