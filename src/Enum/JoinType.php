<?php

namespace App\Enum;

use App\Trait\EnumUtils;

enum JoinType : string
{
    use EnumUtils;

    case PUBLIC = "PUBLIC";
    case INVITE = "INVITE";
    case PASSWORD = "PASSWORD";

}
