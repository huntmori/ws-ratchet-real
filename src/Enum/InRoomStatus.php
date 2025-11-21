<?php

namespace App\Enum;

use App\Trait\EnumUtils;

enum InRoomStatus: string
{
    use EnumUtils;

    case JOIN = "JOIN";
    case LEAVE = "LEAVE";
}
