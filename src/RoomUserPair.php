<?php

namespace App;

use App\Model\Room;
use App\Model\User;

class RoomUserPair
{
    public Room $room;

    /** @var array<string, User> */
    public array $users;

    public function getSessionKey(): string
    {
        return "room:{$this->room->uuid}";
    }
}