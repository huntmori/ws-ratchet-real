<?php

namespace App\Response;

use App\Model\Room;
use App\Model\User;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class RoomCreateResponse
{
    use Buildable, ArraySerializable;

    public Room $room;

    /** @var array<User> $useres */
    public array $users;
}