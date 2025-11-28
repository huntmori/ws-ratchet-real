<?php

namespace App\Enum;

use App\Trait\EnumUtils;

/**
 * RoomState Enum
 *
 * 채팅방의 운영 상태를 정의하는 열거형
 *
 * @property string OPEN 운영 중 - 채팅방이 활성화 상태
 * @property string CLOSE 종료됨 - 채팅방이 비활성화 상태
 */
enum RoomState : string
{
    use EnumUtils;

    case OPEN = "OPEN";    // 채팅방 운영 중 - 사용자 참여 가능
    case CLOSE = "CLOSE";  // 채팅방 종료됨 - 사용자 참여 불가
}
