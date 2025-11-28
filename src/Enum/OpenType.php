<?php

namespace App\Enum;

use App\Trait\EnumUtils;

/**
 * OpenType Enum
 *
 * 채팅방 공개 여부를 정의하는 열거형
 *
 * @property string PUBLIC 공개 - 방 목록에 표시됨
 * @property string PRIVATE 비공개 - 방 목록에 표시되지 않음
 */
enum OpenType : string
{
    use EnumUtils;

    case PUBLIC = "PUBLIC";    // 공개 방 - 방 목록에 노출
    case PRIVATE = "PRIVATE";  // 비공개 방 - 방 목록에서 숨김
}