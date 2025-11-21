<?php

namespace App\Enum;

use App\Trait\EnumUtils;

enum OpenType : string
{
    use EnumUtils;

    case PUBLIC = "PUBLIC";
    case PRIVATE = "PRIVATE";
}