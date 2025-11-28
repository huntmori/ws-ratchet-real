<?php

namespace App\Enum;

use App\Trait\EnumUtils;

/**
 * JoinType Enum
 *
 * 채팅방 참여 방식을 정의하는 열거형
 *
 * @property string PUBLIC 공개 - 누구나 자유롭게 참여 가능
 * @property string INVITE 초대 - 초대받은 사용자만 참여 가능
 * @property string PASSWORD 비밀번호 - 비밀번호를 알아야 참여 가능
 */
enum JoinType : string
{
    use EnumUtils;

    case PUBLIC = "PUBLIC";      // 공개 방 - 누구나 참여 가능
    case INVITE = "INVITE";      // 초대 방 - 초대받은 사용자만 참여 가능
    case PASSWORD = "PASSWORD";  // 비밀번호 방 - 비밀번호가 필요한 방

}
