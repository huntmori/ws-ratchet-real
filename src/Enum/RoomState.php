<?php

namespace App\Enum;

use App\Trait\EnumUtils;

enum RoomState : string
{
    use EnumUtils;

    case OPEN = "OPEN";
    case CLOSE = "CLOSE";
}
