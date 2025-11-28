<?php

namespace App\Enum;

use App\Trait\EnumUtils;

/**
 * InRoomStatus Enum
 *
 * 사용자의 채팅방 참여 상태를 정의하는 열거형
 *
 * @property string JOIN 참여 중 - 사용자가 방에 있음
 * @property string LEAVE 퇴장 - 사용자가 방을 나감
 */
enum InRoomStatus: string
{
    use EnumUtils;

    case JOIN = "JOIN";    // 채팅방 참여 중
    case LEAVE = "LEAVE";  // 채팅방 퇴장함
}
