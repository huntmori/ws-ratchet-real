<?php

namespace App;

use App\Attribute\FromArrayKey;
use App\Attribute\ToArrayKey;
use App\Model\Room;
use App\Model\User;
use App\Trait\ArraySerializable;
use App\Trait\Buildable;

class RoomUserPair
{
    use Buildable, ArraySerializable;

    #[ToArrayKey(key: 'room', exclude: false)]
    #[FromArrayKey(key: 'room', required: true)]
    public Room $room;

    #[FromArrayKey(key: 'users', required: true)]
    #[ToArrayKey(key: 'users', exclude: false)]
    /** @var array<string, User> $users*/
    public array $users;

    #[FromArrayKey(key: 'messages', required: false)]
    #[ToArrayKey(key: 'messages', exclude: false)]
    /** @var array<string, string> $messages userKey => messageEntity */
    public array $messages = [];
    public function getSessionKey(): string
    {
        return "room:{$this->room->uuid}";
    }

    public static function getSessionKeyByUuid(string $roomUuid): string
    {
        return "room:{$roomUuid}";
    }
}